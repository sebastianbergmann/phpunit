<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use const DIRECTORY_SEPARATOR;
use function array_keys;
use function assert;
use function class_exists;
use function defined;
use function dirname;
use function is_dir;
use function is_file;
use function preg_match;
use function realpath;
use function str_starts_with;
use function strlen;
use function substr;
use PHPUnit\Metadata\DataProvider as DataProviderMetadata;
use PHPUnit\Metadata\Parser\Registry;
use PHPUnit\Metadata\UsesFixture;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * The files and directories a test declares that it depends on although
 * executing the test does not show it.
 *
 * A path is declared where it is known: on the test class, on the test method,
 * or on the method that provides the data for the test. A path that is
 * declared on a data provider counts for every test that provider provides
 * data for, which is why declaring it there and not on each of those tests is
 * worth doing.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Fixtures
{
    /**
     * Paths that cannot be resolved are not returned; they are reported
     * separately, so that a path that does not exist is not silently taken to
     * mean that the test depends on nothing.
     *
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return list<non-empty-string>
     */
    public function for(string $className, string $methodName): array
    {
        $paths = [];

        foreach ($this->declaredPaths($className, $methodName) as $path => $directory) {
            $resolved = $this->resolve($path, $directory);

            if ($resolved === null) {
                continue;
            }

            $paths[$resolved] = true;
        }

        return array_keys($paths);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return list<non-empty-string>
     */
    public function thatCannotBeResolved(string $className, string $methodName): array
    {
        $paths = [];

        foreach ($this->declaredPaths($className, $methodName) as $path => $directory) {
            if ($this->resolve($path, $directory) !== null) {
                continue;
            }

            $paths[$path] = true;
        }

        return array_keys($paths);
    }

    /**
     * The path as it was declared, mapped to the directory of the file it was
     * declared in.
     *
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return array<non-empty-string, non-empty-string>
     */
    private function declaredPaths(string $className, string $methodName): array
    {
        $paths = $this->declaredOnClass($className);

        foreach ($this->declaredOnMethod($className, $methodName) as $path => $directory) {
            $paths[$path] = $directory;
        }

        foreach ($this->dataProviders($className, $methodName) as [$providerClassName, $providerMethodName]) {
            /*
             * A data provider can name a class that does not exist. The test
             * does not work as things are, and it does not have fixtures that
             * can be resolved either.
             */
            if (!class_exists($providerClassName)) {
                continue;
            }

            foreach ($this->declaredOnClass($providerClassName) as $path => $directory) {
                $paths[$path] = $directory;
            }

            foreach ($this->declaredOnMethod($providerClassName, $providerMethodName) as $path => $directory) {
                $paths[$path] = $directory;
            }
        }

        return $paths;
    }

    /**
     * @param class-string $className
     *
     * @return array<non-empty-string, non-empty-string>
     */
    private function declaredOnClass(string $className): array
    {
        $paths = [];

        foreach (Registry::parser()->forClass($className)->isUsesFixture() as $metadata) {
            assert($metadata instanceof UsesFixture);

            $directory = $this->directoryOf($className);

            if ($directory === null) {
                continue; // @codeCoverageIgnore
            }

            $paths[$metadata->path()] = $directory;
        }

        return $paths;
    }

    /**
     * A path is resolved relative to the file the attribute is written in, and
     * that is the file of the class the attribute was found on: an attribute
     * on a parent class or on a trait names a path next to that parent class
     * or that trait, and not one next to the class that inherits it.
     *
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return array<non-empty-string, non-empty-string>
     */
    private function declaredOnMethod(string $className, string $methodName): array
    {
        $paths = [];

        foreach (Registry::parser()->forMethod($className, $methodName)->isUsesFixture() as $metadata) {
            assert($metadata instanceof UsesFixture);

            $directory = $this->directoryOfMethod($className, $methodName);

            if ($directory === null) {
                continue; // @codeCoverageIgnore
            }

            $paths[$metadata->path()] = $directory;
        }

        return $paths;
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return list<array{0: class-string, 1: non-empty-string}>
     */
    private function dataProviders(string $className, string $methodName): array
    {
        $dataProviders = [];

        foreach (Registry::parser()->forMethod($className, $methodName)->isDataProvider() as $metadata) {
            assert($metadata instanceof DataProviderMetadata);

            $dataProviders[] = [$metadata->className(), $metadata->methodName()];
        }

        return $dataProviders;
    }

    /**
     * @param class-string $className
     *
     * @return ?non-empty-string
     */
    private function directoryOf(string $className): ?string
    {
        try {
            $file = new ReflectionClass($className)->getFileName();
            // @codeCoverageIgnoreStart
        } catch (ReflectionException) {
            return null;
        }
        // @codeCoverageIgnoreEnd

        if ($file === false || $file === '') {
            return null; // @codeCoverageIgnore
        }

        return dirname($file);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return ?non-empty-string
     */
    private function directoryOfMethod(string $className, string $methodName): ?string
    {
        try {
            $file = new ReflectionMethod($className, $methodName)->getDeclaringClass()->getFileName();
            // @codeCoverageIgnoreStart
        } catch (ReflectionException) {
            return null;
        }
        // @codeCoverageIgnoreEnd

        if ($file === false || $file === '') {
            return null; // @codeCoverageIgnore
        }

        return dirname($file);
    }

    /**
     * @param non-empty-string $path
     * @param non-empty-string $directory
     *
     * @return ?non-empty-string
     */
    private function resolve(string $path, string $directory): ?string
    {
        if (!$this->isAbsolute($path)) {
            $path = $directory . DIRECTORY_SEPARATOR . $path;
        }

        if (!is_file($path) && !is_dir($path)) {
            return null;
        }

        $resolved = realpath($path);

        if ($resolved === false) {
            return null; // @codeCoverageIgnore
        }

        return $resolved;
    }

    /**
     * On Windows, this matches what the loader of the XML configuration file
     * matches: a UNC path, a path that begins with a drive letter, and a path
     * that begins with a backslash.
     */
    private function isAbsolute(string $path): bool
    {
        if (str_starts_with($path, '/')) {
            return true;
        }

        // @codeCoverageIgnoreStart
        if (defined('PHP_WINDOWS_VERSION_BUILD') &&
            ($path[0] === '\\' || (strlen($path) >= 3 && preg_match('#^[A-Z]:[/\\\\]#i', substr($path, 0, 3)) === 1))) {
            return true;
        }
        // @codeCoverageIgnoreEnd

        return false;
    }
}
