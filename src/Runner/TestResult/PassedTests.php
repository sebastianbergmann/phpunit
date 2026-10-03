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

use function assert;
use function explode;
use function in_array;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Framework\TestSize\Known;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\Api\Groups;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PassedTests
{
    private static ?self $instance = null;

    /**
     * @var list<class-string>
     */
    private array $passedTestClasses = [];

    /**
     * @var array<string,array{returnValue: mixed, size: TestSize}>
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
     * @param class-string $className
     */
    public function testClassPassed(string $className): void
    {
        $this->passedTestClasses[] = $className;
    }

    public function testMethodPassed(TestMethod $test, mixed $returnValue): void
    {
        $size = (new Groups)->size(
            $test->className(),
            $test->methodName(),
        );

        $this->passedTestMethods[$test->className() . '::' . $test->methodName()] = [
            'returnValue' => $returnValue,
            'size'        => $size,
        ];
    }

    /**
     * The passes are added to the ones recorded so far rather than merged
     * with them into new arrays: merging would copy every pass recorded so
     * far on each import, and the main process imports the passes of every
     * test that runs in a separate process, which made importing them
     * quadratic.
     */
    public function import(self $other): void
    {
        foreach ($other->passedTestClasses as $className) {
            $this->passedTestClasses[] = $className;
        }

        foreach ($other->passedTestMethods as $method => $passedTestMethod) {
            $this->passedTestMethods[$method] = $passedTestMethod;
        }
    }

    /**
     * @param class-string $className
     */
    public function hasTestClassPassed(string $className): bool
    {
        return in_array($className, $this->passedTestClasses, true);
    }

    public function hasTestMethodPassed(string $method): bool
    {
        return isset($this->passedTestMethods[$method]);
    }

    public function isGreaterThan(string $method, TestSize $other): bool
    {
        if ($other->isUnknown()) {
            return false;
        }

        if (!isset($this->passedTestMethods[$method])) {
            return false;
        }

        assert($other instanceof Known);

        $size = $this->passedTestMethods[$method]['size'];

        if ($size->isUnknown()) {
            return false;
        }

        assert($size instanceof Known);

        return $size->isGreaterThan($other);
    }

    public function hasReturnValue(string $method): bool
    {
        $returnType = new ReflectionMethod(...explode('::', $method))->getReturnType();

        return !$returnType instanceof ReflectionNamedType || !in_array($returnType->getName(), ['never', 'void'], true);
    }

    public function returnValue(string $method): mixed
    {
        if (isset($this->passedTestMethods[$method])) {
            return $this->passedTestMethods[$method]['returnValue'];
        }

        return null;
    }
}
