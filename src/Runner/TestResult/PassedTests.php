<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use function array_flip;
use function array_keys;
use function array_merge;
use function array_unique;
use function in_array;
use function strpos;
use function substr;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\Api\Groups;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PassedTests
{
    private static ?self $instance = null;

    /**
     * @psalm-var list<class-string>
     */
    private array $passedTestClasses = [];

    /**
     * @psalm-var array<string,array{returnValue: mixed, size: TestSize}>
     */
    private array $passedTestMethods = [];

    public static function instance(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self;

        return self::$instance;
    }

    /**
     * @psalm-param class-string $className
     */
    public function testClassPassed(string $className): void
    {
        $this->passedTestClasses[] = $className;
    }

    public function testMethodPassed(TestMethod $test, mixed $returnValue): void
    {
        $size = (new Groups)->size(
            $test->className(),
            $test->methodName()
        );

        $this->passedTestMethods[$test->nameWithClass()] = [
            'returnValue' => $returnValue,
            'size'        => $size,
        ];
    }

    public function import(self $other): void
    {
        $this->passedTestClasses = array_merge(
            $this->passedTestClasses,
            $other->passedTestClasses
        );

        $this->passedTestMethods = array_merge(
            $this->passedTestMethods,
            $other->passedTestMethods
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function hasTestClassPassed(string $className): bool
    {
        return in_array($className, $this->passedTestClasses, true);
    }

    public function hasTestMethodPassed(string $method): bool
    {
        return isset($this->passedTestMethods[$method]);
    }

    public function hasTestMethodWithDataProviderPassed(string $method): bool
    {
        $passedTestMethodsKeys = array_keys($this->passedTestMethods);

        foreach ($passedTestMethodsKeys as $keyIndex => $keyValue) {
            $pos = strpos($keyValue, ' with data set');

            if ($pos !== false) {
                $passedTestMethodsKeys[$keyIndex] = substr($keyValue, 0, $pos);
            }
        }

        $passedTestMethodsKeys = array_flip(array_unique($passedTestMethodsKeys));

        return isset($passedTestMethodsKeys[$method]);
    }

    public function isGreaterThan(string $method, TestSize $size): bool
    {
        return $this->passedTestMethods[$method]['size']->isKnown() &&
               $size->isKnown() &&
               $this->passedTestMethods[$method]['size']->isGreaterThan($size);
    }

    public function returnValue(string $method): mixed
    {
        if (isset($this->passedTestMethods[$method])) {
            return $this->passedTestMethods[$method]['returnValue'];
        }

        return null;
    }
}
