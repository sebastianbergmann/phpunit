<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SebastianBergmann\GlobalState\Snapshot;
use SebastianBergmann\GlobalState\Restorer;
use SebastianBergmann\GlobalState\Blacklist;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\ObjectEnumerator\Enumerator;
use Prophecy\Exception\Prediction\PredictionException;
use Prophecy\Prophet;
use DeepCopy\DeepCopy;

/**
 * A TestCase defines the fixture to run multiple tests.
 *
 * To define a TestCase
 *
 *   1) Implement a subclass of PHPUnit_Framework_TestCase.
 *   2) Define instance variables that store the state of the fixture.
 *   3) Initialize the fixture state by overriding setUp().
 *   4) Clean-up after a test by overriding tearDown().
 *
 * Each test runs in its own fixture so there can be no side effects
 * among test runs.
 *
 * Here is an example:
 *
 * <code>
 * <?php
 * class MathTest extends PHPUnit_Framework_TestCase
 * {
 *     public $value1;
 *     public $value2;
 *
 *     protected function setUp()
 *     {
 *         $this->value1 = 2;
 *         $this->value2 = 3;
 *     }
 * }
 * ?>
 * </code>
 *
 * For each test implement a method which interacts with the fixture.
 * Verify the expected results with assertions specified by calling
 * assert with a boolean.
 *
 * <code>
 * <?php
 * public function testPass()
 * {
 *     $this->assertTrue($this->value1 + $this->value2 == 5);
 * }
 * ?>
 * </code>
 *
 * @since Class available since Release 2.0.0
 */
abstract class PHPUnit_Framework_TestCase extends PHPUnit_Framework_Assert implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing
{
    /**
     * Enable or disable the backup and restoration of the $GLOBALS array.
     * Overwrite this attribute in a child class of TestCase.
     * Setting this attribute in setUp() has no effect!
     *
     * @var bool
     */
    protected $backupGlobals = null;

    /**
     * @var array
     */
    protected $backupGlobalsBlacklist = [];

    /**
     * Enable or disable the backup and restoration of static attributes.
     * Overwrite this attribute in a child class of TestCase.
     * Setting this attribute in setUp() has no effect!
     *
     * @var bool
     */
    protected $backupStaticAttributes = null;

    /**
     * @var array
     */
    protected $backupStaticAttributesBlacklist = [];

    /**
     * Whether or not this test is to be run in a separate PHP process.
     *
     * @var bool
     */
    protected $runTestInSeparateProcess = null;

    /**
     * Whether or not this test should preserve the global state when
     * running in a separate PHP process.
     *
     * @var bool
     */
    protected $preserveGlobalState = true;

    /**
     * Whether or not this test is running in a separate PHP process.
     *
     * @var bool
     */
    private $inIsolation = false;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $dataName = '';

    /**
     * @var bool
     */
    private $useErrorHandler = null;

    /**
     * The name of the expected Exception.
     *
     * @var mixed
     */
    private $expectedException = null;

    /**
     * The message of the expected Exception.
     *
     * @var string
     */
    private $expectedExceptionMessage = '';

    /**
     * The regex pattern to validate the expected Exception message.
     *
     * @var string
     */
    private $expectedExceptionMessageRegExp = '';

    /**
     * The code of the expected Exception.
     *
     * @var int|string
     */
    private $expectedExceptionCode;

    /**
     * The name of the test case.
     *
     * @var string
     */
    private $name = null;

    /**
     * @var array
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
     * @var array
     */
    private $mockObjectGenerator = null;

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
     * @var PHPUnit_Framework_TestResult
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
    private $outputExpectedRegex = null;

    /**
     * @var string
     */
    private $outputExpectedString = null;

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
     * @var SebastianBergmann\GlobalState\Snapshot
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
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        if ($name !== null) {
            $this->setName($name);
        }

