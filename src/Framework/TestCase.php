<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

use DeepCopy\DeepCopy;
use PHPUnit\Framework\Constraint\Exception as ExceptionConstraint;
use PHPUnit\Framework\Constraint\ExceptionCode;
use PHPUnit\Framework\Constraint\ExceptionMessage;
use PHPUnit\Framework\Constraint\ExceptionMessageRegularExpression;
use PHPUnit\Framework\MockObject\Generator as MockGenerator;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtIndex as InvokedAtIndexMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastCount as InvokedAtLeastCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastOnce as InvokedAtLeastOnceMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtMostCount as InvokedAtMostCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls as ConsecutiveCallsStub;
use PHPUnit\Framework\MockObject\Stub\Exception as ExceptionStub;
use PHPUnit\Framework\MockObject\Stub\ReturnArgument as ReturnArgumentStub;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback as ReturnCallbackStub;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf as ReturnSelfStub;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap as ReturnValueMapStub;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Util\GlobalState;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use Prophecy;
use Prophecy\Exception\Prediction\PredictionException;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\GlobalState\Blacklist;
use SebastianBergmann\GlobalState\Restorer;
use SebastianBergmann\GlobalState\Snapshot;
use SebastianBergmann\ObjectEnumerator\Enumerator;
use Text_Template;
use Throwable;

abstract class TestCase extends Assert implements Test, SelfDescribing
{
    private const LOCALE_CATEGORIES = [LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_NUMERIC, LC_TIME];

    /**
     * @var bool
     */
    protected $backupGlobals;

    /**
     * @var array
     */
    protected $backupGlobalsBlacklist = [];

    /**
     * @var bool
     */
    protected $backupStaticAttributes;

    /**
     * @var array
     */
    protected $backupStaticAttributesBlacklist = [];

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess;

    /**
     * @var bool
     */
    protected $preserveGlobalState = true;

    /**
     * @var bool
     */
    private $runClassInSeparateProcess;

    /**
     * @var bool
     */
    private $inIsolation = false;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $dataName;

    /**
     * @var bool
     */
    private $useErrorHandler;

    /**
     * @var null|string
     */
    private $expectedException;

    /**
     * @var string
     */
    private $expectedExceptionMessage;

    /**
     * @var string
     */
    private $expectedExceptionMessageRegExp;

    /**
     * @var null|int|string
     */
    private $expectedExceptionCode;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $dependencies = [];

    /**
     * @var array
     */
    private $dependencyInput = [];

    /**
     * @var array
     */
    private $iniSettings = [];

    /**
     * @var array
     */
    private $locale = [];

    /**
     * @var array
     */
    private $mockObjects = [];

    /**
     * @var MockGenerator
     */
    private $mockObjectGenerator;

    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $statusMessage = '';

    /**
     * @var int
     */
    private $numAssertions = 0;

    /**
     * @var TestResult
     */
    private $result;

    /**
     * @var mixed
     */
    private $testResult;

    /**
     * @var string
     */
    private $output = '';

    /**
     * @var string
     */
    private $outputExpectedRegex;

    /**
     * @var string
     */
    private $outputExpectedString;

    /**
     * @var mixed
     */
    private $outputCallback = false;

    /**
     * @var bool
     */
    private $outputBufferingActive = false;

    /**
     * @var int
     */
    private $outputBufferingLevel;

    /**
     * @var Snapshot
     */
    private $snapshot;

    /**
     * @var Prophecy\Prophet
     */
    private $prophet;

    /**
     * @var bool
     */
    private $beStrictAboutChangesToGlobalState = false;

    /**
     * @var bool
     */
    private $registerMockObjectsFromTestArgumentsRecursively = false;

    /**
     * @var string[]
     */
    private $warnings = [];

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var bool
     */
    private $doesNotPerformAssertions = false;

    /**
     * @var Comparator[]
     */
    private $customComparators = [];

    /**
     * Returns a matcher that matches when the method is executed
     * zero or more times.
     */
    public static function any(): AnyInvokedCountMatcher
    {
        return new AnyInvokedCountMatcher;
    }

