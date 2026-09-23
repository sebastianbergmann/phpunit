<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use const PHP_EOL;
use function array_any;
use function array_keys;
use function array_merge;
use function array_values;
use function assert;
use function chdir;
use function clearstatcache;
use function error_clear_last;
use function getcwd;
use function implode;
use function is_callable;
use function libxml_clear_errors;
use function method_exists;
use function sprintf;
use function str_contains;
use AssertionError;
use PHPUnit\Event;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Framework\MockObject\InvocationJournal;
use PHPUnit\Framework\MockObject\InvocationJournalImplementation;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtLeastCount as InvokedAtLeastCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtLeastOnce as InvokedAtLeastOnceMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtMostCount as InvokedAtMostCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Stub\Exception as ExceptionStub;
use PHPUnit\Framework\MockObject\TestStubBuilder;
use PHPUnit\Framework\TestCase\CustomRegistrations;
use PHPUnit\Framework\TestCase\DataSet;
use PHPUnit\Framework\TestCase\DependencyResolver;
use PHPUnit\Framework\TestCase\DeprecationExpectation;
use PHPUnit\Framework\TestCase\EnvironmentVariables;
use PHPUnit\Framework\TestCase\ErrorLogCapture;
use PHPUnit\Framework\TestCase\ExceptionExpectation;
use PHPUnit\Framework\TestCase\GlobalStateCapture;
use PHPUnit\Framework\TestCase\HookMethodInvoker;
use PHPUnit\Framework\TestCase\MockObjectRegistry;
use PHPUnit\Framework\TestCase\OutputBuffer;
use PHPUnit\Framework\TestCase\OutputBufferStopResult;
use PHPUnit\Framework\TestCase\TestDoubleFactory;
use PHPUnit\Framework\TestRunner\TestRunner;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Metadata\Api\HookMethods;
use PHPUnit\Metadata\Api\Requirements;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\TestRunner\TestResult\PassedTests;
use ReflectionMethod;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Exporter\ObjectExporter;
use SebastianBergmann\Invoker\TimeoutException;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @phpstan-import-type HookMethodsByType from HookMethods
 */
abstract class TestCase extends Assert implements Reorderable, SelfDescribing, Test
{
    /**
     * @var non-empty-string
     */
    private string $methodName;
    private DataSet $dataSet;

    /**
     * @var list<string>
     */
    private array $groups = [];

    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $providedTests = [];

    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $dependencies = [];

    /**
     * @var array<string, mixed>
     */
    private array $dependencyInput                 = [];
    private ?bool $runTestInSeparateProcess        = null;
    private bool $preserveGlobalState              = false;
    private bool $inIsolation                      = false;
    private ?string $emptyDataProviderSkipMessage  = null;
    private ?Throwable $throwableFromDeferredIssue = null;

    /**
     * @var positive-int
     */
    private int $repetition = 1;

    /**
     * @var positive-int
     */
    private int $totalRepetitions = 1;

    /**
     * @var positive-int
     */
    private int $attempt = 1;

    /**
     * @var positive-int
     */
    private int $maxAttempts = 1;
    private ExceptionExpectation $exceptionExpectation;
    private DeprecationExpectation $deprecationExpectation;
    private OutputBuffer $outputBuffer;
    private ErrorLogCapture $errorLogCapture;
    private GlobalStateCapture $globalStateCapture;
    private EnvironmentVariables $environmentVariables;
    private MockObjectRegistry $mockObjectRegistry;
    private CustomRegistrations $customRegistrations;

    /**
     * @var array<class-string, true>
     */
    private array $failureTypes = [];
    private TestStatus $status;

    /**
     * @var non-negative-int
     */
    private int $numberOfAssertionsPerformed                 = 0;
    private mixed $testResult                                = null;
    private bool $doesNotPerformAssertions                   = false;
    private bool $wasPrepared                                = false;
    private ?Event\Code\TestMethod $testValueObjectForEvents = null;
    private Event\Emitter $emitter;

