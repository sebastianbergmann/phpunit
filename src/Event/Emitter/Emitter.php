<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\TestSuite\TestSuite;
use PHPUnit\Framework\Constraint;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface Emitter
{
    public function eventFacadeSealed(): void;

    public function testRunnerStarted(): void;

    public function testRunnerConfigured(Configuration $configuration): void;

    public function testExecutionStarted(TestSuite $testSuite): void;

    public function testExecutionFinished(): void;

    public function testRunnerFinished(): void;

    public function testRunnerTriggeredWarning(string $message): void;

    public function assertionMade(mixed $value, Constraint\Constraint $constraint, string $message, bool $hasFailed): void;

    public function bootstrapFinished(string $filename): void;

    /**
     * @psalm-param class-string $className
     */
    public function comparatorRegistered(string $className): void;

    public function extensionLoaded(string $name, string $version): void;

    public function testErrored(Code\Test $test, Throwable $throwable): void;

    public function testFailed(Code\Test $test, Throwable $throwable): void;

    public function testFinished(Code\Test $test, int $numberOfAssertionsPerformed): void;

    public function testPassed(Code\Test $test, mixed $testMethodReturnValue): void;

    public function testPassedWithWarning(Code\Test $test, Throwable $throwable): void;

    public function testConsideredRisky(Code\Test $test, string $message): void;

    public function testMarkedAsIncomplete(Code\Test $test, Throwable $throwable): void;

    public function testSkipped(Code\Test $test, string $message): void;

    public function testPreparationStarted(Code\Test $test): void;

    public function testPrepared(Code\Test $test): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testAfterTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testAfterLastTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeFirstTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeFirstTestMethodErrored(string $testClassName, Code\ClassMethod $calledMethod, Throwable $throwable): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeFirstTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testPreConditionCalled(string $testClassName, Code\ClassMethod $calledMethod): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testPreConditionFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testPostConditionCalled(string $testClassName, Code\ClassMethod $calledMethod): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testPostConditionFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testAfterTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testAfterLastTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void;

    public function testTriggeredPhpunitDeprecation(Code\Test $test, string $message): void;

    public function testTriggeredPhpDeprecation(Code\Test $test, string $message, string $file, int $line): void;

    public function testTriggeredDeprecation(Code\Test $test, string $message, string $file, int $line): void;

    public function testTriggeredError(Code\Test $test, string $message, string $file, int $line): void;

    public function testTriggeredPhpError(Code\Test $test, string $message, string $file, int $line): void;

    public function testTriggeredNotice(Code\Test $test, string $message, string $file, int $line): void;

    public function testTriggeredPhpNotice(Code\Test $test, string $message, string $file, int $line): void;

    public function testTriggeredWarning(Code\Test $test, string $message, string $file, int $line): void;

    public function testTriggeredPhpWarning(Code\Test $test, string $message, string $file, int $line): void;

    public function testTriggeredPhpunitWarning(Code\Test $test, string $message): void;

    /**
     * @psalm-param class-string $className
     */
    public function testMockObjectCreated(string $className): void;

    /**
     * @psalm-param trait-string $traitName
     */
    public function testMockObjectCreatedForTrait(string $traitName): void;

    /**
     * @psalm-param class-string $className
     */
    public function testMockObjectCreatedForAbstractClass(string $className): void;

    /**
     * @psalm-param class-string $originalClassName
     * @psalm-param class-string $mockClassName
     */
    public function testMockObjectCreatedFromWsdl(string $wsdlFile, string $originalClassName, string $mockClassName, array $methods, bool $callOriginalConstructor, array $options): void;

    /**
     * @psalm-param class-string $className
     */
    public function testPartialMockObjectCreated(string $className, string ...$methodNames): void;

    /**
     * @psalm-param class-string $className
     */
    public function testTestProxyCreated(string $className, array $constructorArguments): void;

    /**
     * @psalm-param class-string $className
     */
    public function testTestStubCreated(string $className): void;

    public function testSuiteLoaded(TestSuite $testSuite): void;

    public function testSuiteFiltered(TestSuite $testSuite): void;

    public function testSuiteSorted(int $executionOrder, int $executionOrderDefects, bool $resolveDependencies): void;

    public function testSuiteStarted(TestSuite $testSuite): void;

    public function testSuiteFinished(TestSuite $testSuite): void;
}