        $this->data                = $data;
        $this->dataName            = $dataName;
    }

    /**
     * Returns a string representation of the test case.
     *
     * @return string
     */
    public function toString()
    {
        $class = new ReflectionClass($this);

        $buffer = sprintf(
            '%s::%s',
            $class->name,
            $this->getName(false)
        );

        return $buffer . $this->getDataSetAsString();
    }

    /**
     * Counts the number of test cases executed by run(TestResult result).
     *
     * @return int
     */
    public function count()
    {
        return 1;
    }

    /**
     * Returns the annotations for this test.
     *
     * @return array
     *
     * @since Method available since Release 3.4.0
     */
    public function getAnnotations()
    {
        return PHPUnit_Util_Test::parseTestMethodAnnotations(
            get_class($this),
            $this->name
        );
    }

    /**
     * Gets the name of a TestCase.
     *
     * @param bool $withDataSet
     *
     * @return string
     */
    public function getName($withDataSet = true)
    {
        if ($withDataSet) {
            return $this->name . $this->getDataSetAsString(false);
        } else {
            return $this->name;
        }
    }

    /**
     * Returns the size of the test.
     *
     * @return int
     *
     * @since  Method available since Release 3.6.0
     */
    public function getSize()
    {
        return PHPUnit_Util_Test::getSize(
            get_class($this),
            $this->getName(false)
        );
    }

    /**
     * @return string
     *
     * @since  Method available since Release 3.6.0
     */
    public function getActualOutput()
    {
        if (!$this->outputBufferingActive) {
            return $this->output;
        } else {
            return ob_get_contents();
        }
    }

    /**
     * @return bool
     *
     * @since  Method available since Release 3.6.0
     */
    public function hasOutput()
    {
        if (strlen($this->output) === 0) {
            return false;
        }

        if ($this->hasExpectationOnOutput()) {
            return false;
        }

        return true;
    }

    /**
     * @param string $expectedRegex
     *
     * @since Method available since Release 3.6.0
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function expectOutputRegex($expectedRegex)
    {
        if ($this->outputExpectedString !== null) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedRegex) || is_null($expectedRegex)) {
            $this->outputExpectedRegex = $expectedRegex;
        }
    }

    /**
     * @param string $expectedString
     *
     * @since Method available since Release 3.6.0
     */
    public function expectOutputString($expectedString)
    {
        if ($this->outputExpectedRegex !== null) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedString) || is_null($expectedString)) {
            $this->outputExpectedString = $expectedString;
        }
    }

    /**
     * @return bool
     *
     * @since Method available since Release 3.6.5
     * @deprecated
     */
    public function hasPerformedExpectationsOnOutput()
    {
        return $this->hasExpectationOnOutput();
    }

    /**
     * @return bool
     *
     * @since Method available since Release 4.3.3
     */
    public function hasExpectationOnOutput()
    {
        return is_string($this->outputExpectedString) || is_string($this->outputExpectedRegex);
    }

    /**
     * @return string
     *
     * @since  Method available since Release 3.2.0
     */
    public function getExpectedException()
    {
        return $this->expectedException;
    }

    /**
     * @param mixed      $exception
     * @param string     $message
     * @param int|string $code
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since      Method available since Release 3.2.0
     * @deprecated Method deprecated since Release 5.2.0
     */
    public function setExpectedException($exception, $message = '', $code = null)
    {
        $this->expectedException = $exception;

        if ($message !== null && $message !== '') {
            $this->expectExceptionMessage($message);
        }

        if ($code !== null) {
            $this->expectExceptionCode($code);
        }
    }

    /**
     * @param mixed  $exception
     * @param string $messageRegExp
     * @param int    $code
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 4.3.0
     */
    public function setExpectedExceptionRegExp($exception, $messageRegExp = '', $code = null)
    {
        if (!is_string($messageRegExp)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $this->expectedException              = $exception;
        $this->expectedExceptionMessageRegExp = $messageRegExp;

        if ($code !== null) {
            $this->expectExceptionCode($code);
        }
    }

    /**
     * @param mixed $exception
     *
     * @since  Method available since Release 5.2.0
     */
    public function expectException($exception)
    {
        $this->expectedException = $exception;
    }

    /**
     * @param int|string $code
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 5.2.0
     */
    public function expectExceptionCode($code)
    {
        if (!is_int($code) && !is_string($code)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer or string');
        }

        $this->expectedExceptionCode = $code;
    }

    /**
     * @param string $message
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 5.2.0
     */
    public function expectExceptionMessage($message)
    {
        if (!is_string($message)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $this->expectedExceptionMessage = $message;
    }

    /**
     * @param string $messageRegExp
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 5.2.0
     */
    public function expectExceptionMessageRegExp($messageRegExp)
    {
        if (!is_string($messageRegExp)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $this->expectedExceptionMessageRegExp = $messageRegExp;
    }

    /**
     * @since Method available since Release 3.4.0
     */
    protected function setExpectedExceptionFromAnnotation()
    {
        try {
            $expectedException = PHPUnit_Util_Test::getExpectedException(
                get_class($this),
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

    /**
     * @param bool $useErrorHandler
     *
     * @since Method available since Release 3.4.0
     */
    public function setUseErrorHandler($useErrorHandler)
    {
        $this->useErrorHandler = $useErrorHandler;
    }

    /**
     * @since Method available since Release 3.4.0
     */
    protected function setUseErrorHandlerFromAnnotation()
    {
        try {
            $useErrorHandler = PHPUnit_Util_Test::getErrorHandlerSettings(
                get_class($this),
                $this->name
            );

            if ($useErrorHandler !== null) {
                $this->setUseErrorHandler($useErrorHandler);
            }
        } catch (ReflectionException $e) {
        }
    }

    /**
     * @since Method available since Release 3.6.0
     */
    protected function checkRequirements()
    {
        if (!$this->name || !method_exists($this, $this->name)) {
            return;
        }

        $missingRequirements = PHPUnit_Util_Test::getMissingRequirements(
            get_class($this),
            $this->name
        );

        if (!empty($missingRequirements)) {
            $this->markTestSkipped(implode(PHP_EOL, $missingRequirements));
        }
    }

    /**
     * Returns the status of this test.
     *
     * @return int
     *
     * @since  Method available since Release 3.1.0
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the status message of this test.
     *
     * @return string
     *
     * @since  Method available since Release 3.3.0
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * Returns whether or not this test has failed.
     *
     * @return bool
     *
     * @since  Method available since Release 3.0.0
     */
    public function hasFailed()
    {
        $status = $this->getStatus();

        return $status == PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE ||
               $status == PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
    }

    /**
     * Runs the test case and collects the results in a TestResult object.
     * If no TestResult object is passed a new one will be created.
     *
     * @param PHPUnit_Framework_TestResult $result
     *
     * @return PHPUnit_Framework_TestResult
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function run(PHPUnit_Framework_TestResult $result = null)
    {
        if ($result === null) {
            $result = $this->createResult();
        }

        if (!$this instanceof PHPUnit_Framework_WarningTestCase) {
            $this->setTestResultObject($result);
            $this->setUseErrorHandlerFromAnnotation();
        }

        if ($this->useErrorHandler !== null) {
            $oldErrorHandlerSetting = $result->getConvertErrorsToExceptions();
            $result->convertErrorsToExceptions($this->useErrorHandler);
        }

        if (!$this instanceof PHPUnit_Framework_WarningTestCase && !$this->handleDependencies()) {
            return;
        }

        if ($this->runTestInSeparateProcess === true &&
            $this->inIsolation !== true &&
            !$this instanceof PHPUnit_Extensions_PhptTestCase) {
            $class = new ReflectionClass($this);

            $template = new Text_Template(
                __DIR__ . '/../Util/PHP/Template/TestCaseMethod.tpl'
            );

            if ($this->preserveGlobalState) {
                $constants     = PHPUnit_Util_GlobalState::getConstantsAsString();
                $globals       = PHPUnit_Util_GlobalState::getGlobalsAsString();
                $includedFiles = PHPUnit_Util_GlobalState::getIncludedFilesAsString();
                $iniSettings   = PHPUnit_Util_GlobalState::getIniSettingsAsString();
            } else {
                $constants     = '';
                if (!empty($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
                    $globals     = '$GLOBALS[\'__PHPUNIT_BOOTSTRAP\'] = ' . var_export($GLOBALS['__PHPUNIT_BOOTSTRAP'], true) . ";\n";
                } else {
                    $globals     = '';
                }
                $includedFiles = '';
                $iniSettings   = '';
            }

            $coverage                                   = $result->getCollectCodeCoverageInformation()          ? 'true' : 'false';
            $isStrictAboutTestsThatDoNotTestAnything    = $result->isStrictAboutTestsThatDoNotTestAnything()    ? 'true' : 'false';
            $isStrictAboutOutputDuringTests             = $result->isStrictAboutOutputDuringTests()             ? 'true' : 'false';
            $enforcesTimeLimit                          = $result->enforcesTimeLimit()                          ? 'true' : 'false';
            $isStrictAboutTodoAnnotatedTests            = $result->isStrictAboutTodoAnnotatedTests()            ? 'true' : 'false';
            $isStrictAboutResourceUsageDuringSmallTests = $result->isStrictAboutResourceUsageDuringSmallTests() ? 'true' : 'false';

            if (defined('PHPUNIT_COMPOSER_INSTALL')) {
                $composerAutoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
            } else {
                $composerAutoload = '\'\'';
            }

            if (defined('__PHPUNIT_PHAR__')) {
                $phar = var_export(__PHPUNIT_PHAR__, true);
            } else {
                $phar = '\'\'';
            }

            if ($result->getCodeCoverage()) {
                $codeCoverageFilter = $result->getCodeCoverage()->filter();
            } else {
                $codeCoverageFilter = null;
            }

            $data               = var_export(serialize($this->data), true);
            $dataName           = var_export($this->dataName, true);
            $dependencyInput    = var_export(serialize($this->dependencyInput), true);
            $includePath        = var_export(get_include_path(), true);
            $codeCoverageFilter = var_export(serialize($codeCoverageFilter), true);
            // must do these fixes because TestCaseMethod.tpl has unserialize('{data}') in it, and we can't break BC
            // the lines above used to use addcslashes() rather than var_export(), which breaks null byte escape sequences
            $data               = "'." . $data . ".'";
            $dataName           = "'.(" . $dataName . ").'";
            $dependencyInput    = "'." . $dependencyInput . ".'";
            $includePath        = "'." . $includePath . ".'";
            $codeCoverageFilter = "'." . $codeCoverageFilter . ".'";

            $configurationFilePath = (isset($GLOBALS['__PHPUNIT_CONFIGURATION_FILE']) ? $GLOBALS['__PHPUNIT_CONFIGURATION_FILE'] : '');

            $template->setVar(
                [
                    'composerAutoload'                           => $composerAutoload,
                    'phar'                                       => $phar,
                    'filename'                                   => $class->getFileName(),
                    'className'                                  => $class->getName(),
                    'methodName'                                 => $this->name,
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
                    'configurationFilePath'                      => $configurationFilePath
                ]
            );

            $this->prepareTemplate($template);

            $php = PHPUnit_Util_PHP::factory();
            $php->runTestJob($template->render(), $this, $result);
        } else {
            $result->run($this);
        }

        if ($this->useErrorHandler !== null) {
            $result->convertErrorsToExceptions($oldErrorHandlerSetting);
        }

        $this->result = null;

        return $result;
    }

    /**
     * Runs the bare test sequence.
     */
    public function runBare()
    {
        $this->numAssertions = 0;

        $this->snapshotGlobalState();
        $this->startOutputBuffering();
        clearstatcache();
        $currentWorkingDirectory = getcwd();

        $hookMethods = PHPUnit_Util_Test::getHookMethods(get_class($this));

        try {
            $hasMetRequirements = false;
            $this->checkRequirements();
            $hasMetRequirements = true;

            if ($this->inIsolation) {
                foreach ($hookMethods['beforeClass'] as $method) {
                    $this->$method();
                }
            }

            $this->setExpectedExceptionFromAnnotation();

            foreach ($hookMethods['before'] as $method) {
                $this->$method();
            }

            $this->assertPreConditions();
            $this->testResult = $this->runTest();
            $this->verifyMockObjects();
            $this->assertPostConditions();

            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
        } catch (PHPUnit_Framework_IncompleteTest $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            $this->statusMessage = $e->getMessage();
        } catch (PHPUnit_Framework_SkippedTest $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            $this->statusMessage = $e->getMessage();
        } catch (PHPUnit_Framework_Warning $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_WARNING;
            $this->statusMessage = $e->getMessage();
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->statusMessage = $e->getMessage();
        } catch (PredictionException $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->statusMessage = $e->getMessage();
        } catch (Throwable $_e) {
            $e = $_e;
        } catch (Exception $_e) {
            $e = $_e;
        }

        if (isset($_e)) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
            $this->statusMessage = $_e->getMessage();
        }

        // Clean up the mock objects.
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
        } catch (Exception $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }

        try {
            $this->stopOutputBuffering();
        } catch (PHPUnit_Framework_RiskyTestError $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }

        clearstatcache();

        if ($currentWorkingDirectory != getcwd()) {
            chdir($currentWorkingDirectory);
        }

        $this->restoreGlobalState();

        // Clean up INI settings.
        foreach ($this->iniSettings as $varName => $oldValue) {
            ini_set($varName, $oldValue);
        }

        $this->iniSettings = [];

        // Clean up locale settings.
        foreach ($this->locale as $category => $locale) {
            setlocale($category, $locale);
        }

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
            } catch (Exception $_e) {
                $e = $_e;
            }
        }

        // Workaround for missing "finally".
        if (isset($e)) {
            if ($e instanceof PredictionException) {
                $e = new PHPUnit_Framework_AssertionFailedError($e->getMessage());
            }

            $this->onNotSuccessfulTest($e);
        }
    }

    /**
     * Override to run the test and assert its state.
     *
     * @return mixed
     *
     * @throws Exception|PHPUnit_Framework_Exception
     * @throws PHPUnit_Framework_Exception
     */
    protected function runTest()
    {
        if ($this->name === null) {
            throw new PHPUnit_Framework_Exception(
                'PHPUnit_Framework_TestCase::$name must not be null.'
            );
        }

        try {
            $class  = new ReflectionClass($this);
            $method = $class->getMethod($this->name);
        } catch (ReflectionException $e) {
            $this->fail($e->getMessage());
        }

        $testArguments = array_merge($this->data, $this->dependencyInput);

        $this->registerMockObjectsFromTestArguments($testArguments);

        try {
            $testResult = $method->invokeArgs($this, $testArguments);
        } catch (Throwable $_e) {
            $e = $_e;
        } catch (Exception $_e) {
            $e = $_e;
        }

        if (isset($e)) {
            $checkException = false;

            if (is_string($this->expectedException)) {
                $checkException = true;

                if ($e instanceof PHPUnit_Framework_Exception) {
                    $checkException = false;
                }

                $reflector = new ReflectionClass($this->expectedException);

                if ($this->expectedException == 'PHPUnit_Framework_Exception' ||
                    $reflector->isSubclassOf('PHPUnit_Framework_Exception')) {
                    $checkException = true;
                }
            }

            if ($checkException) {
                $this->assertThat(
                    $e,
                    new PHPUnit_Framework_Constraint_Exception(
                        $this->expectedException
                    )
                );

                if (is_string($this->expectedExceptionMessage) &&
                    !empty($this->expectedExceptionMessage)) {
                    $this->assertThat(
                        $e,
                        new PHPUnit_Framework_Constraint_ExceptionMessage(
                            $this->expectedExceptionMessage
                        )
                    );
                }

                if (is_string($this->expectedExceptionMessageRegExp) &&
                    !empty($this->expectedExceptionMessageRegExp)) {
                    $this->assertThat(
                        $e,
                        new PHPUnit_Framework_Constraint_ExceptionMessageRegExp(
                            $this->expectedExceptionMessageRegExp
                        )
                    );
                }

                if ($this->expectedExceptionCode !== null) {
                    $this->assertThat(
                        $e,
                        new PHPUnit_Framework_Constraint_ExceptionCode(
                            $this->expectedExceptionCode
                        )
                    );
                }

                return;
            } else {
                throw $e;
            }
        }

        if ($this->expectedException !== null) {
            $this->assertThat(
                null,
                new PHPUnit_Framework_Constraint_Exception(
                    $this->expectedException
                )
            );
        }

        return $testResult;
    }

    /**
     * Verifies the mock object expectations.
     *
     * @since Method available since Release 3.5.0
     */
    protected function verifyMockObjects()
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
            } catch (Exception $t) {
                /* Intentionally left empty */
            }

            foreach ($this->prophet->getProphecies() as $objectProphecy) {
                foreach ($objectProphecy->getMethodProphecies() as $methodProphecies) {
                    foreach ($methodProphecies as $methodProphecy) {
                        $this->numAssertions += count($methodProphecy->getCheckedPredictions());
                    }
                }
            }

            if (isset($t)) {
                throw $t;
            }
        }
    }

    /**
     * Sets the name of a TestCase.
     *
     * @param  string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the dependencies of a TestCase.
     *
     * @param array $dependencies
     *
     * @since  Method available since Release 3.4.0
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Returns true if the tests has dependencies
     *
     * @return bool
     *
     * @since Method available since Release 4.0.0
     */
    public function hasDependencies()
    {
        return count($this->dependencies) > 0;
    }

    /**
     * Sets
     *
     * @param array $dependencyInput
     *
     * @since  Method available since Release 3.4.0
     */
    public function setDependencyInput(array $dependencyInput)
    {
        $this->dependencyInput = $dependencyInput;
    }

    /**
     * @param bool $beStrictAboutChangesToGlobalState
     *
     * @since Method available since Release 4.6.0
     */
    public function setbeStrictAboutChangesToGlobalState($beStrictAboutChangesToGlobalState)
    {
        $this->beStrictAboutChangesToGlobalState = $beStrictAboutChangesToGlobalState;
    }

    /**
     * Calling this method in setUp() has no effect!
     *
     * @param bool $backupGlobals
     *
     * @since  Method available since Release 3.3.0
     */
    public function setBackupGlobals($backupGlobals)
    {
        if (is_null($this->backupGlobals) && is_bool($backupGlobals)) {
            $this->backupGlobals = $backupGlobals;
        }
    }

    /**
     * Calling this method in setUp() has no effect!
     *
     * @param bool $backupStaticAttributes
     *
     * @since  Method available since Release 3.4.0
     */
    public function setBackupStaticAttributes($backupStaticAttributes)
    {
        if (is_null($this->backupStaticAttributes) &&
            is_bool($backupStaticAttributes)) {
            $this->backupStaticAttributes = $backupStaticAttributes;
        }
    }

    /**
     * @param bool $runTestInSeparateProcess
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 3.4.0
     */
    public function setRunTestInSeparateProcess($runTestInSeparateProcess)
    {
        if (is_bool($runTestInSeparateProcess)) {
            if ($this->runTestInSeparateProcess === null) {
                $this->runTestInSeparateProcess = $runTestInSeparateProcess;
            }
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @param bool $preserveGlobalState
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 3.4.0
     */
    public function setPreserveGlobalState($preserveGlobalState)
    {
        if (is_bool($preserveGlobalState)) {
            $this->preserveGlobalState = $preserveGlobalState;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @param bool $inIsolation
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 3.4.0
     */
    public function setInIsolation($inIsolation)
    {
        if (is_bool($inIsolation)) {
            $this->inIsolation = $inIsolation;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @return bool
     *
     * @since  Method available since Release 4.3.0
     */
    public function isInIsolation()
    {
        return $this->inIsolation;
    }

    /**
     * @return mixed
     *
     * @since  Method available since Release 3.4.0
     */
    public function getResult()
    {
        return $this->testResult;
    }

    /**
     * @param mixed $result
     *
     * @since  Method available since Release 3.4.0
     */
    public function setResult($result)
    {
        $this->testResult = $result;
    }

    /**
     * @param callable $callback
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since Method available since Release 3.6.0
     */
    public function setOutputCallback($callback)
    {
        if (!is_callable($callback)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'callback');
        }

        $this->outputCallback = $callback;
    }

    /**
     * @return PHPUnit_Framework_TestResult
     *
     * @since  Method available since Release 3.5.7
     */
    public function getTestResultObject()
    {
        return $this->result;
    }

    /**
     * @param PHPUnit_Framework_TestResult $result
     *
     * @since Method available since Release 3.6.0
     */
    public function setTestResultObject(PHPUnit_Framework_TestResult $result)
    {
        $this->result = $result;
    }

    /**
     * This method is a wrapper for the ini_set() function that automatically
     * resets the modified php.ini setting to its original value after the
     * test is run.
     *
     * @param string $varName
     * @param string $newValue
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 3.0.0
     */
    protected function iniSet($varName, $newValue)
    {
        if (!is_string($varName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $currentValue = ini_set($varName, $newValue);

        if ($currentValue !== false) {
            $this->iniSettings[$varName] = $currentValue;
        } else {
            throw new PHPUnit_Framework_Exception(
                sprintf(
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
     * @param int    $category
     * @param string $locale
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 3.1.0
     */
    protected function setLocale()
    {
        $args = func_get_args();

        if (count($args) < 2) {
            throw new PHPUnit_Framework_Exception;
        }

        $category = $args[0];
        $locale   = $args[1];

        $categories = [
            LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_NUMERIC, LC_TIME
        ];

        if (defined('LC_MESSAGES')) {
            $categories[] = LC_MESSAGES;
        }

        if (!in_array($category, $categories)) {
            throw new PHPUnit_Framework_Exception;
        }

        if (!is_array($locale) && !is_string($locale)) {
            throw new PHPUnit_Framework_Exception;
        }

        $this->locale[$category] = setlocale($category, null);

        $result = call_user_func_array('setlocale', $args);

        if ($result === false) {
            throw new PHPUnit_Framework_Exception(
                'The locale functionality is not implemented on your platform, ' .
                'the specified locale does not exist or the category name is ' .
                'invalid.'
            );
        }
    }

    /**
     * Returns a mock object for the specified class.
     *
     * @param string     $originalClassName       Name of the class to mock.
     * @param array|null $methods                 When provided, only methods whose names are in the array
     *                                            are replaced with a configurable test double. The behavior
     *                                            of the other methods is not changed.
     *                                            Providing null means that no methods will be replaced.
     * @param array      $arguments               Parameters to pass to the original class' constructor.
     * @param string     $mockClassName           Class name for the generated test double class.
     * @param bool       $callOriginalConstructor Can be used to disable the call to the original class' constructor.
     * @param bool       $callOriginalClone       Can be used to disable the call to the original class' clone constructor.
     * @param bool       $callAutoload            Can be used to disable __autoload() during the generation of the test double class.
     * @param bool       $cloneArguments
     * @param bool       $callOriginalMethods
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 3.0.0
     */
    public function getMock($originalClassName, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false)
    {
        $mockObject = $this->getMockObjectGenerator()->getMock(
            $originalClassName,
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments,
            $callOriginalMethods
        );

        $this->mockObjects[] = $mockObject;

        return $mockObject;
    }

    /**
     * Returns a mock with disabled constructor object for the specified class.
     *
     * @param string $originalClassName
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 5.0.0
     */
    public function getMockWithoutInvokingTheOriginalConstructor($originalClassName)
    {
        return $this->getMockBuilder($originalClassName)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @param string $className
     *
     * @return PHPUnit_Framework_MockObject_MockBuilder
     *
     * @since  Method available since Release 3.5.0
     */
    public function getMockBuilder($className)
    {
        return new PHPUnit_Framework_MockObject_MockBuilder($this, $className);
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
     * @return string
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 3.5.0
     */
    protected function getMockClass($originalClassName, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = false, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false)
    {
        $mock = $this->getMock(
            $originalClassName,
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments
        );

        return get_class($mock);
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
     * @return PHPUnit_Framework_MockObject_MockObject
     *
     * @since  Method available since Release 3.4.0
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function getMockForAbstractClass($originalClassName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = false)
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

        $this->mockObjects[] = $mockObject;

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
     * @return PHPUnit_Framework_MockObject_MockObject
     *
     * @since  Method available since Release 3.4.0
     */
    protected function getMockFromWsdl($wsdlFile, $originalClassName = '', $mockClassName = '', array $methods = [], $callOriginalConstructor = true, array $options = [])
    {
        if ($originalClassName === '') {
            $originalClassName = str_replace('.wsdl', '', basename($wsdlFile));
        }

        if (!class_exists($originalClassName)) {
            eval(
            $this->getMockObjectGenerator()->generateClassFromWsdl(
                $wsdlFile,
                $originalClassName,
                $methods,
                $options
            )
            );
        }

        return $this->getMock(
            $originalClassName,
            $methods,
            ['', $options],
            $mockClassName,
            $callOriginalConstructor,
            false,
            false
        );
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
     * @return PHPUnit_Framework_MockObject_MockObject
     *
     * @since  Method available since Release 4.0.0
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function getMockForTrait($traitName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = false)
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

        $this->mockObjects[] = $mockObject;

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
     * @param bool   $cloneArguments
     *
     * @return object
     *
     * @since  Method available since Release 3.6.0
     *
     * @throws PHPUnit_Framework_Exception
     */
    protected function getObjectForTrait($traitName, array $arguments = [], $traitClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false)
    {
        return $this->getMockObjectGenerator()->getObjectForTrait(
            $traitName,
            $arguments,
            $traitClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments
        );
    }

    /**
     * @param string|null $classOrInterface
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     *
     * @throws \LogicException
     *
     * @since  Method available since Release 4.5.0
     */
    protected function prophesize($classOrInterface = null)
    {
        return $this->getProphet()->prophesize($classOrInterface);
    }

    /**
     * Adds a value to the assertion counter.
     *
     * @param int $count
     *
     * @since Method available since Release 3.3.3
     */
    public function addToAssertionCount($count)
    {
        $this->numAssertions += $count;
    }

    /**
     * Returns the number of assertions performed by this test.
     *
     * @return int
     *
     * @since  Method available since Release 3.3.0
     */
    public function getNumAssertions()
    {
        return $this->numAssertions;
    }

    /**
     * Returns a matcher that matches when the method is executed
     * zero or more times.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     *
     * @since  Method available since Release 3.0.0
     */
    public static function any()
    {
        return new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
    }

    /**
     * Returns a matcher that matches when the method is never executed.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
     *
     * @since  Method available since Release 3.0.0
     */
    public static function never()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount(0);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at least N times.
     *
     * @param int $requiredInvocations
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastCount
     *
     * @since  Method available since Release 4.2.0
     */
    public static function atLeast($requiredInvocations)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastCount(
            $requiredInvocations
        );
    }

    /**
     * Returns a matcher that matches when the method is executed at least once.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce
     *
     * @since  Method available since Release 3.0.0
     */
    public static function atLeastOnce()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce;
    }

    /**
     * Returns a matcher that matches when the method is executed exactly once.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
     *
     * @since  Method available since Release 3.0.0
     */
    public static function once()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount(1);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * exactly $count times.
     *
     * @param int $count
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
     *
     * @since  Method available since Release 3.0.0
     */
    public static function exactly($count)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount($count);
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at most N times.
     *
     * @param int $allowedInvocations
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtMostCount
     *
     * @since  Method available since Release 4.2.0
     */
    public static function atMost($allowedInvocations)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtMostCount(
            $allowedInvocations
        );
    }

    /**
     * Returns a matcher that matches when the method is executed
     * at the given index.
     *
     * @param int $index
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex
     *
     * @since  Method available since Release 3.0.0
     */
    public static function at($index)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex($index);
    }

    /**
     * @param mixed $value
     *
     * @return PHPUnit_Framework_MockObject_Stub_Return
     *
     * @since  Method available since Release 3.0.0
     */
    public static function returnValue($value)
    {
        return new PHPUnit_Framework_MockObject_Stub_Return($value);
    }

    /**
     * @param array $valueMap
     *
     * @return PHPUnit_Framework_MockObject_Stub_ReturnValueMap
     *
     * @since  Method available since Release 3.6.0
     */
    public static function returnValueMap(array $valueMap)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnValueMap($valueMap);
    }

    /**
     * @param int $argumentIndex
     *
     * @return PHPUnit_Framework_MockObject_Stub_ReturnArgument
     *
     * @since  Method available since Release 3.3.0
     */
    public static function returnArgument($argumentIndex)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnArgument(
            $argumentIndex
        );
    }

    /**
     * @param mixed $callback
     *
     * @return PHPUnit_Framework_MockObject_Stub_ReturnCallback
     *
     * @since  Method available since Release 3.3.0
     */
    public static function returnCallback($callback)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnCallback($callback);
    }

    /**
     * Returns the current object.
     *
     * This method is useful when mocking a fluent interface.
     *
     * @return PHPUnit_Framework_MockObject_Stub_ReturnSelf
     *
     * @since  Method available since Release 3.6.0
     */
    public static function returnSelf()
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnSelf();
    }

    /**
     * @param Throwable|Exception $exception
     *
     * @return PHPUnit_Framework_MockObject_Stub_Exception
     *
     * @since  Method available since Release 3.1.0
     *
     * @todo   Add type declaration when support for PHP 5 is dropped
     */
    public static function throwException($exception)
    {
        return new PHPUnit_Framework_MockObject_Stub_Exception($exception);
    }

    /**
     * @param mixed $value, ...
     *
     * @return PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls
     *
     * @since  Method available since Release 3.0.0
     */
    public static function onConsecutiveCalls()
    {
        $args = func_get_args();

        return new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($args);
    }

    /**
     * Gets the data set description of a TestCase.
     *
     * @param bool $includeData
     *
     * @return string
     *
     * @since  Method available since Release 3.3.0
     */
    protected function getDataSetAsString($includeData = true)
    {
        $buffer = '';

        if (!empty($this->data)) {
            if (is_int($this->dataName)) {
                $buffer .= sprintf(' with data set #%d', $this->dataName);
            } else {
                $buffer .= sprintf(' with data set "%s"', $this->dataName);
            }

            $exporter = new Exporter;

            if ($includeData) {
                $buffer .= sprintf(' (%s)', $exporter->shortenedRecursiveExport($this->data));
            }
        }

        return $buffer;
    }

    /**
     * Creates a default TestResult object.
     *
     * @return PHPUnit_Framework_TestResult
     */
    protected function createResult()
    {
        return new PHPUnit_Framework_TestResult;
    }

    /**
     * @since Method available since Release 3.5.4
     */
    protected function handleDependencies()
    {
        if (!empty($this->dependencies) && !$this->inIsolation) {
            $className  = get_class($this);
            $passed     = $this->result->passed();
            $passedKeys = array_keys($passed);
            $numKeys    = count($passedKeys);

            for ($i = 0; $i < $numKeys; $i++) {
                $pos = strpos($passedKeys[$i], ' with data set');

                if ($pos !== false) {
                    $passedKeys[$i] = substr($passedKeys[$i], 0, $pos);
                }
            }

            $passedKeys = array_flip(array_unique($passedKeys));

            foreach ($this->dependencies as $dependency) {
                $clone = false;

                if (strpos($dependency, 'clone ') === 0) {
                    $clone      = true;
                    $dependency = substr($dependency, strlen('clone '));
                } elseif (strpos($dependency, '!clone ') === 0) {
                    $clone      = false;
                    $dependency = substr($dependency, strlen('!clone '));
                }

                if (strpos($dependency, '::') === false) {
                    $dependency = $className . '::' . $dependency;
                }

                if (!isset($passedKeys[$dependency])) {
                    $this->result->addError(
                        $this,
                        new PHPUnit_Framework_SkippedTestError(
                            sprintf(
                                'This test depends on "%s" to pass.',
                                $dependency
                            )
                        ),
                        0
                    );

                    return false;
                }

                if (isset($passed[$dependency])) {
                    if ($passed[$dependency]['size'] != PHPUnit_Util_Test::UNKNOWN &&
                        $this->getSize() != PHPUnit_Util_Test::UNKNOWN &&
                        $passed[$dependency]['size'] > $this->getSize()) {
                        $this->result->addError(
                            $this,
                            new PHPUnit_Framework_SkippedTestError(
                                'This test depends on a test that is larger than itself.'
                            ),
                            0
                        );

                        return false;
                    }

                    if ($clone) {
                        $deepCopy   = new DeepCopy;
                        $deepCopy->skipUncloneable(false);

                        $this->dependencyInput[$dependency] = $deepCopy->copy($passed[$dependency]['result']);
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
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called before the execution of a test starts
     * and after setUp() is called.
     *
     * @since  Method available since Release 3.2.8
     */
    protected function assertPreConditions()
    {
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called before the execution of a test ends
     * and before tearDown() is called.
     *
     * @since  Method available since Release 3.2.8
     */
    protected function assertPostConditions()
    {
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function tearDownAfterClass()
    {
    }

    /**
     * This method is called when a test method did not execute successfully.
     *
     * @param Exception|Throwable $e
     *
     * @since Method available since Release 3.4.0
     *
     * @throws Exception|Throwable
     */
    protected function onNotSuccessfulTest($e)
    {
        $expected = PHP_MAJOR_VERSION >= 7 ? 'Throwable' : 'Exception';

        if ($e instanceof $expected) {
            throw $e;
        }

        throw PHPUnit_Util_InvalidArgumentHelper::factory(
            1,
            'Throwable or Exception'
        );
    }

    /**
     * Performs custom preparations on the process isolation template.
     *
     * @param Text_Template $template
     *
     * @since Method available since Release 3.4.0
     */
    protected function prepareTemplate(Text_Template $template)
    {
    }

    /**
     * Get the mock object generator, creating it if it doesn't exist.
     *
     * @return PHPUnit_Framework_MockObject_Generator
     */
    protected function getMockObjectGenerator()
    {
        if (null === $this->mockObjectGenerator) {
            $this->mockObjectGenerator = new PHPUnit_Framework_MockObject_Generator;
        }

        return $this->mockObjectGenerator;
    }

    /**
     * @since Method available since Release 4.2.0
     */
    private function startOutputBuffering()
    {
        while (!defined('PHPUNIT_TESTSUITE') && ob_get_level() > 0) {
            ob_end_clean();
        }

        ob_start();

        $this->outputBufferingActive = true;
        $this->outputBufferingLevel  = ob_get_level();
    }

    /**
     * @since Method available since Release 4.2.0
     */
    private function stopOutputBuffering()
    {
        if (ob_get_level() != $this->outputBufferingLevel) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            throw new PHPUnit_Framework_RiskyTestError(
                'Test code or tested code did not (only) close its own output buffers'
            );
        }

        $output = ob_get_contents();

        if ($this->outputCallback === false) {
            $this->output = $output;
        } else {
            $this->output = call_user_func_array(
                $this->outputCallback,
                [$output]
            );
        }

        ob_end_clean();

        $this->outputBufferingActive = false;
        $this->outputBufferingLevel  = ob_get_level();
    }

    private function snapshotGlobalState()
    {
        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals === true;

        if ($this->runTestInSeparateProcess || $this->inIsolation ||
            (!$backupGlobals && !$this->backupStaticAttributes)) {
            return;
        }

        $this->snapshot = $this->createGlobalStateSnapshot($backupGlobals);
    }

    private function restoreGlobalState()
    {
        if (!$this->snapshot instanceof Snapshot) {
            return;
        }

        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals === true;

        if ($this->beStrictAboutChangesToGlobalState) {
            try {
                $this->compareGlobalStateSnapshots(
                    $this->snapshot,
                    $this->createGlobalStateSnapshot($backupGlobals)
                );
            } catch (PHPUnit_Framework_RiskyTestError $rte) {
                // Intentionally left empty
            }
        }

        $restorer = new Restorer;

        if ($backupGlobals) {
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

    /**
     * @param bool $backupGlobals
     *
     * @return Snapshot
     */
    private function createGlobalStateSnapshot($backupGlobals)
    {
        $blacklist = new Blacklist;

        foreach ($this->backupGlobalsBlacklist as $globalVariable) {
            $blacklist->addGlobalVariable($globalVariable);
        }

        if (!defined('PHPUNIT_TESTSUITE')) {
            $blacklist->addClassNamePrefix('PHPUnit');
            $blacklist->addClassNamePrefix('File_Iterator');
            $blacklist->addClassNamePrefix('PHP_CodeCoverage');
            $blacklist->addClassNamePrefix('PHP_Invoker');
            $blacklist->addClassNamePrefix('PHP_Timer');
            $blacklist->addClassNamePrefix('PHP_Token');
            $blacklist->addClassNamePrefix('Symfony');
            $blacklist->addClassNamePrefix('Text_Template');
            $blacklist->addClassNamePrefix('Doctrine\Instantiator');

            foreach ($this->backupStaticAttributesBlacklist as $class => $attributes) {
                foreach ($attributes as $attribute) {
                    $blacklist->addStaticAttribute($class, $attribute);
                }
            }
        }

        return new Snapshot(
            $blacklist,
            $backupGlobals,
            $this->backupStaticAttributes,
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
     * @param Snapshot $before
     * @param Snapshot $after
     *
     * @throws PHPUnit_Framework_RiskyTestError
     */
    private function compareGlobalStateSnapshots(Snapshot $before, Snapshot $after)
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
     * @param array  $before
     * @param array  $after
     * @param string $header
     *
     * @throws PHPUnit_Framework_RiskyTestError
     */
    private function compareGlobalStateSnapshotPart(array $before, array $after, $header)
    {
        if ($before != $after) {
            $differ   = new Differ($header);
            $exporter = new Exporter;

            $diff = $differ->diff(
                $exporter->export($before),
                $exporter->export($after)
            );

            throw new PHPUnit_Framework_RiskyTestError(
                $diff
            );
        }
    }

    /**
     * @return Prophecy\Prophet
     *
     * @since Method available since Release 4.5.0
     */
    private function getProphet()
    {
        if ($this->prophet === null) {
            $this->prophet = new Prophet;
        }

        return $this->prophet;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $mock
     *
     * @return bool
     *
     * @since  Method available since Release 5.0.0
     */
    private function shouldInvocationMockerBeReset(PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $enumerator = new Enumerator;

        foreach ($enumerator->enumerate($this->dependencyInput) as $object) {
            if ($mock === $object) {
                return false;
            }
        }

        if (!is_array($this->testResult) && !is_object($this->testResult)) {
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
     * @param array $testArguments
     *
     * @since Method available since Release 5.0.0
     */
    private function registerMockObjectsFromTestArguments(array $testArguments)
    {
        $enumerator = new Enumerator;

        foreach ($enumerator->enumerate($testArguments) as $object) {
            if ($object instanceof PHPUnit_Framework_MockObject_MockObject) {
                $this->mockObjects[] = $object;
            }
        }
    }
}