    /**
     * @param non-empty-string $name
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function __construct(string $name)
    {
        $this->methodName             = $name;
        $this->dataSet                = DataSet::empty();
        $this->status                 = TestStatus::unknown();
        $this->exceptionExpectation   = new ExceptionExpectation;
        $this->outputBuffer           = new OutputBuffer;
        $this->errorLogCapture        = new ErrorLogCapture;
        $this->globalStateCapture     = new GlobalStateCapture;
        $this->mockObjectRegistry     = new MockObjectRegistry;
        $this->deprecationExpectation = new DeprecationExpectation;
        $this->environmentVariables   = new EnvironmentVariables;
        $this->customRegistrations    = new CustomRegistrations;
        $this->emitter                = Event\Facade::emitter();

        if (is_callable($this->sortId(), true)) {
            $this->providedTests = [new ExecutionOrderDependency($this->sortId())];
        }
    }

    /**
     * This method is called before the first test of this test class is run.
     *
     * @codeCoverageIgnore
     */
    public static function setUpBeforeClass(): void
    {
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @codeCoverageIgnore
     */
    public static function tearDownAfterClass(): void
    {
    }

    /**
     * This method is called before each test.
     *
     * @codeCoverageIgnore
     */
    protected function setUp(): void
    {
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called between setUp() and test.
     *
     * @codeCoverageIgnore
     */
    protected function assertPreConditions(): void
    {
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called between test and tearDown().
     *
     * @codeCoverageIgnore
     */
    protected function assertPostConditions(): void
    {
    }

    /**
     * This method is called after each test.
     *
     * @codeCoverageIgnore
     */
    protected function tearDown(): void
    {
    }

    /**
     * Returns a string representation of the test case.
     *
     * @throws Exception
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function toString(): string
    {
        $buffer = sprintf(
            '%s::%s',
            static::class,
            $this->methodName,
        );

        return $buffer . $this->dataSetAsStringWithData();
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function count(): int
    {
        return 1;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function status(): TestStatus
    {
        return $this->status;
    }

    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws \SebastianBergmann\Template\InvalidArgumentException
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws ProcessIsolationException
     * @throws UnintentionallyCoveredCodeException
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function run(): void
    {
        if (!$this->inIsolation &&
            !(new DependencyResolver)->resolve($this, $this->dependencies, $this->emitter)) {
            return;
        }

        new TestRunner($this->emitter)->run($this);
    }

    /**
     * @return list<string>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function groups(): array
    {
        return $this->groups;
    }

    /**
     * @param list<string> $groups
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function nameWithDataSet(): string
    {
        return $this->methodName . $this->dataSetAsString();
    }

    /**
     * @return non-empty-string
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function name(): string
    {
        return $this->methodName;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function size(): TestSize
    {
        return (new Groups)->size(
            static::class,
            $this->methodName,
        );
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     *
     * @phpstan-assert-if-true non-empty-string $this->output()
     */
    final public function hasUnexpectedOutput(): bool
    {
        return $this->outputBuffer->hasUnexpectedOutput();
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function output(): string
    {
        return $this->outputBuffer->output();
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function doesNotPerformAssertions(): bool
    {
        return $this->doesNotPerformAssertions;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function expectsOutput(): bool
    {
        return $this->outputBuffer->expectsOutput();
    }

    /**
     * @throws Throwable
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function runLifecycle(): void
    {
        $this->prepareEnvironment();

        $hookMethods             = new HookMethods($this->emitter)->hookMethods(static::class);
        $currentWorkingDirectory = getcwd();
        $hasMetRequirements      = false;
        $testSucceeded           = false;
        $e                       = null;

        try {
            $this->throwThrowableFromDeferredIssue();
            $this->checkRequirements();

            $hasMetRequirements = true;

            $this->prepareTest($hookMethods);

            $this->testResult = $this->runTest();

            $this->verifyTest($hookMethods);

            $this->status  = TestStatus::success();
            $testSucceeded = true;
        } catch (Throwable $t) {
            $e = $this->handleThrowableFromTest($t);
        }

        $outputBufferingStopped = false;

        if ($e === null && $this->outputBuffer->hasExpectation()) {
            $outputBufferingStopped = true;

            if ($this->stopOutputBuffering()->closedCleanly) {
                $e = $this->performOutputAssertions();
            }
        }

        $throwableFromMockObjectDestructor = $this->discardMockObjects();

        if ($throwableFromMockObjectDestructor !== null) {
            $e = $throwableFromMockObjectDestructor;
        }

        if ($hasMetRequirements) {
            $e = $this->tearDownTest($hookMethods, $e);
        }

        // $e is null when the test failed with a throwable of a registered failure type
        if ($testSucceeded && $e === null) {
            $this->registerAsPassed();
        }

        if (!$outputBufferingStopped) {
            $this->stopOutputBuffering();
        }

        $this->restoreEnvironment($currentWorkingDirectory);

        if ($e !== null) {
            $this->onNotSuccessfulTest($e);
        }
    }

    /**
     * @param list<ExecutionOrderDependency> $dependencies
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @param array<string, mixed> $dependencyInput
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setDependencyInput(array $dependencyInput): void
    {
        $this->dependencyInput          = $dependencyInput;
        $this->testValueObjectForEvents = null;
    }

    /**
     * @return array<string, mixed>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function dependencyInput(): array
    {
        return $this->dependencyInput;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function hasDependencyInput(): bool
    {
        return $this->dependencyInput !== [];
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setBackupGlobals(bool $backupGlobals): void
    {
        $this->globalStateCapture->setBackupGlobals($backupGlobals);
    }

    /**
     * @param list<string> $backupGlobalsExcludeList
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setBackupGlobalsExcludeList(array $backupGlobalsExcludeList): void
    {
        $this->globalStateCapture->setBackupGlobalsExcludeList($backupGlobalsExcludeList);
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setBackupStaticProperties(bool $backupStaticProperties): void
    {
        $this->globalStateCapture->setBackupStaticProperties($backupStaticProperties);
    }

    /**
     * @param array<class-string, list<non-empty-string>> $backupStaticPropertiesExcludeList
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setBackupStaticPropertiesExcludeList(array $backupStaticPropertiesExcludeList): void
    {
        $this->globalStateCapture->setBackupStaticPropertiesExcludeList($backupStaticPropertiesExcludeList);
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function runsTestInSeparateProcess(): bool
    {
        return $this->runTestInSeparateProcess === true;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setRunTestInSeparateProcess(bool $runTestInSeparateProcess): void
    {
        if ($this->runTestInSeparateProcess === null) {
            $this->runTestInSeparateProcess = $runTestInSeparateProcess;
        }
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function preservesGlobalState(): bool
    {
        return $this->preserveGlobalState;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setPreserveGlobalState(bool $preserveGlobalState): void
    {
        $this->preserveGlobalState = $preserveGlobalState;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function isInIsolation(): bool
    {
        return $this->inIsolation;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     *
     * @codeCoverageIgnore
     */
    final public function setInIsolation(bool $inIsolation): void
    {
        $this->inIsolation = $inIsolation;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setEmptyDataProviderSkipMessage(string $message): void
    {
        $this->emptyDataProviderSkipMessage = $message;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setThrowableFromDeferredIssue(Throwable $throwable): void
    {
        $this->throwableFromDeferredIssue = $throwable;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function result(): mixed
    {
        return $this->testResult;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setResult(mixed $result): void
    {
        $this->testResult = $result;
    }

    /**
     * @param non-empty-string $type
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function registerMockObject(string $type, MockObject $mockObject): void
    {
        $this->mockObjectRegistry->register($type, $mockObject);
    }

    /**
     * @param non-negative-int $count
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function addToAssertionCount(int $count): void
    {
        assert($count >= 0);

        $this->numberOfAssertionsPerformed += $count;
    }

    /**
     * @return non-negative-int
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function numberOfAssertionsPerformed(): int
    {
        return $this->numberOfAssertionsPerformed;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function usesDataProvider(): bool
    {
        return !$this->dataSet->isEmpty();
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function dataName(): int|string
    {
        return $this->dataSet->name();
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function dataSetAsString(): string
    {
        return $this->dataSet->asString();
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function dataSetAsStringWithData(): string
    {
        return $this->dataSet->asStringWithData();
    }

    /**
     * @return array<mixed>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function providedData(): array
    {
        return $this->dataSet->data();
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function sortId(): string
    {
        $id = $this->methodName;

        if (!str_contains($id, '::')) {
            $id = static::class . '::' . $id;
        }

        if ($this->usesDataProvider()) {
            $id .= $this->dataSetAsString();
        }

        return $id;
    }

    /**
     * @return list<ExecutionOrderDependency>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function provides(): array
    {
        return $this->providedTests;
    }

    /**
     * @return list<ExecutionOrderDependency>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function requires(): array
    {
        return $this->dependencies;
    }

    /**
     * @param array<mixed> $data
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setData(int|string $dataName, array $data): void
    {
        $this->dataSet                  = new DataSet($dataName, $data);
        $this->testValueObjectForEvents = null;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function valueObjectForEvents(): Event\Code\TestMethod
    {
        if ($this->testValueObjectForEvents !== null) {
            return $this->testValueObjectForEvents;
        }

        $this->testValueObjectForEvents = Event\Code\TestMethodBuilder::fromTestCase($this);

        return $this->testValueObjectForEvents;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function wasPrepared(): bool
    {
        return $this->wasPrepared;
    }

    /**
     * @return positive-int
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function repetition(): int
    {
        return $this->repetition;
    }

    /**
     * @return positive-int
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function totalRepetitions(): int
    {
        return $this->totalRepetitions;
    }

    /**
     * @param positive-int $repetition
     * @param positive-int $totalRepetitions
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setRepetition(int $repetition, int $totalRepetitions): void
    {
        $this->repetition               = $repetition;
        $this->totalRepetitions         = $totalRepetitions;
        $this->testValueObjectForEvents = null;
    }

    /**
     * @return positive-int
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function attempt(): int
    {
        return $this->attempt;
    }

    /**
     * @return positive-int
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * @param positive-int $attempt
     * @param positive-int $maxAttempts
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setAttempt(int $attempt, int $maxAttempts): void
    {
        $this->attempt                  = $attempt;
        $this->maxAttempts              = $maxAttempts;
        $this->testValueObjectForEvents = null;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function setStatus(TestStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * Returns a matcher that matches when the method is executed
     * zero or more times.
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/6461
     */
    final protected function any(): AnyInvokedCountMatcher
    {
        Event\Facade::emitter()->testTriggeredPhpunitDeprecation(
            $this->valueObjectForEvents(),
            'The any() invoked count expectation is deprecated and will be removed in PHPUnit 14. ' .
            'Use a test stub instead or configure a real invocation count expectation.',
        );

        return new AnyInvokedCountMatcher;
    }

    /**
     * Returns a matcher that matches when the method is never executed.
     */
    final protected function never(): InvokedCountMatcher
    {
        return new InvokedCountMatcher(0);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at least N times.
     */
    final protected function atLeast(int $requiredInvocations): InvokedAtLeastCountMatcher
    {
        if ($requiredInvocations < 1) {
            Event\Facade::emitter()->testTriggeredPhpunitDeprecation(
                $this->valueObjectForEvents(),
                'Calling atLeast() with an argument that is not positive is deprecated.' . PHP_EOL .
                'This will become an error in PHPUnit 14.',
            );
        }

        return new InvokedAtLeastCountMatcher(
            $requiredInvocations,
        );
    }

    /**
     * Returns a matcher that matches when the method is executed at least once.
     */
    final protected function atLeastOnce(): InvokedAtLeastOnceMatcher
    {
        return new InvokedAtLeastOnceMatcher;
    }

    /**
     * Returns a matcher that matches when the method is executed exactly once.
     */
    final protected function once(): InvokedCountMatcher
    {
        return new InvokedCountMatcher(1);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * exactly $count times.
     */
    final protected function exactly(int $count): InvokedCountMatcher
    {
        return new InvokedCountMatcher($count);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at most N times.
     */
    final protected function atMost(int $allowedInvocations): InvokedAtMostCountMatcher
    {
        return new InvokedAtMostCountMatcher($allowedInvocations);
    }

    final protected function throwException(Throwable $exception): ExceptionStub
    {
        return new ExceptionStub($exception);
    }

    final protected function getActualOutputForAssertion(): string
    {
        return $this->outputBuffer->getActualOutputForAssertion();
    }

    final protected function expectOutputRegex(string $expectedRegex): void
    {
        $this->outputBuffer->expectRegularExpression($expectedRegex);
    }

    final protected function expectOutputString(string $expectedString): void
    {
        $this->warnAboutConflictingOutputStringExpectation($expectedString);

        $this->outputBuffer->expectString($expectedString);
    }

    final protected function expectErrorLog(): void
    {
        $this->errorLogCapture->expect();
    }

    /**
     * @param class-string<Throwable> $exception
     */
    final protected function expectException(string $exception): void
    {
        $this->exceptionExpectation->expectClass($exception);
    }

    final protected function expectExceptionCode(int|string $code): void
    {
        $this->exceptionExpectation->expectCode($code);
    }

    /**
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/6560
     */
    final protected function expectExceptionMessage(string $message): void
    {
        $this->expectExceptionMessageIsOrContains($message);
    }

    final protected function expectExceptionMessageIs(string $message): void
    {
        $this->exceptionExpectation->expectMessageIs($message);
    }

    final protected function expectExceptionMessageIsOrContains(string $message): void
    {
        $this->exceptionExpectation->expectMessageIsOrContains($message);
    }

    final protected function expectExceptionMessageMatches(string $regularExpression): void
    {
        $this->exceptionExpectation->expectMessageMatches($regularExpression);
    }

    /**
     * Sets up an expectation for an exception to be raised by the code under test.
     * Information for expected exception class, expected exception message, and
     * expected exception code are retrieved from a given Exception object.
     */
    final protected function expectExceptionObject(Throwable $exception): void
    {
        $this->expectException($exception::class);
        $this->expectExceptionMessageIsOrContains($exception->getMessage());
        $this->expectExceptionCode($exception->getCode());
    }

    final protected function expectNotToPerformAssertions(): void
    {
        $this->doesNotPerformAssertions = true;
    }

    /**
     * @param non-empty-string $expectedUserDeprecationMessage
     */
    final protected function expectUserDeprecationMessage(string $expectedUserDeprecationMessage): void
    {
        $this->deprecationExpectation->expectMessage($expectedUserDeprecationMessage);
    }

    /**
     * @param non-empty-string $expectedUserDeprecationMessageRegularExpression
     */
    final protected function expectUserDeprecationMessageMatches(string $expectedUserDeprecationMessageRegularExpression): void
    {
        $this->deprecationExpectation->expectMessageMatches($expectedUserDeprecationMessageRegularExpression);
    }

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $className
     *
     * @return MockBuilder<RealInstanceType>
     */
    final protected function getMockBuilder(string $className): MockBuilder
    {
        return new MockBuilder($this, $className);
    }

    final protected function registerComparator(Comparator $comparator): void
    {
        $this->customRegistrations->registerComparator($comparator);

        Event\Facade::emitter()->testRegisteredComparator($comparator::class);
    }

    final protected function registerObjectExporter(ObjectExporter $objectExporter): void
    {
        $this->customRegistrations->registerObjectExporter($objectExporter);
    }

    /**
     * @param class-string $classOrInterface
     */
    final protected function registerFailureType(string $classOrInterface): void
    {
        $this->failureTypes[$classOrInterface] = true;
    }

    /**
     * Creates a mock object for the specified interface or class.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return MockObject&RealInstanceType
     */
    final protected function createMock(string $type): MockObject
    {
        return self::testDoubleFactory()->createMock($this, $type);
    }

    /**
     * @param list<class-string> $interfaces
     *
     * @throws MockObjectException
     */
    final protected function createMockForIntersectionOfInterfaces(array $interfaces): MockObject
    {
        return self::testDoubleFactory()->createMockForIntersectionOfInterfaces($this, $interfaces);
    }

    /**
     * Creates a journal that the invocations of methods of mock objects can be
     * recorded in, in the order in which they happen.
     */
    final protected function createInvocationJournal(): InvocationJournal
    {
        return new InvocationJournalImplementation;
    }

    /**
     * Creates (and configures) a mock object for the specified interface or class.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     * @param array<non-empty-string, mixed> $configuration
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return MockObject&RealInstanceType
     */
    final protected function createConfiguredMock(string $type, array $configuration): MockObject
    {
        return self::testDoubleFactory()->createConfiguredMock($this, $type, $configuration);
    }

    /**
     * Creates a partial mock object for the specified interface or class.
     *
     * @param class-string<RealInstanceType> $type
     * @param list<non-empty-string>         $methods
     *
     * @template RealInstanceType of object
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     *
     * @return MockObject&RealInstanceType
     */
    final protected function createPartialMock(string $type, array $methods): MockObject
    {
        return self::testDoubleFactory()->createPartialMock($this, $type, $methods);
    }

    /**
     * @param non-empty-string $additionalInformation
     */
    final protected function provideAdditionalInformation(string $additionalInformation): void
    {
        Event\Facade::emitter()->testProvidedAdditionalInformation(
            $this->valueObjectForEvents(),
            $additionalInformation,
        );
    }

    protected function transformException(Throwable $t): Throwable
    {
        return $t;
    }

    /**
     * This method is called when a test method did not execute successfully.
     *
     * @throws Throwable
     */
    protected function onNotSuccessfulTest(Throwable $t): never
    {
        throw $t;
    }

    /**
     * @param array<mixed> $testArguments
     */
    protected function invokeTestMethod(string $methodName, array $testArguments): mixed
    {
        /** @phpstan-ignore method.dynamicName */
        return $this->{$methodName}(...$testArguments);
    }

    private function prepareEnvironment(): void
    {
        error_clear_last();
        clearstatcache();

        $this->emitter->testPreparationStarted(
            $this->valueObjectForEvents(),
        );

        $this->globalStateCapture->snapshotGlobals($this, $this->emitter, $this->inIsolation, $this->runTestInSeparateProcess);
        $this->globalStateCapture->snapshotErrorHandlers($this, $this->emitter);
        $this->environmentVariables->set(static::class, $this->methodName);
        $this->outputBuffer->start();

        $this->numberOfAssertionsPerformed = 0;
    }

    private function restoreEnvironment(false|string $currentWorkingDirectory): void
    {
        clearstatcache();

        if ($currentWorkingDirectory !== false && $currentWorkingDirectory !== getcwd()) {
            chdir($currentWorkingDirectory);
        }

        $this->environmentVariables->restore();
        $this->globalStateCapture->restoreErrorHandlers($this, $this->emitter, $this->inIsolation);
        $this->globalStateCapture->restoreGlobals($this, $this->emitter);
        $this->customRegistrations->unregisterAll();
        libxml_clear_errors();

        $this->testValueObjectForEvents = null;
    }

    /**
     * A previously registered error handler may have turned an issue that
     * was triggered before this test was run, in a data provider for
     * example, into an exception: the exception is control flow of this
     * test and must be handled as if it was thrown while this test was
     * prepared.
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/6831
     *
     * @throws Throwable
     */
    private function throwThrowableFromDeferredIssue(): void
    {
        if ($this->throwableFromDeferredIssue === null) {
            return;
        }

        $throwableFromDeferredIssue = $this->throwableFromDeferredIssue;

        $this->throwableFromDeferredIssue = null;

        throw $throwableFromDeferredIssue;
    }

    /**
     * @param HookMethodsByType $hookMethods
     *
     * @throws Throwable
     */
    private function prepareTest(array $hookMethods): void
    {
        if ($this->emptyDataProviderSkipMessage !== null) {
            $this->markTestSkipped($this->emptyDataProviderSkipMessage);
        }

        if ($this->inIsolation) {
            // @codeCoverageIgnoreStart
            HookMethodInvoker::invokeBeforeClass($this, $hookMethods, $this->emitter);
            // @codeCoverageIgnoreEnd
        }

        if (method_exists(static::class, $this->methodName) &&
            MetadataRegistry::parser()->forClassAndMethod(static::class, $this->methodName)->isDoesNotPerformAssertions()->isNotEmpty()) {
            $this->doesNotPerformAssertions = true;
        }

        HookMethodInvoker::invokeBeforeTest($this, $hookMethods, $this->emitter);
        HookMethodInvoker::invokePreCondition($this, $hookMethods, $this->emitter);

        $this->emitter->testPrepared(
            $this->valueObjectForEvents(),
        );

        $this->wasPrepared = true;
    }

    /**
     * @param HookMethodsByType $hookMethods
     *
     * @throws Throwable
     */
    private function verifyTest(array $hookMethods): void
    {
        $this->deprecationExpectation->verify($this);
        $this->mockObjectRegistry->verify($this, $this->emitter);

        HookMethodInvoker::invokePostCondition($this, $hookMethods, $this->emitter);
    }

    /**
     * Sets the status of the test and emits the events for a throwable that
     * escaped from preparing, running, or verifying the test.
     *
     * Returns the throwable that is to be passed to onNotSuccessfulTest() or
     * null when the throwable is of a registered failure type.
     */
    private function handleThrowableFromTest(Throwable $t): ?Throwable
    {
        if ($t instanceof IncompleteTest) {
            $this->status = TestStatus::incomplete($t->getMessage());

            $this->emitter->testMarkedAsIncomplete(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($t),
            );

            return $t;
        }

        if ($t instanceof SkippedTest) {
            $this->status = TestStatus::skipped($t->getMessage());

            /** @var non-empty-string $skipMessage */
            $skipMessage = $t->getMessage();

            $this->emitter->testSkipped(
                $this->valueObjectForEvents(),
                $skipMessage,
            );

            return $t;
        }

        if ($t instanceof AssertionError || $t instanceof AssertionFailedError) {
            $this->mockObjectRegistry->handleExceptionFromInvokedCountRule($this, $t);

            if (!$this->wasPrepared) {
                $this->wasPrepared = true;

                $this->emitter->testPreparationFailed(
                    $this->valueObjectForEvents(),
                    Event\Code\ThrowableBuilder::from($t),
                );
            }

            $this->status = TestStatus::failure($t->getMessage());

            $this->emitter->testFailed(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($t),
                Event\Code\ComparisonFailureBuilder::from($t),
            );

            return $t;
        }

        if ($t instanceof TimeoutException) {
            return $t;
        }

        if ($this->isRegisteredFailure($t)) {
            $this->status = TestStatus::failure($t->getMessage());

            $this->emitter->testFailed(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($t),
                null,
            );

            return null;
        }

        $e = $this->transformException($t);

        $this->status = TestStatus::error($e->getMessage());

        if (!$this->wasPrepared) {
            if ($e instanceof AssertionFailedError) {
                $this->emitter->testPreparationFailed(
                    $this->valueObjectForEvents(),
                    Event\Code\ThrowableBuilder::from($e),
                );
            } else {
                $this->emitter->testPreparationErrored(
                    $this->valueObjectForEvents(),
                    Event\Code\ThrowableBuilder::from($e),
                );
            }
        }

        $this->emitter->testErrored(
            $this->valueObjectForEvents(),
            Event\Code\ThrowableBuilder::from($e),
        );

        return $e;
    }

    private function stopOutputBuffering(): OutputBufferStopResult
    {
        $stopResult = $this->outputBuffer->stop();

        if ($stopResult->riskyMessage !== null) {
            $this->emitter->testConsideredRisky(
                $this->valueObjectForEvents(),
                $stopResult->riskyMessage,
            );
        }

        return $stopResult;
    }

    private function performOutputAssertions(): ?Throwable
    {
        try {
            $this->outputBuffer->performAssertions();
        } catch (ExpectationFailedException $e) {
            $this->status = TestStatus::failure($e->getMessage());

            $this->emitter->testFailed(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($e),
                Event\Code\ComparisonFailureBuilder::from($e),
            );

            return $e;
        }

        return null;
    }

    /**
     * Returns the throwable raised by the destructor of a mock object, if any.
     */
    private function discardMockObjects(): ?Throwable
    {
        try {
            $this->mockObjectRegistry->clear();
        } catch (Throwable $t) {
            $this->emitter->testErrored(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($t),
            );

            return $t;
        }

        return null;
    }

    /**
     * Tears down the fixture. An exception raised in tearDown() is passed on
     * when no exception was raised before.
     *
     * @param HookMethodsByType $hookMethods
     */
    private function tearDownTest(array $hookMethods, ?Throwable $e): ?Throwable
    {
        try {
            HookMethodInvoker::invokeAfterTest($this, $hookMethods, $this->emitter);

            if ($this->inIsolation) {
                // @codeCoverageIgnoreStart
                HookMethodInvoker::invokeAfterClass($this, $hookMethods, $this->emitter);
                // @codeCoverageIgnoreEnd
            }
        } catch (AssertionError|AssertionFailedError $t) {
            $this->status = TestStatus::failure($t->getMessage());

            $this->emitter->testFailed(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($t),
                Event\Code\ComparisonFailureBuilder::from($t),
            );

            return $t;
        } catch (Throwable $t) {
            if ($e === null || $e instanceof SkippedWithMessageException) {
                $this->status = TestStatus::error($t->getMessage());

                $this->emitter->testErrored(
                    $this->valueObjectForEvents(),
                    Event\Code\ThrowableBuilder::from($t),
                );

                return $t;
            }
        }

        return $e;
    }

    private function registerAsPassed(): void
    {
        $this->emitter->testPassed(
            $this->valueObjectForEvents(),
        );

        // a repeated test method is registered as passed once all of its
        // repetitions have finished without failure or error
        if (!$this->usesDataProvider() && $this->totalRepetitions === 1) {
            PassedTests::instance()->testMethodPassed(
                $this->valueObjectForEvents(),
                $this->testResult,
            );
        }
    }

    /**
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws Throwable
     */
    private function runTest(): mixed
    {
        $testArguments = array_merge($this->dataSet->data(), array_values($this->dependencyInput));

        try {
            $testResult = $this->invokeTestMethod($this->methodName, $testArguments);

            $this->errorLogCapture->verify();
        } catch (Throwable $exception) {
            if (!$this->exceptionExpectation->shouldBeVerifiedFor($exception)) {
                throw $exception;
            }

            $this->exceptionExpectation->verify($exception);

            return null;
        } finally {
            $this->errorLogCapture->stop();
        }

        $this->emitEventForCustomTestMethodInvocation();
        $this->exceptionExpectation->assertWasRaised($this);

        return $testResult;
    }

    /**
     * @throws SkippedTest
     */
    private function checkRequirements(): void
    {
        $missingRequirements = new Requirements($this->emitter)->requirementsNotSatisfiedFor(
            static::class,
            $this->methodName,
        );

        if ($missingRequirements !== []) {
            $this->markTestSkipped(implode(PHP_EOL, $missingRequirements));
        }
    }

    private function isRegisteredFailure(Throwable $t): bool
    {
        return array_any(
            array_keys($this->failureTypes),
            static fn (string $failureType) => $t instanceof $failureType,
        );
    }

    private function emitEventForCustomTestMethodInvocation(): void
    {
        $reflector = new ReflectionMethod($this, 'invokeTestMethod');

        if (self::class === $reflector->getDeclaringClass()->getName()) {
            return;
        }

        Event\Facade::emitter()->testUsedCustomMethodInvocation(
            $this->valueObjectForEvents(),
            new Event\Code\ClassMethod(
                $reflector->getDeclaringClass()->getName(),
                'invokeTestMethod',
            ),
        );
    }

    private function warnAboutConflictingOutputStringExpectation(string $expectedString): void
    {
        if ($this->outputBuffer->conflictsWithExpectedString($expectedString)) {
            Event\Facade::emitter()->testTriggeredPhpunitWarning(
                $this->valueObjectForEvents(),
                'Output cannot be expected to be identical to more than one string; expectOutputString() was already called with a different argument',
            );
        }
    }

    /**
     * Returns a builder object to create test stubs using a fluent interface.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $className
     *
     * @return TestStubBuilder<RealInstanceType>
     */
    final protected static function getStubBuilder(string $className): TestStubBuilder
    {
        return new TestStubBuilder($className);
    }

    /**
     * Creates a test stub for the specified interface or class.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return RealInstanceType&Stub
     */
    final protected static function createStub(string $type): Stub
    {
        return self::testDoubleFactory()->createStub($type);
    }

    /**
     * @param list<class-string> $interfaces
     *
     * @throws MockObjectException
     */
    final protected static function createStubForIntersectionOfInterfaces(array $interfaces): Stub
    {
        return self::testDoubleFactory()->createStubForIntersectionOfInterfaces($interfaces);
    }

    /**
     * Creates (and configures) a test stub for the specified interface or class.
     *
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     * @param array<non-empty-string, mixed> $configuration
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return RealInstanceType&Stub
     */
    final protected static function createConfiguredStub(string $type, array $configuration): Stub
    {
        return self::testDoubleFactory()->createConfiguredStub($type, $configuration);
    }

    private static function testDoubleFactory(): TestDoubleFactory
    {
        return new TestDoubleFactory(static::class, Event\Facade::emitter());
    }
}
