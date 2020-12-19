<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Constraint;
use PHPUnit\Framework\TestSuite;
use SebastianBergmann\CodeUnit;
use SebastianBergmann\GlobalState\Snapshot;

final class NullEmitter implements \PHPUnit\Event\Emitter
{
    public function applicationConfigured(): void
    {
    }

    public function applicationStarted(): void
    {
    }

    public function assertionMade($value, Constraint\Constraint $constraint, string $message, bool $hasFailed): void
    {
    }

    public function bootstrapFinished(string $filename): void
    {
    }

    public function comparatorRegistered(string $className): void
    {
    }

    public function extensionLoaded(string $name, string $version): void
    {
    }

    public function globalStateCaptured(Snapshot $snapshot): void
    {
    }

    public function globalStateModified(Snapshot $snapshotBefore, Snapshot $snapshotAfter, string $message): void
    {
    }

    public function globalStateRestored(Snapshot $snapshot): void
    {
    }

    public function testRunConfigured(): void
    {
    }

    public function testErrored(): void
    {
    }

    public function testFailed(): void
    {
    }

    public function testFinished(): void
    {
    }

    public function testPassed(): void
    {
    }

    public function testPassedButRisky(): void
    {
    }

    public function testSkippedByDataProvider(CodeUnit\ClassMethodUnit $testMethod, string $message): void
    {
    }

    public function testAbortedWithMessage(): void
    {
    }

    public function testSkippedDueToUnsatisfiedRequirements(CodeUnit\ClassMethodUnit $testMethod, string ...$missingRequirements): void
    {
    }

    public function testSkippedWithMessage(): void
    {
    }

    public function testPrepared(CodeUnit\ClassMethodUnit $testMethod): void
    {
    }

    public function testSetUpFinished(): void
    {
    }

    public function testAfterTestMethodFinished(): void
    {
    }

    public function testAfterLastTestMethodFinished(): void
    {
    }

    public function testBeforeFirstTestMethodCalled(string $testClassName, CodeUnit\ClassMethodUnit $calledMethod): void
    {
    }

    public function testBeforeFirstTestMethodFinished(string $testClassName, CodeUnit\ClassMethodUnit ...$calledMethods): void
    {
    }

    public function testBeforeTestMethodCalled(string $testClassName, CodeUnit\ClassMethodUnit $calledMethod): void
    {
    }

    public function testBeforeTestMethodFinished(string $testClassName, CodeUnit\ClassMethodUnit ...$calledMethods): void
    {
    }

    public function testPreConditionCalled(string $testClassName, CodeUnit\ClassMethodUnit $calledMethod): void
    {
    }

    public function testPreConditionFinished(string $testClassName, CodeUnit\ClassMethodUnit ...$calledMethods): void
    {
    }

    public function testAfterLastTestMethodCalled(): void
    {
    }

    public function testDoubleMockCreated(string $className): void
    {
    }

    public function testDoubleMockObjectCreatedForTrait(string $traitName): void
    {
    }

    public function testDoubleMockObjectCreatedForAbstractClass(string $className): void
    {
    }

    public function testDoublePartialMockObjectCreated(string $className, string ...$methodNames): void
    {
    }

    public function testDoubleTestProxyCreated(string $className, array $constructorArguments): void
    {
    }

    public function testSuiteAfterClassFinished(): void
    {
    }

    public function testSuiteLoaded(TestSuite $testSuite): void
    {
    }

    public function testSuiteRunFinished(): void
    {
    }

    public function testSuiteSorted(int $executionOrder, int $executionOrderDefects, bool $resolveDependencies): void
    {
    }

    public function testSuiteStarted(string $name): void
    {
    }
}
