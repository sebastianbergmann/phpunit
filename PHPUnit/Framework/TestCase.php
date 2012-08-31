<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2012, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

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
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
abstract class PHPUnit_Framework_TestCase extends PHPUnit_Framework_Assert implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing
{
    /**
     * Enable or disable the backup and restoration of the $GLOBALS array.
     * Overwrite this attribute in a child class of TestCase.
     * Setting this attribute in setUp() has no effect!
     *
     * @var boolean
     */
    protected $backupGlobals = NULL;

    /**
     * @var array
     */
    protected $backupGlobalsBlacklist = array();

    /**
     * Enable or disable the backup and restoration of static attributes.
     * Overwrite this attribute in a child class of TestCase.
     * Setting this attribute in setUp() has no effect!
     *
     * @var boolean
     */
    protected $backupStaticAttributes = NULL;

    /**
     * @var array
     */
    protected $backupStaticAttributesBlacklist = array();

    /**
     * Whether or not this test is to be run in a separate PHP process.
     *
     * @var boolean
     */
    protected $runTestInSeparateProcess = NULL;

    /**
     * Whether or not this test should preserve the global state when
     * running in a separate PHP process.
     *
     * @var boolean
     */
    protected $preserveGlobalState = TRUE;

    /**
     * Whether or not this test is running in a separate PHP process.
     *
     * @var boolean
     */
    private $inIsolation = FALSE;

    /**
     * @var array
     */
    private $data = array();

    /**
     * @var string
     */
    private $dataName = '';

    /**
     * @var boolean
     */
    private $useErrorHandler = NULL;

    /**
     * @var boolean
     */
    private $useOutputBuffering = NULL;

    /**
     * The name of the expected Exception.
     *
     * @var mixed
     */
    private $expectedException = NULL;

    /**
     * The message of the expected Exception.
     *
     * @var string
     */
    private $expectedExceptionMessage = '';

    /**
     * The code of the expected Exception.
     *
     * @var integer
     */
    private $expectedExceptionCode;

    /**
     * The required preconditions for a test.
     *
     * @var array
     */
    private $required = array(
        'PHP' => NULL,
        'PHPUnit' => NULL,
        'functions' => array(),
        'extensions' => array()
    );

    /**
     * The name of the test case.
     *
     * @var string
     */
    private $name = NULL;

    /**
     * @var array
     */
    private $dependencies = array();

    /**
     * @var array
     */
    private $dependencyInput = array();

    /**
     * @var array
     */
    private $iniSettings = array();

    /**
     * @var array
     */
    private $locale = array();

    /**
     * @var array
     */
    private $mockObjects = array();

    /**
     * @var integer
     */
    private $status;

    /**
     * @var string
     */
    private $statusMessage = '';

    /**
     * @var integer
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
    private $outputExpectedRegex = NULL;

    /**
     * @var string
     */
    private $outputExpectedString = NULL;

    /**
     * @var bool
     */
    private $hasPerformedExpectationsOnOutput = FALSE;

    /**
     * @var mixed
     */
    private $outputCallback = FALSE;

    /**
     * @var boolean
     */
    private $outputBufferingActive = FALSE;

    /**
     * Constructs a test case with the given name.
     *
     * @param  string $name
     * @param  array  $data
     * @param  string $dataName
     */
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        if ($name !== NULL) {
            $this->setName($name);
        }

