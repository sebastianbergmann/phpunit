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

use PHPUnit\Framework\Constraint;
use PHPUnit\Framework\TestSuite;
use SebastianBergmann\GlobalState\Snapshot;

interface Emitter
{
    public function applicationConfigured(): void;

    public function applicationStarted(): void;

    /**
     * @param mixed $value
     */
    public function assertionMade($value, Constraint\Constraint $constraint, string $message, bool $hasFailed): void;

    public function bootstrapFinished(string $filename): void;

    /**
     * @psalm-param class-string $className
     */
    public function comparatorRegistered(string $className): void;

    public function extensionLoaded(string $name, string $version): void;

    public function globalStateCaptured(Snapshot $snapshot): void;

    public function globalStateModified(Snapshot $snapshotBefore, Snapshot $snapshotAfter, string $message): void;

    public function globalStateRestored(Snapshot $snapshot): void;

    public function testRunConfigured(): void;

    public function testErrored(): void;

    public function testFailed(): void;

    public function testFinished(): void;

    public function testPassed(): void;

    public function testPassedButRisky(): void;

    public function testSkippedByDataProvider(Code\ClassMethod $testMethod, string $message): void;

    public function testAbortedWithMessage(Code\Test $test, string $message): void;

    public function testSkippedDueToUnsatisfiedRequirements(Code\ClassMethod $testMethod, string ...$missingRequirements): void;

    public function testSkippedWithMessage(Code\Test $test, string $message): void;

    public function testPrepared(Code\Test $test): void;

    public function testSetUpFinished(): void;

    public function testAfterTestMethodFinished(): void;

    public function testAfterLastTestMethodFinished(): void;

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeFirstTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void;

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

    public function testAfterLastTestMethodCalled(): void;

    /**
     * @psalm-param class-string $className
     */
    public function testDoubleMockObjectCreated(string $className): void;

    /**
     * @psalm-param trait-string $traitName
     */
    public function testDoubleMockObjectCreatedForTrait(string $traitName): void;

    /**
     * @psalm-param class-string $className
     */
    public function testDoubleMockObjectCreatedForAbstractClass(string $className): void;

    /**
     * @psalm-param class-string $originalClassName
     * @psalm-param class-string $mockClassName
     */
    public function testDoubleMockObjectCreatedFromWsdl(
        string $wsdlFile,
        string $originalClassName,
        string $mockClassName,
        array $methods,
        bool $callOriginalConstructor,
        array $options
    ): void;

    /**
     * @psalm-param class-string $className
     */
    public function testDoublePartialMockObjectCreated(string $className, string ...$methodNames): void;

    /**
     * @psalm-param class-string $className
     */
    public function testDoubleTestProxyCreated(string $className, array $constructorArguments): void;

    /**
     * @psalm-param class-string $className
     */
    public function testDoubleTestStubCreated(string $className): void;

    public function testSuiteAfterClassFinished(): void;

    public function testSuiteLoaded(TestSuite $testSuite): void;

    public function testSuiteRunFinished(): void;

    public function testSuiteSorted(int $executionOrder, int $executionOrderDefects, bool $resolveDependencies): void;

    public function testSuiteStarted(string $name): void;
}
