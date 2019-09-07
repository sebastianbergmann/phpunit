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

use PHPUnit\Util\FileLoader;
use PHPUnit\Util\Filesystem;
use ReflectionClass;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class StandardTestSuiteLoader implements TestSuiteLoader
{
    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\Exception
     */
    public function load(string $suiteClassName, string $suiteClassFile = ''): ReflectionClass
    {
        $suiteClassName = \str_replace('.php', '', \basename($suiteClassName));

        if (empty($suiteClassFile)) {
            $suiteClassFile = Filesystem::classNameToFilename(
                $suiteClassName
            );
        }

        $loadedClasses = \get_declared_classes();
        $filename      = FileLoader::checkAndLoad($suiteClassFile);
        $loadedClasses = \array_values(
            \array_diff(\get_declared_classes(), $loadedClasses)
        );

        $offset = 0 - \strlen($suiteClassName);
        $class  = null;

        foreach ($loadedClasses as $loadedClass) {
            try {
                $class = new ReflectionClass($loadedClass);
            } catch (\ReflectionException $e) {
                throw new Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }

            if (\substr($loadedClass, $offset) === $suiteClassName &&
                $class->getFileName() == $filename) {
                $suiteClassName = $loadedClass;

                break;
            }
        }

        if (!\class_exists($suiteClassName, false) ||
            !($class instanceof ReflectionClass)) {
            throw new Exception(
                \sprintf(
                    "Class '%s' could not be found in '%s'.",
                    $suiteClassName,
                    $suiteClassFile
                )
            );
        }

        return $class;
    }

    public function reload(ReflectionClass $aClass): ReflectionClass
    {
        return $aClass;
    }
}
