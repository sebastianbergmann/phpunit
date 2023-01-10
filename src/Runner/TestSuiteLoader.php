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

use function array_diff;
use function array_merge;
use function array_values;
use function basename;
use function class_exists;
use function get_declared_classes;
use function stripos;
use function strlen;
use function strpos;
use function substr;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteLoader
{
    /**
     * @psalm-var list<class-string>
     */
    private static array $loadedClasses = [];

    /**
     * @psalm-var list<class-string>
     */
    private static array $declaredClasses = [];

    public function __construct()
    {
        if (empty(self::$declaredClasses)) {
            self::$declaredClasses = get_declared_classes();
        }
    }

    /**
     * @throws Exception
     */
    public function load(string $suiteClassFile): ReflectionClass
    {
        $suiteClassName = $this->classNameFromFileName($suiteClassFile);

        if (!class_exists($suiteClassName, false)) {
            include_once $suiteClassFile;

            $loadedClasses = array_values(
                array_diff(
                    get_declared_classes(),
                    array_merge(
                        self::$declaredClasses,
                        self::$loadedClasses
                    )
                )
            );

            self::$loadedClasses = array_merge($loadedClasses, self::$loadedClasses);

            if (empty(self::$loadedClasses)) {
                throw new ClassCannotBeFoundException($suiteClassName, $suiteClassFile);
            }
        }

        if (!class_exists($suiteClassName, false)) {
            $offset = 0 - strlen($suiteClassName);

            foreach (self::$loadedClasses as $loadedClass) {
                if (stripos(substr($loadedClass, $offset - 1), '\\' . $suiteClassName) === 0 ||
                    stripos(substr($loadedClass, $offset - 1), '_' . $suiteClassName) === 0) {
                    $suiteClassName = $loadedClass;

                    break;
                }
            }
        }

        if (!class_exists($suiteClassName, false)) {
            throw new ClassCannotBeFoundException($suiteClassName, $suiteClassFile);
        }

        $class = new ReflectionClass($suiteClassName);

        if ($class->isSubclassOf(TestCase::class)) {
            if ($class->isAbstract()) {
                throw new ClassIsAbstractException($suiteClassName, $suiteClassFile);
            }

            return $class;
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
}