    /**
     * Returns a matcher that matches when the method is never executed.
     */
    public static function never(): InvokedCountMatcher
    {
        return new InvokedCountMatcher(0);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at least N times.
     */
    public static function atLeast(int $requiredInvocations): InvokedAtLeastCountMatcher
    {
        return new InvokedAtLeastCountMatcher(
            $requiredInvocations
        );
    }

    /**
     * Returns a matcher that matches when the method is executed at least once.
     */
    public static function atLeastOnce(): InvokedAtLeastOnceMatcher
    {
        return new InvokedAtLeastOnceMatcher;
    }

    /**
     * Returns a matcher that matches when the method is executed exactly once.
     */
    public static function once(): InvokedCountMatcher
    {
        return new InvokedCountMatcher(1);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * exactly $count times.
     */
    public static function exactly(int $count): InvokedCountMatcher
    {
        return new InvokedCountMatcher($count);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at most N times.
     */
    public static function atMost(int $allowedInvocations): InvokedAtMostCountMatcher
    {
        return new InvokedAtMostCountMatcher($allowedInvocations);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at the given index.
     */
    public static function at(int $index): InvokedAtIndexMatcher
    {
        return new InvokedAtIndexMatcher($index);
    }

    /**
     * @param mixed $value
     */
    public static function returnValue($value): ReturnStub
    {
        return new ReturnStub($value);
    }

    public static function returnValueMap(array $valueMap): ReturnValueMapStub
    {
        return new ReturnValueMapStub($valueMap);
    }

    public static function returnArgument(int $argumentIndex): ReturnArgumentStub
    {
        return new ReturnArgumentStub($argumentIndex);
    }

    /**
     * @param mixed $callback
     */
    public static function returnCallback($callback): ReturnCallbackStub
    {
        return new ReturnCallbackStub($callback);
    }

    /**
     * Returns the current object.
     *
     * This method is useful when mocking a fluent interface.
     */
    public static function returnSelf(): ReturnSelfStub
    {
        return new ReturnSelfStub;
    }

    public static function throwException(Throwable $exception): ExceptionStub
    {
        return new ExceptionStub($exception);
    }

    public static function onConsecutiveCalls(...$args): ConsecutiveCallsStub
    {
        return new ConsecutiveCallsStub($args);
    }

    /**
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        if ($name !== null) {
            $this->setName($name);
        }

        $this->data     = $data;
        $this->dataName = $dataName;
    }

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()/* The :void return type declaration that should be here would cause a BC issue */
    {
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()/* The :void return type declaration that should be here would cause a BC issue */
    {
    }

    /**
     * This method is called before each test.
     */
    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown()/* The :void return type declaration that should be here would cause a BC issue */
    {
    }

    /**
     * Returns a string representation of the test case.
     *
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function toString(): string
    {
        $class = new ReflectionClass($this);

        $buffer = \sprintf(
            '%s::%s',
            $class->name,
            $this->getName(false)
        );

        return $buffer . $this->getDataSetAsString();
    }

    public function count(): int
    {
        return 1;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    public function getAnnotations(): array
    {
        return \PHPUnit\Util\Test::parseTestMethodAnnotations(
            \get_class($this),
            $this->name
        );
    }

    /**
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function getName(bool $withDataSet = true): ?string
    {
        if ($withDataSet) {
            return $this->name . $this->getDataSetAsString(false);
        }

        return $this->name;
    }

    /**
     * Returns the size of the test.
     *
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function getSize(): int
    {
        return \PHPUnit\Util\Test::getSize(
            \get_class($this),
            $this->getName(false)
        );
    }

    /**
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function hasSize(): bool
    {
        return $this->getSize() !== \PHPUnit\Util\Test::UNKNOWN;
    }

    /**
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function isSmall(): bool
    {
        return $this->getSize() === \PHPUnit\Util\Test::SMALL;
    }

    /**
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function isMedium(): bool
    {
        return $this->getSize() === \PHPUnit\Util\Test::MEDIUM;
    }

    /**
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function isLarge(): bool
    {
        return $this->getSize() === \PHPUnit\Util\Test::LARGE;
    }

    public function getActualOutput(): string
    {
        if (!$this->outputBufferingActive) {
            return $this->output;
        }

        return \ob_get_contents();
    }

    public function hasOutput(): bool
    {
        if ($this->output === '') {
            return false;
        }

        if ($this->hasExpectationOnOutput()) {
            return false;
        }

        return true;
    }

    public function doesNotPerformAssertions(): bool
    {
        return $this->doesNotPerformAssertions;
    }

    public function expectOutputRegex(string $expectedRegex): void
    {
        $this->outputExpectedRegex = $expectedRegex;
    }

    public function expectOutputString(string $expectedString): void
    {
        $this->outputExpectedString = $expectedString;
    }

    public function hasExpectationOnOutput(): bool
    {
        return \is_string($this->outputExpectedString) || \is_string($this->outputExpectedRegex);
    }

    public function getExpectedException(): ?string
    {
        return $this->expectedException;
    }

    /**
     * @return null|int|string
     */
    public function getExpectedExceptionCode()
    {
        return $this->expectedExceptionCode;
    }

    public function getExpectedExceptionMessage(): string
    {
        return $this->expectedExceptionMessage;
    }

    public function getExpectedExceptionMessageRegExp(): string
    {
        return $this->expectedExceptionMessageRegExp;
    }

    public function expectException(string $exception): void
    {
        $this->expectedException = $exception;
    }

    /**
     * @param int|string $code
     */
    public function expectExceptionCode($code): void
    {
        $this->expectedExceptionCode = $code;
    }

    public function expectExceptionMessage(string $message): void
    {
        $this->expectedExceptionMessage = $message;
    }

    public function expectExceptionMessageRegExp(string $messageRegExp): void
    {
        $this->expectedExceptionMessageRegExp = $messageRegExp;
    }

    /**
     * Sets up an expectation for an exception to be raised by the code under test.
     * Information for expected exception class, expected exception message, and
     * expected exception code are retrieved from a given Exception object.
     */
    public function expectExceptionObject(\Exception $exception): void
    {
        $this->expectException(\get_class($exception));
        $this->expectExceptionMessage($exception->getMessage());
        $this->expectExceptionCode($exception->getCode());
    }

    public function expectNotToPerformAssertions()
    {
        $this->doesNotPerformAssertions = true;
    }

    public function setRegisterMockObjectsFromTestArgumentsRecursively(bool $flag): void
    {
        $this->registerMockObjectsFromTestArgumentsRecursively = $flag;
    }

    public function setUseErrorHandler(bool $useErrorHandler): void
    {
        $this->useErrorHandler = $useErrorHandler;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function markAsRisky(): void
    {
        $this->status = BaseTestRunner::STATUS_RISKY;
    }

    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    public function hasFailed(): bool
    {
        $status = $this->getStatus();

        return $status === BaseTestRunner::STATUS_FAILURE || $status === BaseTestRunner::STATUS_ERROR;
    }

    /**
     * Runs the test case and collects the results in a TestResult object.
     * If no TestResult object is passed a new one will be created.
     *
     * @throws CodeCoverageException
     * @throws ReflectionException
     * @throws SebastianBergmann\CodeCoverage\CoveredCodeNotExecutedException
     * @throws SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws SebastianBergmann\CodeCoverage\MissingCoversAnnotationException
     * @throws SebastianBergmann\CodeCoverage\RuntimeException
     * @throws SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function run(TestResult $result = null): TestResult
    {
        if ($result === null) {
            $result = $this->createResult();
        }

        if (!$this instanceof WarningTestCase) {
            $this->setTestResultObject($result);
            $this->setUseErrorHandlerFromAnnotation();
        }

        if ($this->useErrorHandler !== null) {
            $oldErrorHandlerSetting = $result->getConvertErrorsToExceptions();
            $result->convertErrorsToExceptions($this->useErrorHandler);
        }

        if (!$this instanceof WarningTestCase &&
            !$this instanceof SkippedTestCase &&
            !$this->handleDependencies()) {
            return $result;
        }

        $runEntireClass =  $this->runClassInSeparateProcess && !$this->runTestInSeparateProcess;

        if (($this->runTestInSeparateProcess === true || $this->runClassInSeparateProcess === true) &&
            $this->inIsolation !== true &&
            !$this instanceof PhptTestCase) {
            $class = new ReflectionClass($this);

            if ($runEntireClass) {
                $template = new Text_Template(
                    __DIR__ . '/../Util/PHP/Template/TestCaseClass.tpl'
                );
            } else {
                $template = new Text_Template(
                    __DIR__ . '/../Util/PHP/Template/TestCaseMethod.tpl'
                );
            }

            if ($this->preserveGlobalState) {
                $constants     = GlobalState::getConstantsAsString();
                $globals       = GlobalState::getGlobalsAsString();
                $includedFiles = GlobalState::getIncludedFilesAsString();
                $iniSettings   = GlobalState::getIniSettingsAsString();
            } else {
                $constants = '';

                if (!empty($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
                    $globals = '$GLOBALS[\'__PHPUNIT_BOOTSTRAP\'] = ' . \var_export($GLOBALS['__PHPUNIT_BOOTSTRAP'], true) . ";\n";
                } else {
                    $globals = '';
                }
                $includedFiles = '';
                $iniSettings   = '';
            }

            $coverage                                   = $result->getCollectCodeCoverageInformation() ? 'true' : 'false';
            $isStrictAboutTestsThatDoNotTestAnything    = $result->isStrictAboutTestsThatDoNotTestAnything() ? 'true' : 'false';
            $isStrictAboutOutputDuringTests             = $result->isStrictAboutOutputDuringTests() ? 'true' : 'false';
            $enforcesTimeLimit                          = $result->enforcesTimeLimit() ? 'true' : 'false';
            $isStrictAboutTodoAnnotatedTests            = $result->isStrictAboutTodoAnnotatedTests() ? 'true' : 'false';
            $isStrictAboutResourceUsageDuringSmallTests = $result->isStrictAboutResourceUsageDuringSmallTests() ? 'true' : 'false';

            if (\defined('PHPUNIT_COMPOSER_INSTALL')) {
                $composerAutoload = \var_export(PHPUNIT_COMPOSER_INSTALL, true);
            } else {
                $composerAutoload = '\'\'';
            }

            if (\defined('__PHPUNIT_PHAR__')) {
                $phar = \var_export(__PHPUNIT_PHAR__, true);
            } else {
                $phar = '\'\'';
            }

            if ($result->getCodeCoverage()) {
                $codeCoverageFilter = $result->getCodeCoverage()->filter();
            } else {
                $codeCoverageFilter = null;
            }

            $data               = \var_export(\serialize($this->data), true);
            $dataName           = \var_export($this->dataName, true);
            $dependencyInput    = \var_export(\serialize($this->dependencyInput), true);
            $includePath        = \var_export(\get_include_path(), true);
            $codeCoverageFilter = \var_export(\serialize($codeCoverageFilter), true);
            // must do these fixes because TestCaseMethod.tpl has unserialize('{data}') in it, and we can't break BC
            // the lines above used to use addcslashes() rather than var_export(), which breaks null byte escape sequences
            $data               = "'." . $data . ".'";
            $dataName           = "'.(" . $dataName . ").'";
            $dependencyInput    = "'." . $dependencyInput . ".'";
            $includePath        = "'." . $includePath . ".'";
            $codeCoverageFilter = "'." . $codeCoverageFilter . ".'";

            $configurationFilePath = $GLOBALS['__PHPUNIT_CONFIGURATION_FILE'] ?? '';

            $var = [
                'composerAutoload'                           => $composerAutoload,
                'phar'                                       => $phar,
                'filename'                                   => $class->getFileName(),
                'className'                                  => $class->getName(),
                'collectCodeCoverageInformation'             => $coverage,
                'data'                                       => $data,
                'dataName'                                   => $dataName,
                'dependencyInput'                            => $dependencyInput,
                'constants'                                  => $constants,
                'globals'                                    => $globals,
                'include_path'                               => $includePath,
                'included_files'                             => $includedFiles,
                'iniSettings'                                => $iniSettings,
                'isStrictAboutTestsThatDoNotTestAnything'    => $isStrictAboutTestsThatDoNotTestAnything,
                'isStrictAboutOutputDuringTests'             => $isStrictAboutOutputDuringTests,
                'enforcesTimeLimit'                          => $enforcesTimeLimit,
                'isStrictAboutTodoAnnotatedTests'            => $isStrictAboutTodoAnnotatedTests,
                'isStrictAboutResourceUsageDuringSmallTests' => $isStrictAboutResourceUsageDuringSmallTests,
                'codeCoverageFilter'                         => $codeCoverageFilter,
                'configurationFilePath'                      => $configurationFilePath,
                'name'                                       => $this->getName(false),
            ];

            if (!$runEntireClass) {
                $var['methodName'] = $this->name;
            }

            $template->setVar(
                $var
            );

            $php = AbstractPhpProcess::factory();
            $php->runTestJob($template->render(), $this, $result);
        } else {
            $result->run($this);
        }

        if (isset($oldErrorHandlerSetting)) {
            $result->convertErrorsToExceptions($oldErrorHandlerSetting);
        }

        $this->result = null;

        return $result;
    }

    public function runBare(): void
    {
        $this->numAssertions = 0;

        $this->snapshotGlobalState();
        $this->startOutputBuffering();
        \clearstatcache();
        $currentWorkingDirectory = \getcwd();

        $hookMethods = \PHPUnit\Util\Test::getHookMethods(\get_class($this));

        $hasMetRequirements = false;

        try {
            $this->checkRequirements();
            $hasMetRequirements = true;

            if ($this->inIsolation) {
                foreach ($hookMethods['beforeClass'] as $method) {
                    $this->$method();
                }
            }

            $this->setExpectedExceptionFromAnnotation();
            $this->setDoesNotPerformAssertionsFromAnnotation();

            foreach ($hookMethods['before'] as $method) {
                $this->$method();
            }

            $this->assertPreConditions();
            $this->testResult = $this->runTest();
            $this->verifyMockObjects();
            $this->assertPostConditions();

            if (!empty($this->warnings)) {
                throw new Warning(
                    \implode(
                        PHP_EOL,
                        \array_unique($this->warnings)
                    )
                );
            }

            $this->status = BaseTestRunner::STATUS_PASSED;
        } catch (IncompleteTest $e) {
            $this->status        = BaseTestRunner::STATUS_INCOMPLETE;
            $this->statusMessage = $e->getMessage();
        } catch (SkippedTest $e) {
            $this->status        = BaseTestRunner::STATUS_SKIPPED;
            $this->statusMessage = $e->getMessage();
        } catch (Warning $e) {
            $this->status        = BaseTestRunner::STATUS_WARNING;
            $this->statusMessage = $e->getMessage();
        } catch (AssertionFailedError $e) {
            $this->status        = BaseTestRunner::STATUS_FAILURE;
            $this->statusMessage = $e->getMessage();
        } catch (PredictionException $e) {
            $this->status        = BaseTestRunner::STATUS_FAILURE;
            $this->statusMessage = $e->getMessage();
        } catch (Throwable $_e) {
            $e                   = $_e;
            $this->status        = BaseTestRunner::STATUS_ERROR;
            $this->statusMessage = $_e->getMessage();
        }

        $this->mockObjects = [];
        $this->prophet     = null;

        // Tear down the fixture. An exception raised in tearDown() will be
        // caught and passed on when no exception was raised before.
        try {
            if ($hasMetRequirements) {
                foreach ($hookMethods['after'] as $method) {
                    $this->$method();
                }

                if ($this->inIsolation) {
                    foreach ($hookMethods['afterClass'] as $method) {
                        $this->$method();
                    }
                }
            }
        } catch (Throwable $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }

        try {
            $this->stopOutputBuffering();
        } catch (RiskyTestError $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }

        if (isset($_e)) {
            $this->status        = BaseTestRunner::STATUS_ERROR;
            $this->statusMessage = $_e->getMessage();
        }

        \clearstatcache();

        if ($currentWorkingDirectory != \getcwd()) {
            \chdir($currentWorkingDirectory);
        }

        $this->restoreGlobalState();
        $this->unregisterCustomComparators();
        $this->cleanupIniSettings();
        $this->cleanupLocaleSettings();

        // Perform assertion on output.
        if (!isset($e)) {
            try {
                if ($this->outputExpectedRegex !== null) {
                    $this->assertRegExp($this->outputExpectedRegex, $this->output);
                } elseif ($this->outputExpectedString !== null) {
                    $this->assertEquals($this->outputExpectedString, $this->output);
                }
            } catch (Throwable $_e) {
                $e = $_e;
            }
        }

        // Workaround for missing "finally".
        if (isset($e)) {
            if ($e instanceof PredictionException) {
                $e = new AssertionFailedError($e->getMessage());
            }

            $this->onNotSuccessfulTest($e);
        }
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string[] $dependencies
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function hasDependencies(): bool
    {
        return \count($this->dependencies) > 0;
    }

    public function setDependencyInput(array $dependencyInput): void
    {
        $this->dependencyInput = $dependencyInput;
    }

    public function setBeStrictAboutChangesToGlobalState(?bool $beStrictAboutChangesToGlobalState): void
    {
        $this->beStrictAboutChangesToGlobalState = $beStrictAboutChangesToGlobalState;
    }

    public function setBackupGlobals(?bool $backupGlobals): void
    {
        if ($this->backupGlobals === null && $backupGlobals !== null) {
            $this->backupGlobals = $backupGlobals;
        }
    }

    public function setBackupStaticAttributes(?bool $backupStaticAttributes): void
    {
        if ($this->backupStaticAttributes === null && $backupStaticAttributes !== null) {
            $this->backupStaticAttributes = $backupStaticAttributes;
        }
    }

    public function setRunTestInSeparateProcess(bool $runTestInSeparateProcess): void
    {
        if ($this->runTestInSeparateProcess === null) {
            $this->runTestInSeparateProcess = $runTestInSeparateProcess;
        }
    }

    public function setRunClassInSeparateProcess(bool $runClassInSeparateProcess): void
    {
        if ($this->runClassInSeparateProcess === null) {
            $this->runClassInSeparateProcess = $runClassInSeparateProcess;
        }
    }

    public function setPreserveGlobalState(bool $preserveGlobalState): void
    {
        $this->preserveGlobalState = $preserveGlobalState;
    }

    public function setInIsolation(bool $inIsolation): void
    {
        $this->inIsolation = $inIsolation;
    }

    public function isInIsolation(): bool
    {
        return $this->inIsolation;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->testResult;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result): void
    {
        $this->testResult = $result;
    }

    public function setOutputCallback(callable $callback): void
    {
        $this->outputCallback = $callback;
    }

    public function getTestResultObject(): ?TestResult
    {
        return $this->result;
    }

    public function setTestResultObject(TestResult $result): void
    {
        $this->result = $result;
    }

    public function registerMockObject(MockObject $mockObject): void
    {
        $this->mockObjects[] = $mockObject;
    }

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @param string|string[] $className
     */
    public function getMockBuilder($className): MockBuilder
    {
        return new MockBuilder($this, $className);
    }

    public function addToAssertionCount(int $count): void
    {
        $this->numAssertions += $count;
    }

    /**
     * Returns the number of assertions performed by this test.
     */
    public function getNumAssertions(): int
    {
        return $this->numAssertions;
    }

    public function usesDataProvider(): bool
    {
        return !empty($this->data);
    }

    public function dataDescription(): string
    {
        return \is_string($this->dataName) ? $this->dataName : '';
    }

    /**
     * @return int|string
     */
    public function dataName()
    {
        return $this->dataName;
    }

    public function registerComparator(Comparator $comparator): void
    {
        ComparatorFactory::getInstance()->register($comparator);

        $this->customComparators[] = $comparator;
    }

    public function getDataSetAsString(bool $includeData = true): string
    {
        $buffer = '';

        if (!empty($this->data)) {
            if (\is_int($this->dataName)) {
                $buffer .= \sprintf(' with data set #%d', $this->dataName);
            } else {
                $buffer .= \sprintf(' with data set "%s"', $this->dataName);
            }

            $exporter = new Exporter;

            if ($includeData) {
                $buffer .= \sprintf(' (%s)', $exporter->shortenedRecursiveExport($this->data));
            }
        }

        return $buffer;
    }

    /**
     * Override to run the test and assert its state.
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws SebastianBergmann\ObjectEnumerator\InvalidArgumentException
     * @throws Throwable
     *
     * @return mixed
     */
    protected function runTest()
    {
        if ($this->name === null) {
            throw new Exception(
                'PHPUnit\Framework\TestCase::$name must not be null.'
            );
        }

        $testArguments = \array_merge($this->data, $this->dependencyInput);

        $this->registerMockObjectsFromTestArguments($testArguments);

        try {
            $testResult = $this->{$this->name}(...\array_values($testArguments));
        } catch (Throwable $t) {
            $exception = $t;
        }

        if (isset($exception)) {
            if ($this->checkExceptionExpectations($exception)) {
                if ($this->expectedException !== null) {
                    $this->assertThat(
                        $exception,
                        new ExceptionConstraint(
                            $this->expectedException
                        )
                    );
                }

                if ($this->expectedExceptionMessage !== null) {
                    $this->assertThat(
                        $exception,
                        new ExceptionMessage(
                            $this->expectedExceptionMessage
                        )
                    );
                }

                if ($this->expectedExceptionMessageRegExp !== null) {
                    $this->assertThat(
                        $exception,
                        new ExceptionMessageRegularExpression(
                            $this->expectedExceptionMessageRegExp
                        )
                    );
                }

                if ($this->expectedExceptionCode !== null) {
                    $this->assertThat(
                        $exception,
                        new ExceptionCode(
                            $this->expectedExceptionCode
                        )
                    );
                }

                return;
            }

            throw $exception;
        }

        if ($this->expectedException !== null) {
            $this->assertThat(
                null,
                new ExceptionConstraint(
                    $this->expectedException
                )
            );
        } elseif ($this->expectedExceptionMessage !== null) {
            $this->numAssertions++;

            throw new AssertionFailedError(
                \sprintf(
                    'Failed asserting that exception with message "%s" is thrown',
                    $this->expectedExceptionMessage
                )
            );
        } elseif ($this->expectedExceptionMessageRegExp !== null) {
            $this->numAssertions++;

            throw new AssertionFailedError(
                \sprintf(
                    'Failed asserting that exception with message matching "%s" is thrown',
                    $this->expectedExceptionMessageRegExp
                )
            );
        } elseif ($this->expectedExceptionCode !== null) {
            $this->numAssertions++;

            throw new AssertionFailedError(
                \sprintf(
                    'Failed asserting that exception with code "%s" is thrown',
                    $this->expectedExceptionCode
                )
            );
        }

        return $testResult;
    }

    /**
     * This method is a wrapper for the ini_set() function that automatically
     * resets the modified php.ini setting to its original value after the
     * test is run.
     *
     * @param mixed $newValue
     *
     * @throws Exception
     */
    protected function iniSet(string $varName, $newValue): void
    {
        $currentValue = \ini_set($varName, $newValue);

        if ($currentValue !== false) {
            $this->iniSettings[$varName] = $currentValue;
        } else {
            throw new Exception(
                \sprintf(
                    'INI setting "%s" could not be set to "%s".',
                    $varName,
                    $newValue
                )
            );
        }
    }

    /**
     * This method is a wrapper for the setlocale() function that automatically
     * resets the locale to its original value after the test is run.
     *
     * @throws Exception
     */
    protected function setLocale(...$args): void
    {
        if (\count($args) < 2) {
            throw new Exception;
        }

        [$category, $locale] = $args;

        if (\defined('LC_MESSAGES')) {
            $categories[] = LC_MESSAGES;
        }

        if (!\in_array($category, self::LOCALE_CATEGORIES, true)) {
            throw new Exception;
        }

        if (!\is_array($locale) && !\is_string($locale)) {
            throw new Exception;
        }

        $this->locale[$category] = \setlocale($category, 0);

        $result = \setlocale(...$args);

        if ($result === false) {
            throw new Exception(
                'The locale functionality is not implemented on your platform, ' .
                'the specified locale does not exist or the category name is ' .
                'invalid.'
            );
        }
    }

    /**
     * Returns a test double for the specified class.
     *
     * @param string|string[] $originalClassName
     *
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    protected function createMock($originalClassName): MockObject
    {
        return $this->getMockBuilder($originalClassName)
                    ->disableOriginalConstructor()
                    ->disableOriginalClone()
                    ->disableArgumentCloning()
                    ->disallowMockingUnknownTypes()
                    ->getMock();
    }

    /**
     * Returns a configured test double for the specified class.
     *
     * @param string|string[] $originalClassName
     * @param array           $configuration
     *
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    protected function createConfiguredMock($originalClassName, array $configuration): MockObject
    {
        $o = $this->createMock($originalClassName);

        foreach ($configuration as $method => $return) {
            $o->method($method)->willReturn($return);
        }

        return $o;
    }

    /**
     * Returns a partial test double for the specified class.
     *
     * @param string|string[] $originalClassName
     * @param string[]        $methods
     *
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    protected function createPartialMock($originalClassName, array $methods): MockObject
    {
        return $this->getMockBuilder($originalClassName)
                    ->disableOriginalConstructor()
                    ->disableOriginalClone()
                    ->disableArgumentCloning()
                    ->disallowMockingUnknownTypes()
                    ->setMethods(empty($methods) ? null : $methods)
                    ->getMock();
    }

    /**
     * Returns a test proxy for the specified class.
     *
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    protected function createTestProxy(string $originalClassName, array $constructorArguments = []): MockObject
    {
        return $this->getMockBuilder($originalClassName)
                    ->setConstructorArgs($constructorArguments)
                    ->enableProxyingToOriginalMethods()
                    ->getMock();
    }

    /**
     * Mocks the specified class and returns the name of the mocked class.
     *
     * @param string $originalClassName
     * @param array  $methods
     * @param array  $arguments
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param bool   $cloneArguments
     *
     * @throws Exception
     * @throws ReflectionException
     * @throws \InvalidArgumentException
     */
    protected function getMockClass($originalClassName, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = false, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false): string
    {
        $mock = $this->getMockObjectGenerator()->getMock(
            $originalClassName,
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments
        );

        return \get_class($mock);
    }

    /**
     * Returns a mock object for the specified abstract class with all abstract
     * methods of the class mocked. Concrete methods are not mocked by default.
     * To mock concrete methods, use the 7th parameter ($mockedMethods).
     *
     * @param string $originalClassName
     * @param array  $arguments
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param array  $mockedMethods
     * @param bool   $cloneArguments
     *
     * @throws Exception
     * @throws ReflectionException
     * @throws \InvalidArgumentException
     */
    protected function getMockForAbstractClass($originalClassName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = false): MockObject
    {
        $mockObject = $this->getMockObjectGenerator()->getMockForAbstractClass(
            $originalClassName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );

        $this->registerMockObject($mockObject);

        return $mockObject;
    }

    /**
     * Returns a mock object based on the given WSDL file.
     *
     * @param string $wsdlFile
     * @param string $originalClassName
     * @param string $mockClassName
     * @param array  $methods
     * @param bool   $callOriginalConstructor
     * @param array  $options                 An array of options passed to SOAPClient::_construct
     *
     * @throws Exception
     * @throws ReflectionException
     * @throws \InvalidArgumentException
     */
    protected function getMockFromWsdl($wsdlFile, $originalClassName = '', $mockClassName = '', array $methods = [], $callOriginalConstructor = true, array $options = []): MockObject
    {
        if ($originalClassName === '') {
            $originalClassName = \pathinfo(\basename(\parse_url($wsdlFile)['path']), PATHINFO_FILENAME);
        }

        if (!\class_exists($originalClassName)) {
            eval(
                $this->getMockObjectGenerator()->generateClassFromWsdl(
                    $wsdlFile,
                    $originalClassName,
                    $methods,
                    $options
                )
            );
        }

        $mockObject = $this->getMockObjectGenerator()->getMock(
            $originalClassName,
            $methods,
            ['', $options],
            $mockClassName,
            $callOriginalConstructor,
            false,
            false
        );

        $this->registerMockObject($mockObject);

        return $mockObject;
    }

    /**
     * Returns a mock object for the specified trait with all abstract methods
     * of the trait mocked. Concrete methods to mock can be specified with the
     * `$mockedMethods` parameter.
     *
     * @param string $traitName
     * @param array  $arguments
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param array  $mockedMethods
     * @param bool   $cloneArguments
     *
     * @throws Exception
     * @throws ReflectionException
     * @throws \InvalidArgumentException
     */
    protected function getMockForTrait($traitName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = false): MockObject
    {
        $mockObject = $this->getMockObjectGenerator()->getMockForTrait(
            $traitName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );

        $this->registerMockObject($mockObject);

        return $mockObject;
    }

    /**
     * Returns an object for the specified trait.
     *
     * @param string $traitName
     * @param array  $arguments
     * @param string $traitClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     *
     * @throws Exception
     * @throws ReflectionException
     * @throws \InvalidArgumentException
     *
     * @return object
     */
    protected function getObjectForTrait($traitName, array $arguments = [], $traitClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true)/*: object*/
    {
        return $this->getMockObjectGenerator()->getObjectForTrait(
            $traitName,
            $arguments,
            $traitClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload
        );
    }

    /**
     * @param null|string $classOrInterface
     *
     * @throws Prophecy\Exception\Doubler\ClassNotFoundException
     * @throws Prophecy\Exception\Doubler\DoubleException
     * @throws Prophecy\Exception\Doubler\InterfaceNotFoundException
     */
    protected function prophesize($classOrInterface = null): ObjectProphecy
    {
        return $this->getProphet()->prophesize($classOrInterface);
    }

    /**
     * Gets the data set of a TestCase.
     */
    protected function getProvidedData(): array
    {
        return $this->data;
    }

    /**
     * Creates a default TestResult object.
     */
    protected function createResult(): TestResult
    {
        return new TestResult;
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called between setUp() and test.
     */
    protected function assertPreConditions()/* The :void return type declaration that should be here would cause a BC issue */
    {
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called between test and tearDown().
     */
    protected function assertPostConditions()/* The :void return type declaration that should be here would cause a BC issue */
    {
    }

    /**
     * This method is called when a test method did not execute successfully.
     *
     * @throws Throwable
     */
    protected function onNotSuccessfulTest(Throwable $t)/* The :void return type declaration that should be here would cause a BC issue */
    {
        throw $t;
    }

    private function setExpectedExceptionFromAnnotation(): void
    {
        try {
            $expectedException = \PHPUnit\Util\Test::getExpectedException(
                \get_class($this),
                $this->name
            );

            if ($expectedException !== false) {
                $this->expectException($expectedException['class']);

                if ($expectedException['code'] !== null) {
                    $this->expectExceptionCode($expectedException['code']);
                }

                if ($expectedException['message'] !== '') {
                    $this->expectExceptionMessage($expectedException['message']);
                } elseif ($expectedException['message_regex'] !== '') {
                    $this->expectExceptionMessageRegExp($expectedException['message_regex']);
                }
            }
        } catch (ReflectionException $e) {
        }
    }

    private function setUseErrorHandlerFromAnnotation(): void
    {
        try {
            $useErrorHandler = \PHPUnit\Util\Test::getErrorHandlerSettings(
                \get_class($this),
                $this->name
            );

            if ($useErrorHandler !== null) {
                $this->setUseErrorHandler($useErrorHandler);
            }
        } catch (ReflectionException $e) {
        }
    }

    private function checkRequirements(): void
    {
        if (!$this->name || !\method_exists($this, $this->name)) {
            return;
        }

        $missingRequirements = \PHPUnit\Util\Test::getMissingRequirements(
            \get_class($this),
            $this->name
        );

        if (!empty($missingRequirements)) {
            $this->markTestSkipped(\implode(PHP_EOL, $missingRequirements));
        }
    }

    private function verifyMockObjects(): void
    {
        foreach ($this->mockObjects as $mockObject) {
            if ($mockObject->__phpunit_hasMatchers()) {
                $this->numAssertions++;
            }

            $mockObject->__phpunit_verify(
                $this->shouldInvocationMockerBeReset($mockObject)
            );
        }

        if ($this->prophet !== null) {
            try {
                $this->prophet->checkPredictions();
            } catch (Throwable $t) {
                /* Intentionally left empty */
            }

            foreach ($this->prophet->getProphecies() as $objectProphecy) {
                foreach ($objectProphecy->getMethodProphecies() as $methodProphecies) {
                    /** @var MethodProphecy[] $methodProphecies */
                    foreach ($methodProphecies as $methodProphecy) {
                        $this->numAssertions += \count($methodProphecy->getCheckedPredictions());
                    }
                }
            }

            if (isset($t)) {
                throw $t;
            }
        }
    }

    private function handleDependencies(): bool
    {
        if (!empty($this->dependencies) && !$this->inIsolation) {
            $className  = \get_class($this);
            $passed     = $this->result->passed();
            $passedKeys = \array_keys($passed);
            $numKeys    = \count($passedKeys);

            for ($i = 0; $i < $numKeys; $i++) {
                $pos = \strpos($passedKeys[$i], ' with data set');

                if ($pos !== false) {
                    $passedKeys[$i] = \substr($passedKeys[$i], 0, $pos);
                }
            }

            $passedKeys = \array_flip(\array_unique($passedKeys));

            foreach ($this->dependencies as $dependency) {
                $deepClone    = false;
                $shallowClone = false;

                if (\strpos($dependency, 'clone ') === 0) {
                    $deepClone  = true;
                    $dependency = \substr($dependency, \strlen('clone '));
                } elseif (\strpos($dependency, '!clone ') === 0) {
                    $deepClone  = false;
                    $dependency = \substr($dependency, \strlen('!clone '));
                }

                if (\strpos($dependency, 'shallowClone ') === 0) {
                    $shallowClone = true;
                    $dependency   = \substr($dependency, \strlen('shallowClone '));
                } elseif (\strpos($dependency, '!shallowClone ') === 0) {
                    $shallowClone = false;
                    $dependency   = \substr($dependency, \strlen('!shallowClone '));
                }

                if (\strpos($dependency, '::') === false) {
                    $dependency = $className . '::' . $dependency;
                }

                if (!isset($passedKeys[$dependency])) {
                    $this->result->startTest($this);
                    $this->result->addError(
                        $this,
                        new SkippedTestError(
                            \sprintf(
                                'This test depends on "%s" to pass.',
                                $dependency
                            )
                        ),
                        0
                    );
                    $this->result->endTest($this, 0);

                    return false;
                }

                if (isset($passed[$dependency])) {
                    if ($passed[$dependency]['size'] != \PHPUnit\Util\Test::UNKNOWN &&
                        $this->getSize() != \PHPUnit\Util\Test::UNKNOWN &&
                        $passed[$dependency]['size'] > $this->getSize()) {
                        $this->result->addError(
                            $this,
                            new SkippedTestError(
                                'This test depends on a test that is larger than itself.'
                            ),
                            0
                        );

                        return false;
                    }

                    if ($deepClone) {
                        $deepCopy = new DeepCopy;
                        $deepCopy->skipUncloneable(false);

                        $this->dependencyInput[$dependency] = $deepCopy->copy($passed[$dependency]['result']);
                    } elseif ($shallowClone) {
                        $this->dependencyInput[$dependency] = clone $passed[$dependency]['result'];
                    } else {
                        $this->dependencyInput[$dependency] = $passed[$dependency]['result'];
                    }
                } else {
                    $this->dependencyInput[$dependency] = null;
                }
            }
        }

        return true;
    }

    /**
     * Get the mock object generator, creating it if it doesn't exist.
     */
    private function getMockObjectGenerator(): MockGenerator
    {
        if ($this->mockObjectGenerator === null) {
            $this->mockObjectGenerator = new MockGenerator;
        }

        return $this->mockObjectGenerator;
    }

    private function startOutputBuffering(): void
    {
        \ob_start();

        $this->outputBufferingActive = true;
        $this->outputBufferingLevel  = \ob_get_level();
    }

    /**
     * @throws RiskyTestError
     */
    private function stopOutputBuffering(): void
    {
        if (\ob_get_level() !== $this->outputBufferingLevel) {
            while (\ob_get_level() >= $this->outputBufferingLevel) {
                \ob_end_clean();
            }

            throw new RiskyTestError(
                'Test code or tested code did not (only) close its own output buffers'
            );
        }

        $this->output = \ob_get_contents();

        if ($this->outputCallback !== false) {
            $this->output = (string) \call_user_func($this->outputCallback, $this->output);
        }

        \ob_end_clean();

        $this->outputBufferingActive = false;
        $this->outputBufferingLevel  = \ob_get_level();
    }

    private function snapshotGlobalState(): void
    {
        if ($this->runTestInSeparateProcess || $this->inIsolation ||
            (!$this->backupGlobals === true && !$this->backupStaticAttributes)) {
            return;
        }

        $this->snapshot = $this->createGlobalStateSnapshot($this->backupGlobals === true);
    }

    /**
     * @throws RiskyTestError
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    private function restoreGlobalState(): void
    {
        if (!$this->snapshot instanceof Snapshot) {
            return;
        }

        if ($this->beStrictAboutChangesToGlobalState) {
            try {
                $this->compareGlobalStateSnapshots(
                    $this->snapshot,
                    $this->createGlobalStateSnapshot($this->backupGlobals === true)
                );
            } catch (RiskyTestError $rte) {
                // Intentionally left empty
            }
        }

        $restorer = new Restorer;

        if ($this->backupGlobals === true) {
            $restorer->restoreGlobalVariables($this->snapshot);
        }

        if ($this->backupStaticAttributes) {
            $restorer->restoreStaticAttributes($this->snapshot);
        }

        $this->snapshot = null;

        if (isset($rte)) {
            throw $rte;
        }
    }

    private function createGlobalStateSnapshot(bool $backupGlobals): Snapshot
    {
        $blacklist = new Blacklist;

        foreach ($this->backupGlobalsBlacklist as $globalVariable) {
            $blacklist->addGlobalVariable($globalVariable);
        }

        if (!\defined('PHPUNIT_TESTSUITE')) {
            $blacklist->addClassNamePrefix('PHPUnit');
            $blacklist->addClassNamePrefix('SebastianBergmann\CodeCoverage');
            $blacklist->addClassNamePrefix('SebastianBergmann\FileIterator');
            $blacklist->addClassNamePrefix('SebastianBergmann\Invoker');
            $blacklist->addClassNamePrefix('SebastianBergmann\Timer');
            $blacklist->addClassNamePrefix('PHP_Token');
            $blacklist->addClassNamePrefix('Symfony');
            $blacklist->addClassNamePrefix('Text_Template');
            $blacklist->addClassNamePrefix('Doctrine\Instantiator');
            $blacklist->addClassNamePrefix('Prophecy');

            foreach ($this->backupStaticAttributesBlacklist as $class => $attributes) {
                foreach ($attributes as $attribute) {
                    $blacklist->addStaticAttribute($class, $attribute);
                }
            }
        }

        return new Snapshot(
            $blacklist,
            $backupGlobals,
            (bool) $this->backupStaticAttributes,
            false,
            false,
            false,
            false,
            false,
            false,
            false
        );
    }

    /**
     * @throws RiskyTestError
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    private function compareGlobalStateSnapshots(Snapshot $before, Snapshot $after): void
    {
        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals === true;

        if ($backupGlobals) {
            $this->compareGlobalStateSnapshotPart(
                $before->globalVariables(),
                $after->globalVariables(),
                "--- Global variables before the test\n+++ Global variables after the test\n"
            );

            $this->compareGlobalStateSnapshotPart(
                $before->superGlobalVariables(),
                $after->superGlobalVariables(),
                "--- Super-global variables before the test\n+++ Super-global variables after the test\n"
            );
        }

        if ($this->backupStaticAttributes) {
            $this->compareGlobalStateSnapshotPart(
                $before->staticAttributes(),
                $after->staticAttributes(),
                "--- Static attributes before the test\n+++ Static attributes after the test\n"
            );
        }
    }

    /**
     * @throws RiskyTestError
     */
    private function compareGlobalStateSnapshotPart(array $before, array $after, string $header): void
    {
        if ($before != $after) {
            $differ   = new Differ($header);
            $exporter = new Exporter;

            $diff = $differ->diff(
                $exporter->export($before),
                $exporter->export($after)
            );

            throw new RiskyTestError(
                $diff
            );
        }
    }

    private function getProphet(): Prophet
    {
        if ($this->prophet === null) {
            $this->prophet = new Prophet;
        }

        return $this->prophet;
    }

    /**
     * @throws SebastianBergmann\ObjectEnumerator\InvalidArgumentException
     */
    private function shouldInvocationMockerBeReset(MockObject $mock): bool
    {
        $enumerator = new Enumerator;

        foreach ($enumerator->enumerate($this->dependencyInput) as $object) {
            if ($mock === $object) {
                return false;
            }
        }

        if (!\is_array($this->testResult) && !\is_object($this->testResult)) {
            return true;
        }

        foreach ($enumerator->enumerate($this->testResult) as $object) {
            if ($mock === $object) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws SebastianBergmann\ObjectEnumerator\InvalidArgumentException
     * @throws SebastianBergmann\ObjectReflector\InvalidArgumentException
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function registerMockObjectsFromTestArguments(array $testArguments, array &$visited = []): void
    {
        if ($this->registerMockObjectsFromTestArgumentsRecursively) {
            $enumerator = new Enumerator;

            foreach ($enumerator->enumerate($testArguments) as $object) {
                if ($object instanceof MockObject) {
                    $this->registerMockObject($object);
                }
            }
        } else {
            foreach ($testArguments as $testArgument) {
                if ($testArgument instanceof MockObject) {
                    if ($this->isCloneable($testArgument)) {
                        $testArgument = clone $testArgument;
                    }

                    $this->registerMockObject($testArgument);
                } elseif (\is_array($testArgument) && !\in_array($testArgument, $visited, true)) {
                    $visited[] = $testArgument;

                    $this->registerMockObjectsFromTestArguments(
                        $testArgument,
                        $visited
                    );
                }
            }
        }
    }

    private function setDoesNotPerformAssertionsFromAnnotation(): void
    {
        $annotations = $this->getAnnotations();

        if (isset($annotations['method']['doesNotPerformAssertions'])) {
            $this->doesNotPerformAssertions = true;
        }
    }

    private function isCloneable(MockObject $testArgument): bool
    {
        $reflector = new ReflectionObject($testArgument);

        if (!$reflector->isCloneable()) {
            return false;
        }

        if ($reflector->hasMethod('__clone') &&
            $reflector->getMethod('__clone')->isPublic()) {
            return true;
        }

        return false;
    }

    private function unregisterCustomComparators(): void
    {
        $factory = ComparatorFactory::getInstance();

        foreach ($this->customComparators as $comparator) {
            $factory->unregister($comparator);
        }

        $this->customComparators = [];
    }

    private function cleanupIniSettings(): void
    {
        foreach ($this->iniSettings as $varName => $oldValue) {
            \ini_set($varName, $oldValue);
        }

        $this->iniSettings = [];
    }

    private function cleanupLocaleSettings(): void
    {
        foreach ($this->locale as $category => $locale) {
            \setlocale($category, $locale);
        }

        $this->locale = [];
    }

    /**
     * @throws ReflectionException
     */
    private function checkExceptionExpectations(Throwable $throwable): bool
    {
        $result = false;

        if ($this->expectedException !== null || $this->expectedExceptionCode !== null || $this->expectedExceptionMessage !== null || $this->expectedExceptionMessageRegExp !== null) {
            $result = true;
        }

        if ($throwable instanceof Exception) {
            $result = false;
        }

        if (\is_string($this->expectedException)) {
            $reflector = new ReflectionClass($this->expectedException);

            if ($this->expectedException === 'PHPUnit\Framework\Exception' ||
                $this->expectedException === '\PHPUnit\Framework\Exception' ||
                $reflector->isSubclassOf(Exception::class)) {
                $result = true;
            }
        }

        return $result;
    }
}
