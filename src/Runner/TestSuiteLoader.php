<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use function array_keys;
use function array_slice;
use function basename;
use function count;
use function get_declared_classes;
use function realpath;
use function str_ends_with;
use function strpos;
use function strtolower;
use function substr;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteLoader
{
    /**
     * @var ?non-negative-int
     */
    private static ?int $numberOfDeclaredClasses = null;

    /**
     * @var array<non-empty-string, list<class-string>>
     */
    private static array $fileToClassesMap = [];

    /**
     * @var array<string, true>
     */
    private static array $loadedSuiteClassFiles = [];

    /**
     * The classes declared in the test class files that have been loaded,
     * mapped to the file that declares them.
     *
     * A parallel worker loads only the test class file of the unit it runs,
     * so a class that is declared in another test class file, and is not
     * autoloadable, is unknown to it. A sequential run, which loads every
     * test class file before it runs a test, knows such a class, and a test
     * may rely on that, for instance by naming it in #[DataProviderExternal].
     * The worker uses this map to load such a class when it is needed.
     *
     * @return array<lowercase-string, string>
     */
    public static function classesDeclaredInLoadedSuiteClassFiles(): array
    {
        $map = [];

        foreach (array_keys(self::$loadedSuiteClassFiles) as $file) {
            if (!isset(self::$fileToClassesMap[$file])) {
                continue;
            }

            foreach (self::$fileToClassesMap[$file] as $class) {
                $map[strtolower($class)] = $file;
            }
        }

        return $map;
    }

    /**
     * @throws Exception
     *
     * @return ReflectionClass<TestCase>
     */
    public function load(string $suiteClassFile): ReflectionClass
    {
        $resolved = realpath($suiteClassFile);

        if ($resolved === false) {
            throw new ClassCannotBeFoundException($suiteClassFile, $suiteClassFile);
        }

        $suiteClassFile = $resolved;
        $suiteClassName = $this->classNameFromFileName($suiteClassFile);
        $loadedClasses  = $this->loadSuiteClassFile($suiteClassFile);

        foreach ($loadedClasses as $className) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $class = new ReflectionClass($className);

            if ($class->isAnonymous()) {
                continue;
            }

            if ($class->getFileName() !== $suiteClassFile) {
                continue;
            }

            if (!$class->isSubclassOf(TestCase::class)) {
                continue;
            }

            if (!str_ends_with(strtolower($class->getShortName()), strtolower($suiteClassName))) {
                continue;
            }

            if (!$class->isAbstract()) {
                return $class;
            }

            $e = new ClassIsAbstractException($class->getName(), $suiteClassFile);
        }

        if (isset($e)) {
            throw $e;
        }

        foreach ($loadedClasses as $className) {
            if (str_ends_with(strtolower($className), strtolower($suiteClassName))) {
                throw new ClassDoesNotExtendTestCaseException($className, $suiteClassFile);
            }
        }

        throw new ClassCannotBeFoundException($suiteClassName, $suiteClassFile);
    }

    private function classNameFromFileName(string $suiteClassFile): string
    {
        $className = basename($suiteClassFile, '.php');
        $dotPos    = strpos($className, '.');

        if ($dotPos !== false) {
            $className = substr($className, 0, $dotPos);
        }

        return $className;
    }

    /**
     * @return array<class-string>
     */
    private function loadSuiteClassFile(string $suiteClassFile): array
    {
        /*
         * The file is recorded even when it does not have to be loaded
         * because its classes have already been declared, by the autoloader
         * for instance, and mapped while another file was loaded.
         */
        self::$loadedSuiteClassFiles[$suiteClassFile] = true;

        if (isset(self::$fileToClassesMap[$suiteClassFile])) {
            return self::$fileToClassesMap[$suiteClassFile];
        }

        if (self::$numberOfDeclaredClasses === null) {
            /*
             * Classes that were declared before the first test class file was
             * loaded, by the bootstrap script for instance, are mapped to the
             * files that declare them as well. Without this, a test class file
             * whose class has already been declared would have to be searched
             * for among all declared classes.
             */
            $declaredClasses = get_declared_classes();

            self::mapClassesToTheFilesThatDeclareThem($declaredClasses);

            self::$numberOfDeclaredClasses = count($declaredClasses);
        }

        require_once $suiteClassFile;

        $declaredClasses = get_declared_classes();

        /*
         * Classes are declared in the order in which they are encountered and
         * they cannot be undeclared. The classes that were declared while the
         * file was loaded are therefore at the end of the list.
         */
        self::mapClassesToTheFilesThatDeclareThem(
            array_slice($declaredClasses, self::$numberOfDeclaredClasses),
        );

        self::$numberOfDeclaredClasses = count($declaredClasses);

        if (!isset(self::$fileToClassesMap[$suiteClassFile])) {
            return $declaredClasses;
        }

        return self::$fileToClassesMap[$suiteClassFile];
    }

    /**
     * @param array<class-string> $classes
     */
    private static function mapClassesToTheFilesThatDeclareThem(array $classes): void
    {
        foreach ($classes as $class) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $fileName = new ReflectionClass($class)->getFileName();

            if ($fileName === false || $fileName === '') {
                continue;
            }

            self::$fileToClassesMap[$fileName][] = $class;
        }
    }
}