        $this->data     = $data;
        $this->dataName = $dataName;
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
          $this->getName(FALSE)
        );

        return $buffer . $this->getDataSetAsString();
    }

    /**
     * Counts the number of test cases executed by run(TestResult result).
     *
     * @return integer
     */
    public function count()
    {
        return 1;
    }

    /**
     * Returns the annotations for this test.
     *
     * @return array
     * @since Method available since Release 3.4.0
     */
    public function getAnnotations()
    {
        return PHPUnit_Util_Test::parseTestMethodAnnotations(
          get_class($this), $this->name
        );
    }

    /**
     * Gets the name of a TestCase.
     *
     * @param  boolean $withDataSet
     * @return string
     */
    public function getName($withDataSet = TRUE)
    {
        if ($withDataSet) {
            return $this->name . $this->getDataSetAsString(FALSE);
        } else {
            return $this->name;
        }
    }

    /**
     * Returns the size of the test.
     *
     * @return integer
     * @since  Method available since Release 3.6.0
     */
    public function getSize()
    {
        return PHPUnit_Util_Test::getSize(
          get_class($this), $this->getName(FALSE)
        );
    }

    /**
     * @return string
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
     * @return string
     * @since  Method available since Release 3.6.0
     */
    public function hasOutput()
    {
        if (empty($this->output)) {
            return FALSE;
        }

        if ($this->outputExpectedString !== NULL ||
            $this->outputExpectedRegex  !== NULL ||
            $this->hasPerformedExpectationsOnOutput) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param string $expectedRegex
     * @since Method available since Release 3.6.0
     */
    public function expectOutputRegex($expectedRegex)
    {
        if ($this->outputExpectedString !== NULL) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedRegex) || is_null($expectedRegex)) {
            $this->outputExpectedRegex = $expectedRegex;
        }
    }

    /**
     * @param string $expectedString
     * @since Method available since Release 3.6.0
     */
    public function expectOutputString($expectedString)
    {
        if ($this->outputExpectedRegex !== NULL) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedString) || is_null($expectedString)) {
            $this->outputExpectedString = $expectedString;
        }
    }

    /**
     * @return bool
     * @since Method available since Release 3.6.5
     */
    public function hasPerformedExpectationsOnOutput()
    {
        return $this->hasPerformedExpectationsOnOutput;
    }

    /**
     * @return string
     * @since  Method available since Release 3.2.0
     */
    public function getExpectedException()
    {
        return $this->expectedException;
    }

    /**
     * @param  mixed   $exceptionName
     * @param  string  $exceptionMessage
     * @param  integer $exceptionCode
     * @since  Method available since Release 3.2.0
     */
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = NULL)
    {
        $this->expectedException        = $exceptionName;
        $this->expectedExceptionMessage = $exceptionMessage;
        $this->expectedExceptionCode    = $exceptionCode;
    }

    /**
     * @since  Method available since Release 3.4.0
     */
    protected function setExpectedExceptionFromAnnotation()
    {
        try {
            $expectedException = PHPUnit_Util_Test::getExpectedException(
              get_class($this), $this->name
            );

            if ($expectedException !== FALSE) {
                $this->setExpectedException(
                  $expectedException['class'],
                  $expectedException['message'],
                  $expectedException['code']
                );
            }
        }

        catch (ReflectionException $e) {
        }
    }

    /**
     * @param boolean $useErrorHandler
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
              get_class($this), $this->name
            );

            if ($useErrorHandler !== NULL) {
                $this->setUseErrorHandler($useErrorHandler);
            }
        }

        catch (ReflectionException $e) {
        }
    }

    /**
     * @param boolean $useOutputBuffering
     * @since Method available since Release 3.4.0
     */
    public function setUseOutputBuffering($useOutputBuffering)
    {
        $this->useOutputBuffering = $useOutputBuffering;
    }

    /**
     * @since Method available since Release 3.4.0
     */
    protected function setUseOutputBufferingFromAnnotation()
    {
        try {
            $useOutputBuffering = PHPUnit_Util_Test::getOutputBufferingSettings(
              get_class($this), $this->name
            );

            if ($useOutputBuffering !== NULL) {
                $this->setUseOutputBuffering($useOutputBuffering);
            }
        }

        catch (ReflectionException $e) {
        }
    }

    /**
     * @since Method available since Release 3.6.0
     */
    protected function setRequirementsFromAnnotation()
    {
        try {
            $requirements = PHPUnit_Util_Test::getRequirements(
              get_class($this), $this->name
            );

            if (isset($requirements['PHP'])) {
                $this->required['PHP'] = $requirements['PHP'];
            }

            if (isset($requirements['PHPUnit'])) {
                $this->required['PHPUnit'] = $requirements['PHPUnit'];
            }

            if (isset($requirements['extensions'])) {
                $this->required['extensions'] = $requirements['extensions'];
            }

            if (isset($requirements['functions'])) {
                $this->required['functions'] = $requirements['functions'];
            }
        }

        catch (ReflectionException $e) {
        }
    }

    /**
     * @since Method available since Release 3.6.0
     */
    protected function checkRequirements()
    {
        $this->setRequirementsFromAnnotation();

        $missingRequirements = array();

        if ($this->required['PHP'] &&
            version_compare(PHP_VERSION, $this->required['PHP'], '<')) {
            $missingRequirements[] = sprintf(
              'PHP %s (or later) is required.',
              $this->required['PHP']
            );
        }

        $phpunitVersion = PHPUnit_Runner_Version::id();
        if ($this->required['PHPUnit'] &&
            version_compare($phpunitVersion, $this->required['PHPUnit'], '<')) {
            $missingRequirements[] = sprintf(
              'PHPUnit %s (or later) is required.',
              $this->required['PHPUnit']
            );
        }

        foreach ($this->required['functions'] as $requiredFunction) {
            if (!function_exists($requiredFunction)) {
                $missingRequirements[] = sprintf(
                  'Function %s is required.',
                  $requiredFunction
                );
            }
        }

        foreach ($this->required['extensions'] as $requiredExtension) {
            if (!extension_loaded($requiredExtension)) {
                $missingRequirements[] = sprintf(
                  'Extension %s is required.',
                  $requiredExtension
                );
            }
        }

        if ($missingRequirements) {
            $this->markTestSkipped(
              implode(
                PHP_EOL,
                $missingRequirements
              )
            );
        }
    }

    /**
     * Returns the status of this test.
     *
     * @return integer
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
     * @since  Method available since Release 3.3.0
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * Returns whether or not this test has failed.
     *
     * @return boolean
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
     * @param  PHPUnit_Framework_TestResult $result
     * @return PHPUnit_Framework_TestResult
     * @throws PHPUnit_Framework_Exception
     */
    public function run(PHPUnit_Framework_TestResult $result = NULL)
    {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        if (!$this instanceof PHPUnit_Framework_Warning) {
            $this->setTestResultObject($result);
            $this->setUseErrorHandlerFromAnnotation();
            $this->setUseOutputBufferingFromAnnotation();
        }

        if ($this->useErrorHandler !== NULL) {
            $oldErrorHandlerSetting = $result->getConvertErrorsToExceptions();
            $result->convertErrorsToExceptions($this->useErrorHandler);
        }

        if (!$this->handleDependencies()) {
            return;
        }

        if ($this->runTestInSeparateProcess === TRUE &&
            $this->inIsolation !== TRUE &&
            !$this instanceof PHPUnit_Extensions_SeleniumTestCase &&
            !$this instanceof PHPUnit_Extensions_PhptTestCase) {
            $class = new ReflectionClass($this);

            $template = new Text_Template(
              sprintf(
                '%s%sProcess%sTestCaseMethod.tpl',

                __DIR__,
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR
              )
            );

            if ($this->preserveGlobalState) {
                $constants     = PHPUnit_Util_GlobalState::getConstantsAsString();
                $globals       = PHPUnit_Util_GlobalState::getGlobalsAsString();
                $includedFiles = PHPUnit_Util_GlobalState::getIncludedFilesAsString();
            } else {
                $constants     = '';
                $globals       = '';
                $includedFiles = '';
            }

            if ($result->getCollectCodeCoverageInformation()) {
                $coverage = 'TRUE';
            } else {
                $coverage = 'FALSE';
            }

            if ($result->isStrict()) {
                $strict = 'TRUE';
            } else {
                $strict = 'FALSE';
            }

            $data            = var_export(serialize($this->data), TRUE);
            $dependencyInput = var_export(serialize($this->dependencyInput), TRUE);
            $includePath     = var_export(get_include_path(), TRUE);
            // must do these fixes because TestCaseMethod.tpl has unserialize('{data}') in it, and we can't break BC
            // the lines above used to use addcslashes() rather than var_export(), which breaks null byte escape sequences
            $data            = "'." . $data . ".'";
            $dependencyInput = "'." . $dependencyInput . ".'";
            $includePath     = "'." . $includePath . ".'";

            $template->setVar(
              array(
                'filename'                       => $class->getFileName(),
                'className'                      => $class->getName(),
                'methodName'                     => $this->name,
                'collectCodeCoverageInformation' => $coverage,
                'data'                           => $data,
                'dataName'                       => $this->dataName,
                'dependencyInput'                => $dependencyInput,
                'constants'                      => $constants,
                'globals'                        => $globals,
                'include_path'                   => $includePath,
                'included_files'                 => $includedFiles,
                'strict'                         => $strict
              )
            );

            $this->prepareTemplate($template);

            $php = PHPUnit_Util_PHP::factory();
            $php->runJob($template->render(), $this, $result);
        } else {
            $result->run($this);
        }

        if ($this->useErrorHandler !== NULL) {
            $result->convertErrorsToExceptions($oldErrorHandlerSetting);
        }

        $this->result = NULL;

        return $result;
    }

    /**
     * Runs the bare test sequence.
     */
    public function runBare()
    {
        $this->numAssertions = 0;

        // Backup the $GLOBALS array and static attributes.
        if ($this->runTestInSeparateProcess !== TRUE &&
            $this->inIsolation !== TRUE) {
            if ($this->backupGlobals === NULL ||
                $this->backupGlobals === TRUE) {
                PHPUnit_Util_GlobalState::backupGlobals(
                  $this->backupGlobalsBlacklist
                );
            }

            if ($this->backupStaticAttributes === TRUE) {
                PHPUnit_Util_GlobalState::backupStaticAttributes(
                  $this->backupStaticAttributesBlacklist
                );
            }
        }

        // Start output buffering.
        ob_start();
        $this->outputBufferingActive = TRUE;

        // Clean up stat cache.
        clearstatcache();

        // Backup the cwd
        $currentWorkingDirectory = getcwd();

        try {
            if ($this->inIsolation) {
                $this->setUpBeforeClass();
            }

            $this->setExpectedExceptionFromAnnotation();
            $this->setUp();
            $this->checkRequirements();
            $this->assertPreConditions();
            $this->testResult = $this->runTest();
            $this->verifyMockObjects();
            $this->assertPostConditions();
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
        }

        catch (PHPUnit_Framework_IncompleteTest $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            $this->statusMessage = $e->getMessage();
        }

        catch (PHPUnit_Framework_SkippedTest $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            $this->statusMessage = $e->getMessage();
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->statusMessage = $e->getMessage();
        }

        catch (Exception $e) {
            $this->status        = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
            $this->statusMessage = $e->getMessage();
        }

        // Tear down the fixture. An exception raised in tearDown() will be
        // caught and passed on when no exception was raised before.
        try {
            $this->tearDown();

            if ($this->inIsolation) {
                $this->tearDownAfterClass();
            }
        }

        catch (Exception $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }

        // Stop output buffering.
        if ($this->outputCallback === FALSE) {
            $this->output = ob_get_contents();
        } else {
            $this->output = call_user_func_array(
              $this->outputCallback, array(ob_get_contents())
            );
        }

        ob_end_clean();
        $this->outputBufferingActive = FALSE;

        // Clean up stat cache.
        clearstatcache();

        // Restore the cwd if it was changed by the test
        if ($currentWorkingDirectory != getcwd()) {
            chdir($currentWorkingDirectory);
        }

        // Restore the $GLOBALS array and static attributes.
        if ($this->runTestInSeparateProcess !== TRUE &&
            $this->inIsolation !== TRUE) {
            if ($this->backupGlobals === NULL ||
                $this->backupGlobals === TRUE) {
                PHPUnit_Util_GlobalState::restoreGlobals(
                   $this->backupGlobalsBlacklist
                );
            }

            if ($this->backupStaticAttributes === TRUE) {
                PHPUnit_Util_GlobalState::restoreStaticAttributes();
            }
        }

        // Clean up INI settings.
        foreach ($this->iniSettings as $varName => $oldValue) {
            ini_set($varName, $oldValue);
        }

        $this->iniSettings = array();

        // Clean up locale settings.
        foreach ($this->locale as $category => $locale) {
            setlocale($category, $locale);
        }

        // Perform assertion on output.
        if (!isset($e)) {
            try {
                if ($this->outputExpectedRegex !== NULL) {
                    $this->hasPerformedExpectationsOnOutput = TRUE;
                    $this->assertRegExp($this->outputExpectedRegex, $this->output);
                    $this->outputExpectedRegex = NULL;
                }

                else if ($this->outputExpectedString !== NULL) {
                    $this->hasPerformedExpectationsOnOutput = TRUE;
                    $this->assertEquals($this->outputExpectedString, $this->output);
                    $this->outputExpectedString = NULL;
                }
            }

            catch (Exception $_e) {
                $e = $_e;
            }
        }

        // Workaround for missing "finally".
        if (isset($e)) {
            $this->onNotSuccessfulTest($e);
        }
    }

    /**
     * Override to run the test and assert its state.
     *
     * @return mixed
     * @throws PHPUnit_Framework_Exception
     */
    protected function runTest()
    {
        if ($this->name === NULL) {
            throw new PHPUnit_Framework_Exception(
              'PHPUnit_Framework_TestCase::$name must not be NULL.'
            );
        }

        try {
            $class  = new ReflectionClass($this);
            $method = $class->getMethod($this->name);
        }

        catch (ReflectionException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $testResult = $method->invokeArgs(
              $this, array_merge($this->data, $this->dependencyInput)
            );
        }

        catch (Exception $e) {
            $checkException = FALSE;

            if (is_string($this->expectedException)) {
                $checkException = TRUE;

                if ($e instanceof PHPUnit_Framework_Exception) {
                    $checkException = FALSE;
                }

                $reflector = new ReflectionClass($this->expectedException);

                if ($this->expectedException == 'PHPUnit_Framework_Exception' ||
                    $reflector->isSubclassOf('PHPUnit_Framework_Exception')) {
                    $checkException = TRUE;
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

                if ($this->expectedExceptionCode !== NULL) {
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

        if ($this->expectedException !== NULL) {
            $this->assertThat(
              NULL,
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

            $mockObject->__phpunit_verify();
            $mockObject->__phpunit_cleanup();
        }

        $this->mockObjects = array();
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
     * @param  array $dependencies
     * @since  Method available since Release 3.4.0
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Sets
     *
     * @param  array $dependencyInput
     * @since  Method available since Release 3.4.0
     */
    public function setDependencyInput(array $dependencyInput)
    {
        $this->dependencyInput = $dependencyInput;
    }

    /**
     * Calling this method in setUp() has no effect!
     *
     * @param  boolean $backupGlobals
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
     * @param  boolean $backupStaticAttributes
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
     * @param  boolean $runTestInSeparateProcess
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.4.0
     */
    public function setRunTestInSeparateProcess($runTestInSeparateProcess)
    {
        if (is_bool($runTestInSeparateProcess)) {
            if ($this->runTestInSeparateProcess === NULL) {
                $this->runTestInSeparateProcess = $runTestInSeparateProcess;
            }
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @param  boolean $preserveGlobalState
     * @throws PHPUnit_Framework_Exception
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
     * @param  boolean $inIsolation
     * @throws PHPUnit_Framework_Exception
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
     * @return mixed
     * @since  Method available since Release 3.4.0
     */
    public function getResult()
    {
        return $this->testResult;
    }

    /**
     * @param  mixed $result
     * @since  Method available since Release 3.4.0
     */
    public function setResult($result)
    {
        $this->testResult = $result;
    }

    /**
     * @param  callable $callback
     * @throws PHPUnit_Framework_Exception
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
     * @since  Method available since Release 3.5.7
     */
    public function getTestResultObject()
    {
        return $this->result;
    }

    /**
     * @param PHPUnit_Framework_TestResult $result
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
     * @param  string  $varName
     * @param  string  $newValue
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.0.0
     */
    protected function iniSet($varName, $newValue)
    {
        if (!is_string($varName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $currentValue = ini_set($varName, $newValue);

        if ($currentValue !== FALSE) {
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
     * @param  integer $category
     * @param  string  $locale
     * @throws PHPUnit_Framework_Exception
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

        $categories = array(
          LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_NUMERIC, LC_TIME
        );

        if (defined('LC_MESSAGES')) {
            $categories[] = LC_MESSAGES;
        }

        if (!in_array($category, $categories)) {
            throw new PHPUnit_Framework_Exception;
        }

        if (!is_array($locale) && !is_string($locale)) {
            throw new PHPUnit_Framework_Exception;
        }

        $this->locale[$category] = setlocale($category, NULL);

        $result = call_user_func_array( 'setlocale', $args );

        if ($result === FALSE) {
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
     * @param  string  $originalClassName
     * @param  array   $methods
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @param  boolean $cloneArguments
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.0.0
     */
    public function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE, $cloneArguments = FALSE)
    {
        $mockObject = PHPUnit_Framework_MockObject_Generator::getMock(
          $originalClassName,
          $methods,
          $arguments,
          $mockClassName,
          $callOriginalConstructor,
          $callOriginalClone,
          $callAutoload,
          $cloneArguments
        );

        $this->mockObjects[] = $mockObject;

        return $mockObject;
    }

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @param  string $className
     * @return PHPUnit_Framework_MockObject_MockBuilder
     * @since  Method available since Release 3.5.0
     */
    public function getMockBuilder($className)
    {
        return new PHPUnit_Framework_MockObject_MockBuilder(
          $this, $className
        );
    }

    /**
     * Mocks the specified class and returns the name of the mocked class.
     *
     * @param  string  $originalClassName
     * @param  array   $methods
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @param  boolean $cloneArguments
     * @return string
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.5.0
     */
    protected function getMockClass($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = FALSE, $callOriginalClone = TRUE, $callAutoload = TRUE, $cloneArguments = FALSE)
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
     * methods of the class mocked. Concrete methods to mock can be specified with
     * the last parameter
     *
     * @param  string  $originalClassName
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @param  array   $mockedMethods
     * @param  boolean $cloneArguments
     * @return PHPUnit_Framework_MockObject_MockObject
     * @since  Method available since Release 3.4.0
     * @throws PHPUnit_Framework_Exception
     */
    public function getMockForAbstractClass($originalClassName, array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE, $mockedMethods = array(), $cloneArguments = FALSE)
    {
        $mockObject = PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass(
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
     * @param  string  $wsdlFile
     * @param  string  $originalClassName
     * @param  string  $mockClassName
     * @param  array   $methods
     * @param  boolean $callOriginalConstructor
     * @return PHPUnit_Framework_MockObject_MockObject
     * @since  Method available since Release 3.4.0
     */
    protected function getMockFromWsdl($wsdlFile, $originalClassName = '', $mockClassName = '', array $methods = array(), $callOriginalConstructor = TRUE)
    {
        if ($originalClassName === '') {
            $originalClassName = str_replace(
              '.wsdl', '', basename($wsdlFile)
            );
        }

        if (!class_exists($originalClassName)) {
          eval(
            PHPUnit_Framework_MockObject_Generator::generateClassFromWsdl(
              $wsdlFile, $originalClassName, $methods
            )
          );
        }

        return $this->getMock(
          $originalClassName,
          $methods,
          array('', array()),
          $mockClassName,
          $callOriginalConstructor,
          FALSE,
          FALSE
        );
    }

    /**
     * Returns an object for the specified trait.
     *
     * @param  string  $traitName
     * @param  array   $arguments
     * @param  string  $traitClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @param  boolean $cloneArguments
     * @return object
     * @since  Method available since Release 3.6.0
     * @throws PHPUnit_Framework_Exception
     */
    protected function getObjectForTrait($traitName, array $arguments = array(), $traitClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE, $cloneArguments = FALSE)
    {
        return PHPUnit_Framework_MockObject_Generator::getObjectForTrait(
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
     * Adds a value to the assertion counter.
     *
     * @param integer $count
     * @since Method available since Release 3.3.3
     */
    public function addToAssertionCount($count)
    {
        $this->numAssertions += $count;
    }

    /**
     * Returns the number of assertions performed by this test.
     *
     * @return integer
     * @since  Method available since Release 3.3.0
     */
    public function getNumAssertions()
    {
        return $this->numAssertions;
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed zero or more times.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     * @since  Method available since Release 3.0.0
     */
    public static function any()
    {
        return new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is never executed.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
     * @since  Method available since Release 3.0.0
     */
    public static function never()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount(0);
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed at least once.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce
     * @since  Method available since Release 3.0.0
     */
    public static function atLeastOnce()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce;
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed exactly once.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
     * @since  Method available since Release 3.0.0
     */
    public static function once()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount(1);
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed exactly $count times.
     *
     * @param  integer $count
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
     * @since  Method available since Release 3.0.0
     */
    public static function exactly($count)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount($count);
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is invoked at the given $index.
     *
     * @param  integer $index
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex
     * @since  Method available since Release 3.0.0
     */
    public static function at($index)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex($index);
    }

    /**
     *
     *
     * @param  mixed $value
     * @return PHPUnit_Framework_MockObject_Stub_Return
     * @since  Method available since Release 3.0.0
     */
    public static function returnValue($value)
    {
        return new PHPUnit_Framework_MockObject_Stub_Return($value);
    }

    /**
     *
     *
     * @param  array $valueMap
     * @return PHPUnit_Framework_MockObject_Stub_ReturnValueMap
     * @since  Method available since Release 3.6.0
     */
    public static function returnValueMap(array $valueMap)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnValueMap($valueMap);
    }

    /**
     *
     *
     * @param  integer $argumentIndex
     * @return PHPUnit_Framework_MockObject_Stub_ReturnArgument
     * @since  Method available since Release 3.3.0
     */
    public static function returnArgument($argumentIndex)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnArgument(
          $argumentIndex
        );
    }

    /**
     *
     *
     * @param  mixed $callback
     * @return PHPUnit_Framework_MockObject_Stub_ReturnCallback
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
     * @since  Method available since Release 3.6.0
     */
    public static function returnSelf()
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnSelf();
    }

    /**
     *
     *
     * @param  Exception $exception
     * @return PHPUnit_Framework_MockObject_Stub_Exception
     * @since  Method available since Release 3.1.0
     */
    public static function throwException(Exception $exception)
    {
        return new PHPUnit_Framework_MockObject_Stub_Exception($exception);
    }

    /**
     *
     *
     * @param  mixed $value, ...
     * @return PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls
     * @since  Method available since Release 3.0.0
     */
    public static function onConsecutiveCalls()
    {
        $args = func_get_args();

        return new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($args);
    }

    /**
     * @param  mixed $data
     * @return string
     * @since  Method available since Release 3.2.1
     */
    protected function dataToString($data)
    {
        $result = array();

        // There seems to be no other way to check arrays for recursion
        // http://www.php.net/manual/en/language.types.array.php#73936
        preg_match_all('/\n            \[(\w+)\] => Array\s+\*RECURSION\*/', print_r($data, TRUE), $matches);
        $recursiveKeys = array_unique($matches[1]);

        // Convert to valid array keys
        // Numeric integer strings are automatically converted to integers
        // by PHP
        foreach ($recursiveKeys as $key => $recursiveKey) {
            if ((string)(integer)$recursiveKey === $recursiveKey) {
                $recursiveKeys[$key] = (integer)$recursiveKey;
            }
        }

        foreach ($data as $key => $_data) {
            if (in_array($key, $recursiveKeys, TRUE)) {
                $result[] = '*RECURSION*';
            }

            else if (is_array($_data)) {
                $result[] = 'array(' . $this->dataToString($_data) . ')';
            }

            else if (is_object($_data)) {
                $object = new ReflectionObject($_data);

                if ($object->hasMethod('__toString')) {
                    $result[] = (string)$_data;
                } else {
                    $result[] = get_class($_data);
                }
            }

            else if (is_resource($_data)) {
                $result[] = '<resource>';
            }

            else {
                $result[] = var_export($_data, TRUE);
            }
        }

        return join(', ', $result);
    }

    /**
     * Gets the data set description of a TestCase.
     *
     * @param  boolean $includeData
     * @return string
     * @since  Method available since Release 3.3.0
     */
    protected function getDataSetAsString($includeData = TRUE)
    {
        $buffer = '';

        if (!empty($this->data)) {
            if (is_int($this->dataName)) {
                $buffer .= sprintf(' with data set #%d', $this->dataName);
            } else {
                $buffer .= sprintf(' with data set "%s"', $this->dataName);
            }

            if ($includeData) {
                $buffer .= sprintf(' (%s)', $this->dataToString($this->data));
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

                if ($pos !== FALSE) {
                    $passedKeys[$i] = substr($passedKeys[$i], 0, $pos);
                }
            }

            $passedKeys = array_flip(array_unique($passedKeys));

            foreach ($this->dependencies as $dependency) {
                if (strpos($dependency, '::') === FALSE) {
                    $dependency = $className . '::' . $dependency;
                }

                if (!isset($passedKeys[$dependency])) {
                    $this->result->addError(
                      $this,
                      new PHPUnit_Framework_SkippedTestError(
                        sprintf(
                          'This test depends on "%s" to pass.', $dependency
                        )
                      ),
                      0
                    );

                    return FALSE;
                }

                if (isset($passed[$dependency])) {
                    if ($passed[$dependency]['size'] > $this->getSize()) {
                        $this->result->addError(
                          $this,
                          new PHPUnit_Framework_SkippedTestError(
                            'This test depends on a test that is larger than itself.'
                          ),
                          0
                        );

                        return FALSE;
                    }

                    $this->dependencyInput[] = $passed[$dependency]['result'];
                } else {
                    $this->dependencyInput[] = NULL;
                }
            }
        }

        return TRUE;
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
     *
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
     * @param Exception $e
     * @since Method available since Release 3.4.0
     */
    protected function onNotSuccessfulTest(Exception $e)
    {
        throw $e;
    }

    /**
     * Performs custom preparations on the process isolation template.
     *
     * @param Text_Template $template
     * @since Method available since Release 3.4.0
     */
    protected function prepareTemplate(Text_Template $template)
    {
    }
}
