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
use function class_exists;
use function clearstatcache;
use function error_clear_last;
use function getcwd;
use function implode;
use function in_array;
use function is_array;
use function is_callable;
use function is_int;
use function is_object;
use function libxml_clear_errors;
use function method_exists;
use function preg_match;
use function putenv;
use function sprintf;
use function str_contains;
use function str_starts_with;
use AssertionError;
use DeepCopy\DeepCopy;
use PHPUnit\Event;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Framework\MockObject\Generator\Generator as MockGenerator;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\MockObjectInternal;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtLeastCount as InvokedAtLeastCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtLeastOnce as InvokedAtLeastOnceMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtMostCount as InvokedAtMostCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Stub\Exception as ExceptionStub;
use PHPUnit\Framework\MockObject\TestStubBuilder;
use PHPUnit\Framework\TestCase\ErrorLogCapture;
use PHPUnit\Framework\TestCase\ExceptionExpectation;
use PHPUnit\Framework\TestCase\GlobalStateCapture;
use PHPUnit\Framework\TestCase\HookMethodInvoker;
use PHPUnit\Framework\TestCase\OutputBuffer;
use PHPUnit\Framework\TestRunner\SeparateProcessTestRunner;
use PHPUnit\Framework\TestRunner\TestRunner;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Metadata\Api\HookMethods;
use PHPUnit\Metadata\Api\Requirements;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Metadata\WithEnvironmentVariable;
use PHPUnit\Runner\BackedUpEnvironmentVariable;
use PHPUnit\Runner\DeprecationCollector\Facade as DeprecationCollector;
use PHPUnit\Runner\ShutdownHandler;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\Util\Exporter;
use PHPUnit\Util\Sanitizer;
use ReflectionClass;
use ReflectionMethod;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\Invoker\TimeoutException;
use SebastianBergmann\ObjectEnumerator\Enumerator;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class TestCase extends Assert implements Reorderable, SelfDescribing, Test
{
    private GlobalStateCapture $globalStateCapture;
    private ?bool $runTestInSeparateProcess = null;
    private bool $preserveGlobalState       = false;
    private bool $inIsolation               = false;
    private ExceptionExpectation $exceptionExpectation;

    /**
     * @var list<BackedUpEnvironmentVariable>
     */
    private array $backupEnvironmentVariables = [];

    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $providedTests = [];

    /**
     * @var array<mixed>
     */
    private array $data          = [];
    private int|string $dataName = '';

    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * @var list<string>
     */
    private array $groups = [];

    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $dependencies = [];

    /**
     * @var array<string, mixed>
     */
    private array $dependencyInput = [];

    /**
     * @var list<array{type: non-empty-string, mockObject: MockObjectInternal}>
     */
    private array $mockObjects = [];
    private TestStatus $status;

    /**
     * @var non-negative-int
     */
    private int $numberOfAssertionsPerformed = 0;
    private mixed $testResult                = null;
    private bool $doesNotPerformAssertions   = false;
    private OutputBuffer $outputBuffer;
    private ErrorLogCapture $errorLogCapture;

    /**
     * @var list<Comparator>
     */
    private array $customComparators                         = [];
    private ?Event\Code\TestMethod $testValueObjectForEvents = null;
    private bool $wasPrepared                                = false;

    /**
     * @var array<class-string, true>
     */
    private array $failureTypes = [];

    /**
     * @var list<non-empty-string>
     */
    private array $expectedUserDeprecationMessage = [];

    /**
     * @var list<non-empty-string>
     */
    private array $expectedUserDeprecationMessageRegularExpression = [];
    private ?string $emptyDataProviderSkipMessage                  = null;

    /**
     * @param non-empty-string $name
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function __construct(string $name)
    {
        $this->methodName           = $name;
        $this->status               = TestStatus::unknown();
        $this->exceptionExpectation = new ExceptionExpectation;
        $this->outputBuffer         = new OutputBuffer;
        $this->errorLogCapture      = new ErrorLogCapture;
        $this->globalStateCapture   = new GlobalStateCapture;

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
            new ReflectionClass($this)->getName(),
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
        if (!$this->handleDependencies()) {
            return;
        }

        if (!$this->shouldRunInSeparateProcess() || $this->requirementsNotSatisfied()) {
            try {
                ShutdownHandler::setMessage(sprintf('Fatal error: Premature end of PHP process when running %s.', $this->toString()));
                (new TestRunner)->run($this);
            } finally {
                ShutdownHandler::resetMessage();
            }

            return;
        }

        (new SeparateProcessTestRunner)->run(
            $this,
            $this->preserveGlobalState,
            $this->requiresXdebug(),
        );
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
    final public function runBare(): void
    {
        $emitter = Event\Facade::emitter();

        error_clear_last();
        clearstatcache();

        $emitter->testPreparationStarted(
            $this->valueObjectForEvents(),
        );

        $this->globalStateCapture->snapshotGlobals($this, $emitter, $this->inIsolation, $this->runTestInSeparateProcess);
        $this->globalStateCapture->snapshotErrorHandlers($this, $emitter);
        $this->handleEnvironmentVariables();
        $this->outputBuffer->start();

        $hookMethods                       = (new HookMethods)->hookMethods(static::class);
        $hasMetRequirements                = false;
        $this->numberOfAssertionsPerformed = 0;
        $currentWorkingDirectory           = getcwd();

        try {
            $this->checkRequirements();
            $hasMetRequirements = true;

            if ($this->emptyDataProviderSkipMessage !== null) {
                $this->markTestSkipped($this->emptyDataProviderSkipMessage);
            }

            if ($this->inIsolation) {
                // @codeCoverageIgnoreStart
                HookMethodInvoker::invokeBeforeClass($this, $hookMethods, $emitter);
                // @codeCoverageIgnoreEnd
            }

            if (method_exists(static::class, $this->methodName) &&
                MetadataRegistry::parser()->forClassAndMethod(static::class, $this->methodName)->isDoesNotPerformAssertions()->isNotEmpty()) {
                $this->doesNotPerformAssertions = true;
            }

            HookMethodInvoker::invokeBeforeTest($this, $hookMethods, $emitter);
            HookMethodInvoker::invokePreCondition($this, $hookMethods, $emitter);

            $emitter->testPrepared(
                $this->valueObjectForEvents(),
            );

            $this->wasPrepared = true;
            $this->testResult  = $this->runTest();

            $this->verifyDeprecationExpectations();
            $this->verifyMockObjects();
            HookMethodInvoker::invokePostCondition($this, $hookMethods, $emitter);

            $this->status = TestStatus::success();
        } catch (IncompleteTest $e) {
            $this->status = TestStatus::incomplete($e->getMessage());

            $emitter->testMarkedAsIncomplete(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($e),
            );
        } catch (SkippedTest $e) {
            $this->status = TestStatus::skipped($e->getMessage());

            /** @var non-empty-string $skipMessage */
            $skipMessage = $e->getMessage();

            $emitter->testSkipped(
                $this->valueObjectForEvents(),
                $skipMessage,
            );
        } catch (AssertionError|AssertionFailedError $e) {
            $this->handleExceptionFromInvokedCountMockObjectRule($e);

            if (!$this->wasPrepared) {
                $this->wasPrepared = true;

                $emitter->testPreparationFailed(
                    $this->valueObjectForEvents(),
                    Event\Code\ThrowableBuilder::from($e),
                );
            }

            $this->status = TestStatus::failure($e->getMessage());

            $emitter->testFailed(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($e),
                Event\Code\ComparisonFailureBuilder::from($e),
            );
        } catch (TimeoutException $e) {
        } catch (Throwable $_e) {
            if ($this->isRegisteredFailure($_e)) {
                $this->status = TestStatus::failure($_e->getMessage());

                $emitter->testFailed(
                    $this->valueObjectForEvents(),
                    Event\Code\ThrowableBuilder::from($_e),
                    null,
                );
            } else {
                $e = $this->transformException($_e);

                $this->status = TestStatus::error($e->getMessage());

                if (!$this->wasPrepared) {
                    if ($e instanceof AssertionFailedError) {
                        $emitter->testPreparationFailed(
                            $this->valueObjectForEvents(),
                            Event\Code\ThrowableBuilder::from($e),
                        );
                    } else {
                        $emitter->testPreparationErrored(
                            $this->valueObjectForEvents(),
                            Event\Code\ThrowableBuilder::from($e),
                        );
                    }
                }

                $emitter->testErrored(
                    $this->valueObjectForEvents(),
                    Event\Code\ThrowableBuilder::from($e),
                );
            }
        }

        $outputBufferingStopped = false;

        if (!isset($e) && $this->outputBuffer->hasExpectation()) {
            $stopResult = $this->outputBuffer->stop();

            if ($stopResult->riskyMessage !== null) {
                $emitter->testConsideredRisky(
                    $this->valueObjectForEvents(),
                    $stopResult->riskyMessage,
                );
            }

            if ($stopResult->closedCleanly) {
                $outputBufferingStopped = true;

                try {
                    $this->outputBuffer->performAssertions();
                } catch (ExpectationFailedException $e) {
                    $this->status = TestStatus::failure($e->getMessage());

                    $emitter->testFailed(
                        $this->valueObjectForEvents(),
                        Event\Code\ThrowableBuilder::from($e),
                        Event\Code\ComparisonFailureBuilder::from($e),
                    );
                }
            }
        }

        try {
            $this->mockObjects = [];

            /** @phpstan-ignore catch.neverThrown */
        } catch (Throwable $e) {
            Event\Facade::emitter()->testErrored(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($e),
            );
        }

        // Tear down the fixture. An exception raised in tearDown() will be
        // caught and passed on when no exception was raised before.
        try {
            if ($hasMetRequirements) {
                HookMethodInvoker::invokeAfterTest($this, $hookMethods, $emitter);

                if ($this->inIsolation) {
                    // @codeCoverageIgnoreStart
                    HookMethodInvoker::invokeAfterClass($this, $hookMethods, $emitter);
                    // @codeCoverageIgnoreEnd
                }
            }
        } catch (AssertionError|AssertionFailedError $e) {
            $this->status = TestStatus::failure($e->getMessage());

            $emitter->testFailed(
                $this->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($e),
                Event\Code\ComparisonFailureBuilder::from($e),
            );
        } catch (Throwable $exceptionRaisedDuringTearDown) {
            if (!isset($e) || $e instanceof SkippedWithMessageException) {
                $this->status = TestStatus::error($exceptionRaisedDuringTearDown->getMessage());
                $e            = $exceptionRaisedDuringTearDown;

                $emitter->testErrored(
                    $this->valueObjectForEvents(),
                    Event\Code\ThrowableBuilder::from($exceptionRaisedDuringTearDown),
                );
            }
        }

        if (!isset($e) && !isset($_e)) {
            $emitter->testPassed(
                $this->valueObjectForEvents(),
            );

            if (!$this->usesDataProvider()) {
                PassedTests::instance()->testMethodPassed(
                    $this->valueObjectForEvents(),
                    $this->testResult,
                );
            }
        }

        if (!$outputBufferingStopped) {
            $stopResult = $this->outputBuffer->stop();

            if ($stopResult->riskyMessage !== null) {
                $emitter->testConsideredRisky(
                    $this->valueObjectForEvents(),
                    $stopResult->riskyMessage,
                );
            }
        }

        clearstatcache();

        if ($currentWorkingDirectory !== false && $currentWorkingDirectory !== getcwd()) {
            chdir($currentWorkingDirectory);
        }

        $this->restoreEnvironmentVariables();
        $this->globalStateCapture->restoreErrorHandlers($this, $emitter, $this->inIsolation);
        $this->globalStateCapture->restoreGlobals($this, $emitter);
        $this->unregisterCustomComparators();
        libxml_clear_errors();

        $this->testValueObjectForEvents = null;

        if (isset($e)) {
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
     *
     * @codeCoverageIgnore
     */
    final public function setDependencyInput(array $dependencyInput): void
    {
        $this->dependencyInput = $dependencyInput;
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
    final public function setRunTestInSeparateProcess(bool $runTestInSeparateProcess): void
    {
        if ($this->runTestInSeparateProcess === null) {
            $this->runTestInSeparateProcess = $runTestInSeparateProcess;
        }
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
     *
     * @codeCoverageIgnore
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
        assert($mockObject instanceof MockObjectInternal);

        $this->mockObjects[] = [
            'type'       => $type,
            'mockObject' => $mockObject,
        ];
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
        return $this->data !== [];
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function dataName(): int|string
    {
        return $this->dataName;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function dataSetAsString(): string
    {
        if ($this->data !== []) {
            if (is_int($this->dataName)) {
                return sprintf(' with data set #%s', $this->dataName);
            }

            return sprintf(
                ' with data set "%s"',
                Sanitizer::sanitizeBidirectionalControlCharacters($this->dataName),
            );
        }

        return '';
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function dataSetAsStringWithData(): string
    {
        if ($this->data === []) {
            return '';
        }

        if (is_int($this->dataName)) {
            $dataName = sprintf('#%d', $this->dataName);
        } else {
            $dataName = sprintf(
                '@%s',
                Sanitizer::sanitizeBidirectionalControlCharacters($this->dataName),
            );
        }

        return sprintf(
            '%s with data (%s)',
            $dataName,
            Exporter::shortenedRecursiveExport($this->data),
        );
    }

    /**
     * @return array<mixed>
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    final public function providedData(): array
    {
        return $this->data;
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
        $this->dataName = $dataName;
        $this->data     = $data;
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
     * Returns a matcher that matches when the method is executed
     * zero or more times.
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/6461
     */
    final protected function any(): AnyInvokedCountMatcher
    {
        Event\Facade::emitter()->testTriggeredPhpunitDeprecation(
            $this->testValueObjectForEvents,
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
        $this->warnAboutMultipleOutputExpectations();

        $this->outputBuffer->expectRegularExpression($expectedRegex);
    }

    final protected function expectOutputString(string $expectedString): void
    {
        $this->warnAboutMultipleOutputExpectations();

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
        $this->expectedUserDeprecationMessage[] = $expectedUserDeprecationMessage;
    }

    /**
     * @param non-empty-string $expectedUserDeprecationMessageRegularExpression
     */
    final protected function expectUserDeprecationMessageMatches(string $expectedUserDeprecationMessageRegularExpression): void
    {
        $this->expectedUserDeprecationMessageRegularExpression[] = $expectedUserDeprecationMessageRegularExpression;
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
        ComparatorFactory::getInstance()->register($comparator);

        Event\Facade::emitter()->testRegisteredComparator($comparator::class);

        $this->customComparators[] = $comparator;
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
        $mock = (new MockGenerator)->testDouble(
            $type,
            true,
            callOriginalConstructor: false,
            callOriginalClone: false,
            returnValueGeneration: self::generateReturnValuesForTestDoubles(),
        );

        assert($mock instanceof $type);
        assert($mock instanceof MockObject);

        $this->registerMockObject($type, $mock);

        Event\Facade::emitter()->testCreatedMockObject($type);

        return $mock;
    }

    /**
     * @param list<class-string> $interfaces
     *
     * @throws MockObjectException
     */
    final protected function createMockForIntersectionOfInterfaces(array $interfaces): MockObject
    {
        $mock = (new MockGenerator)->testDoubleForInterfaceIntersection(
            $interfaces,
            true,
            returnValueGeneration: self::generateReturnValuesForTestDoubles(),
        );

        assert($mock instanceof MockObject);

        $type = implode('|', $interfaces);

        assert($type !== '');

        $this->registerMockObject($type, $mock);

        Event\Facade::emitter()->testCreatedMockObjectForIntersectionOfInterfaces($interfaces);

        return $mock;
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
        $o = $this->createMock($type);

        foreach ($configuration as $method => $return) {
            $o->method($method)->willReturn($return);
        }

        return $o;
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
        $mockBuilder = $this->getMockBuilder($type)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods($methods);

        if (!self::generateReturnValuesForTestDoubles()) {
            $mockBuilder->disableAutoReturnValueGeneration();
        }

        $partialMock = $mockBuilder->getMock();

        Event\Facade::emitter()->testCreatedPartialMockObject(
            $type,
            ...$methods,
        );

        return $partialMock;
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

    /**
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws Throwable
     */
    private function runTest(): mixed
    {
        $testArguments = array_merge($this->data, array_values($this->dependencyInput));

        $this->errorLogCapture->start();

        try {
            $testResult = $this->invokeTestMethod($this->methodName, $testArguments);

            $this->errorLogCapture->verify();
        } catch (Throwable $exception) {
            $this->errorLogCapture->handleError();

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
     * @throws ExpectationFailedException
     */
    private function verifyDeprecationExpectations(): void
    {
        foreach ($this->expectedUserDeprecationMessage as $deprecationExpectation) {
            $this->numberOfAssertionsPerformed++;

            if (!in_array($deprecationExpectation, DeprecationCollector::deprecations(), true)) {
                throw new ExpectationFailedException(
                    sprintf(
                        'Expected deprecation with message "%s" was not triggered',
                        $deprecationExpectation,
                    ),
                );
            }
        }

        foreach ($this->expectedUserDeprecationMessageRegularExpression as $deprecationExpectation) {
            $this->numberOfAssertionsPerformed++;

            $expectedDeprecationTriggered = array_any(
                DeprecationCollector::deprecations(),
                static fn (string $deprecation) => @preg_match($deprecationExpectation, $deprecation) > 0,
            );

            if (!$expectedDeprecationTriggered) {
                throw new ExpectationFailedException(
                    sprintf(
                        'Expected deprecation with message matching regular expression "%s" was not triggered',
                        $deprecationExpectation,
                    ),
                );
            }
        }
    }

    /**
     * @throws Throwable
     */
    private function verifyMockObjects(): void
    {
        $allowsMockObjectsWithoutExpectations = $this->allowsMockObjectsWithoutExpectations();
        $isPhpunitTestSuite                   = str_starts_with($this::class, 'PHPUnit\\');
        $requireSealedMockObjects             = ConfigurationRegistry::get()->requireSealedMockObjects();

        foreach ($this->mockObjects as $mockObject) {
            $mockedType = $mockObject['type'];
            $mockObject = $mockObject['mockObject'];

            if ($requireSealedMockObjects &&
                !$mockObject->__phpunit_getInvocationHandler()->isSealed()) {
                Event\Facade::emitter()->testConsideredRisky(
                    $this->valueObjectForEvents(),
                    sprintf(
                        'Mock object for %s has not been sealed',
                        $mockedType,
                    ),
                );
            }

            if (!$mockObject->__phpunit_hasInvocationCountRule()) {
                if (!$mockObject->__phpunit_hasParametersRule() &&
                    !$allowsMockObjectsWithoutExpectations &&
                    !$isPhpunitTestSuite) {
                    Event\Facade::emitter()->testTriggeredPhpunitNotice(
                        $this->valueObjectForEvents(),
                        sprintf(
                            'No expectations were configured for the mock object for %s. ' .
                            'Consider refactoring your test code to use a test stub instead. ' .
                            'The #[AllowMockObjectsWithoutExpectations] attribute can be used to opt out of this check.',
                            $mockedType,
                        ),
                    );
                }

                continue;
            }

            $this->numberOfAssertionsPerformed++;

            $mockObject->__phpunit_verify(
                $this->shouldInvocationMockerBeReset($mockObject),
            );
        }
    }

    /**
     * @throws SkippedTest
     */
    private function checkRequirements(): void
    {
        $missingRequirements = (new Requirements)->requirementsNotSatisfiedFor(
            static::class,
            $this->methodName,
        );

        if ($missingRequirements !== []) {
            $this->markTestSkipped(implode(PHP_EOL, $missingRequirements));
        }
    }

    private function handleDependencies(): bool
    {
        if ([] === $this->dependencies || $this->inIsolation) {
            return true;
        }

        $passedTests = PassedTests::instance();

        foreach ($this->dependencies as $dependency) {
            if ($dependency->targetIsClass()) {
                $dependencyClassName = $dependency->getTargetClassName();

                if (!class_exists($dependencyClassName)) {
                    $this->markErrorForInvalidDependency($dependency);

                    return false;
                }

                if (!$passedTests->hasTestClassPassed($dependencyClassName)) {
                    $this->markSkippedForMissingDependency($dependency);

                    return false;
                }
            } else {
                $dependencyTarget = $dependency->getTarget();

                if (!$passedTests->hasTestMethodPassed($dependencyTarget)) {
                    if (!$dependency->targetIsCallableTestMethod()) {
                        $this->markErrorForInvalidDependency($dependency);
                    } else {
                        $this->markSkippedForMissingDependency($dependency);
                    }

                    return false;
                }

                if ($passedTests->isGreaterThan($dependencyTarget, $this->size())) {
                    Event\Facade::emitter()->testConsideredRisky(
                        $this->valueObjectForEvents(),
                        'This test depends on a test that is larger than itself',
                    );

                    return true;
                }

                if (!$passedTests->hasReturnValue($dependencyTarget)) {
                    return true;
                }

                $returnValue = $passedTests->returnValue($dependencyTarget);

                if ($dependency->deepClone()) {
                    $deepCopy = new DeepCopy;
                    $deepCopy->skipUncloneable(false);

                    $this->dependencyInput[$dependencyTarget] = $deepCopy->copy($returnValue);
                } elseif ($dependency->shallowClone() && is_object($returnValue)) {
                    $this->dependencyInput[$dependencyTarget] = clone $returnValue;
                } else {
                    $this->dependencyInput[$dependencyTarget] = $returnValue;
                }
            }
        }

        $this->testValueObjectForEvents = null;

        return true;
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     */
    private function markErrorForInvalidDependency(?ExecutionOrderDependency $dependency = null): void
    {
        $message = 'This test has an invalid dependency';

        if ($dependency !== null) {
            $message = sprintf(
                'This test depends on "%s" which does not exist',
                $dependency->targetIsClass() ? $dependency->getTargetClassName() : $dependency->getTarget(),
            );
        }

        $exception = new InvalidDependencyException($message);

        Event\Facade::emitter()->testErrored(
            $this->valueObjectForEvents(),
            Event\Code\ThrowableBuilder::from($exception),
        );

        $this->status = TestStatus::error($message);
    }

    private function markSkippedForMissingDependency(ExecutionOrderDependency $dependency): void
    {
        $message = sprintf(
            'This test depends on "%s" to pass',
            $dependency->getTarget(),
        );

        Event\Facade::emitter()->testSkipped(
            $this->valueObjectForEvents(),
            $message,
        );

        $this->status = TestStatus::skipped($message);
    }

    private function handleEnvironmentVariables(): void
    {
        $withEnvironmentVariables = MetadataRegistry::parser()->forClassAndMethod(static::class, $this->methodName)->isWithEnvironmentVariable();

        $environmentVariables = [];

        foreach ($withEnvironmentVariables as $metadata) {
            assert($metadata instanceof WithEnvironmentVariable);

            $environmentVariables[$metadata->environmentVariableName()] = $metadata->value();
        }

        foreach ($environmentVariables as $environmentVariableName => $environmentVariableValue) {
            $this->backupEnvironmentVariables = [...$this->backupEnvironmentVariables, ...BackedUpEnvironmentVariable::create($environmentVariableName)];

            if ($environmentVariableValue === null) {
                unset($_ENV[$environmentVariableName]);
                putenv($environmentVariableName);
            } else {
                $_ENV[$environmentVariableName] = $environmentVariableValue;
                putenv("{$environmentVariableName}={$environmentVariableValue}");
            }
        }
    }

    private function restoreEnvironmentVariables(): void
    {
        foreach ($this->backupEnvironmentVariables as $backupEnvironmentVariable) {
            $backupEnvironmentVariable->restore();
        }

        $this->backupEnvironmentVariables = [];
    }

    private function shouldInvocationMockerBeReset(MockObject $mock): bool
    {
        $enumerator = new Enumerator;

        if (in_array($mock, $enumerator->enumerate($this->dependencyInput), true)) {
            return false;
        }

        if (!is_array($this->testResult) && !is_object($this->testResult)) {
            return true;
        }

        return !in_array($mock, $enumerator->enumerate($this->testResult), true);
    }

    private function unregisterCustomComparators(): void
    {
        $factory = ComparatorFactory::getInstance();

        foreach ($this->customComparators as $comparator) {
            $factory->unregister($comparator);
        }

        $this->customComparators = [];
    }

    private function shouldRunInSeparateProcess(): bool
    {
        if ($this->inIsolation) {
            return false;
        }

        if ($this->runTestInSeparateProcess === true) {
            return true;
        }

        return ConfigurationRegistry::get()->processIsolation();
    }

    private function isRegisteredFailure(Throwable $t): bool
    {
        return array_any(
            array_keys($this->failureTypes),
            static fn (string $failureType) => $t instanceof $failureType,
        );
    }

    private function requirementsNotSatisfied(): bool
    {
        return (new Requirements)->requirementsNotSatisfiedFor(static::class, $this->methodName) !== [];
    }

    private function requiresXdebug(): bool
    {
        return (new Requirements)->requiresXdebug(static::class, $this->methodName);
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/6095
     */
    private function handleExceptionFromInvokedCountMockObjectRule(Throwable $t): void
    {
        if (!$t instanceof ExpectationFailedException) {
            return;
        }

        $trace = $t->getTrace();

        if (isset($trace[0]['class']) && $trace[0]['class'] === InvokedCount::class) {
            $this->numberOfAssertionsPerformed++;
        }
    }

    private function allowsMockObjectsWithoutExpectations(): bool
    {
        return MetadataRegistry::parser()->forClassAndMethod(static::class, $this->methodName)->isAllowMockObjectsWithoutExpectations()->isNotEmpty();
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

    private function warnAboutMultipleOutputExpectations(): void
    {
        if ($this->outputBuffer->hasExpectation()) {
            Event\Facade::emitter()->testTriggeredPhpunitWarning(
                $this->valueObjectForEvents(),
                'Only one expectation on output can be configured: expectOutputString() and expectOutputRegex() cannot be combined and must not be called more than once',
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
        $stub = (new MockGenerator)->testDouble(
            $type,
            false,
            callOriginalConstructor: false,
            callOriginalClone: false,
            returnValueGeneration: self::generateReturnValuesForTestDoubles(),
        );

        Event\Facade::emitter()->testCreatedStub($type);

        assert($stub instanceof $type);
        assert($stub instanceof Stub);

        return $stub;
    }

    /**
     * @param list<class-string> $interfaces
     *
     * @throws MockObjectException
     */
    final protected static function createStubForIntersectionOfInterfaces(array $interfaces): Stub
    {
        $stub = (new MockGenerator)->testDoubleForInterfaceIntersection(
            $interfaces,
            false,
            returnValueGeneration: self::generateReturnValuesForTestDoubles(),
        );

        Event\Facade::emitter()->testCreatedStubForIntersectionOfInterfaces($interfaces);

        return $stub;
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
        $o = self::createStub($type);

        foreach ($configuration as $method => $return) {
            $o->method($method)->willReturn($return);
        }

        return $o;
    }

    private static function generateReturnValuesForTestDoubles(): bool
    {
        return MetadataRegistry::parser()->forClass(static::class)->isDisableReturnValueGenerationForTestDoubles()->isEmpty();
    }
}
