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

use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\ComparisonFailure;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\TestSuite\TestSuite;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface Emitter
{
    public function applicationStarted(): void;

    public function testRunnerStarted(): void;

    public function testRunnerConfigured(Configuration $configuration): void;

    public function testRunnerBootstrapFinished(string $filename): void;

    public function testRunnerLoadedExtensionFromPhar(string $filename, string $name, string $version): void;

    /**
     * @param class-string          $className
     * @param array<string, string> $parameters
     */
    public function testRunnerBootstrappedExtension(string $className, array $parameters): void;

    public function dataProviderMethodCalled(ClassMethod $testMethod, ClassMethod $dataProviderMethod): void;

    public function dataProviderMethodFinished(ClassMethod $testMethod, ClassMethod ...$calledMethods): void;

    public function testSuiteLoaded(TestSuite $testSuite): void;

    public function testSuiteFiltered(TestSuite $testSuite): void;

    public function testSuiteSorted(int $executionOrder, int $executionOrderDefects, bool $resolveDependencies): void;

    public function testRunnerEventFacadeSealed(): void;

    public function testRunnerExecutionStarted(TestSuite $testSuite): void;

    public function testRunnerDisabledGarbageCollection(): void;

    public function testRunnerTriggeredGarbageCollection(): void;

    public function testSuiteSkipped(TestSuite $testSuite, string $message): void;

    public function testSuiteStarted(TestSuite $testSuite): void;

    public function testPreparationStarted(Code\Test $test): void;

    public function testPreparationFailed(Code\Test $test): void;

    /**
     * @param class-string $testClassName
     */
    public function testBeforeFirstTestMethodCalled(string $testClassName, ClassMethod $calledMethod): void;

    /**
     * @param class-string $testClassName
     */
    public function testBeforeFirstTestMethodErrored(string $testClassName, ClassMethod $calledMethod, Throwable $throwable): void;

    /**
     * @param class-string $testClassName
     */
    public function testBeforeFirstTestMethodFinished(string $testClassName, ClassMethod ...$calledMethods): void;

    /**
     * @param class-string $testClassName
     */
    public function testBeforeTestMethodCalled(string $testClassName, ClassMethod $calledMethod): void;

    /**
     * @param class-string $testClassName
     */
    public function testBeforeTestMethodFinished(string $testClassName, ClassMethod ...$calledMethods): void;

    /**
     * @param class-string $testClassName
     */
    public function testPreConditionCalled(string $testClassName, ClassMethod $calledMethod): void;

    /**
     * @param class-string $testClassName
     */
    public function testPreConditionFinished(string $testClassName, ClassMethod ...$calledMethods): void;

    public function testPrepared(Code\Test $test): void;

    /**
     * @param class-string $className
     */
    public function testRegisteredComparator(string $className): void;

    /**
     * @param class-string $className
     */
    public function testCreatedMockObject(string $className): void;

    /**
     * @param list<class-string> $interfaces
     */
    public function testCreatedMockObjectForIntersectionOfInterfaces(array $interfaces): void;

    /**
     * @param trait-string $traitName
     */
    public function testCreatedMockObjectForTrait(string $traitName): void;

    /**
     * @param class-string $className
     */
    public function testCreatedMockObjectForAbstractClass(string $className): void;

    /**
     * @param class-string $originalClassName
     * @param class-string $mockClassName
     * @param list<string> $methods
     * @param list<mixed>  $options
     */
    public function testCreatedMockObjectFromWsdl(string $wsdlFile, string $originalClassName, string $mockClassName, array $methods, bool $callOriginalConstructor, array $options): void;

    /**
     * @param class-string $className
     */
    public function testCreatedPartialMockObject(string $className, string ...$methodNames): void;

    /**
     * @param class-string $className
     * @param list<mixed>  $constructorArguments
     */
    public function testCreatedTestProxy(string $className, array $constructorArguments): void;

    /**
     * @param class-string $className
     */
    public function testCreatedStub(string $className): void;

    /**
     * @param list<class-string> $interfaces
     */
    public function testCreatedStubForIntersectionOfInterfaces(array $interfaces): void;

    public function testErrored(Code\Test $test, Throwable $throwable): void;

    public function testFailed(Code\Test $test, Throwable $throwable, ?ComparisonFailure $comparisonFailure): void;

    public function testPassed(Code\Test $test): void;

    /**
     * @param non-empty-string $message
     */
    public function testConsideredRisky(Code\Test $test, string $message): void;

    public function testMarkedAsIncomplete(Code\Test $test, Throwable $throwable): void;

    /**
     * @param non-empty-string $message
     */
    public function testSkipped(Code\Test $test, string $message): void;

    /**
     * @param non-empty-string $message
     */
    public function testTriggeredPhpunitDeprecation(?Code\Test $test, string $message): void;

    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function testTriggeredPhpDeprecation(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline, bool $ignoredByTest, IssueTrigger $trigger): void;

    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $stackTrace
     */
    public function testTriggeredDeprecation(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline, bool $ignoredByTest, IssueTrigger $trigger, string $stackTrace): void;

    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function testTriggeredError(Code\Test $test, string $message, string $file, int $line, bool $suppressed): void;

    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function testTriggeredNotice(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline): void;

    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function testTriggeredPhpNotice(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline): void;

    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function testTriggeredWarning(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline): void;

    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function testTriggeredPhpWarning(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline): void;

    /**
     * @param non-empty-string $message
     */
    public function testTriggeredPhpunitError(Code\Test $test, string $message): void;

    /**
     * @param non-empty-string $message
     */
    public function testTriggeredPhpunitWarning(Code\Test $test, string $message): void;

    /**
     * @param non-empty-string $output
     */
    public function testPrintedUnexpectedOutput(string $output): void;

    public function testFinished(Code\Test $test, int $numberOfAssertionsPerformed): void;

    /**
     * @param class-string $testClassName
     */
    public function testPostConditionCalled(string $testClassName, ClassMethod $calledMethod): void;

    /**
     * @param class-string $testClassName
     */
    public function testPostConditionFinished(string $testClassName, ClassMethod ...$calledMethods): void;

    /**
     * @param class-string $testClassName
     */
    public function testAfterTestMethodCalled(string $testClassName, ClassMethod $calledMethod): void;

    /**
     * @param class-string $testClassName
     */
    public function testAfterTestMethodFinished(string $testClassName, ClassMethod ...$calledMethods): void;

    /**
     * @param class-string $testClassName
     */
    public function testAfterLastTestMethodCalled(string $testClassName, ClassMethod $calledMethod): void;

    /**
     * @param class-string $testClassName
     */
    public function testAfterLastTestMethodFinished(string $testClassName, ClassMethod ...$calledMethods): void;

    public function testSuiteFinished(TestSuite $testSuite): void;

    public function testRunnerStartedChildProcess(): void;

    public function testRunnerFinishedChildProcess(string $stdout, string $stderr): void;

    public function testRunnerTriggeredDeprecation(string $message): void;

    public function testRunnerTriggeredWarning(string $message): void;

    public function testRunnerEnabledGarbageCollection(): void;

    public function testRunnerExecutionAborted(): void;

    public function testRunnerExecutionFinished(): void;

    public function testRunnerFinished(): void;

    public function applicationFinished(int $shellExitCode): void;
}
