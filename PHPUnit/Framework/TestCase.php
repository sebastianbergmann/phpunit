<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/MockObject/Generator.php';
require_once 'PHPUnit/Framework/MockObject/Matcher/InvokedAtLeastOnce.php';
require_once 'PHPUnit/Framework/MockObject/Matcher/InvokedAtIndex.php';
require_once 'PHPUnit/Framework/MockObject/Matcher/InvokedCount.php';
require_once 'PHPUnit/Framework/MockObject/Stub.php';
require_once 'PHPUnit/Runner/BaseTestRunner.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

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
 * require_once 'PHPUnit/Framework/TestCase.php';
 *
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
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 * @abstract
 */
abstract class PHPUnit_Framework_TestCase extends PHPUnit_Framework_Assert implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing
{
    /**
     * Enable or disable the backup and restoration of the $GLOBALS array.
     * Overwrite this attribute in a child class of TestCase.
     * Setting this attribute in setUp() has no effect!
     *
     * @var    boolean
     */
    protected $backupGlobals = NULL;

    /**
     * @var    array
     */
    protected $backupGlobalsBlacklist = array();

    /**
     * @var    array
     */
    protected $globalsBackup = array();

    /**
     * @var    array
     */
    protected $data = array();

    /**
     * @var    string
     */
    protected $dataName = '';

    /**
     * The name of the expected Exception.
     *
     * @var    mixed
     */
    protected $expectedException = NULL;

    /**
     * The message of the expected Exception.
     *
     * @var    string
     */
    protected $expectedExceptionMessage = '';

    /**
     * The code of the expected Exception.
     *
     * @var    integer
     */
    protected $expectedExceptionCode;

    /**
     * Fixture that is shared between the tests of a test suite.
     *
     * @var    mixed
     */
    protected $sharedFixture;

    /**
     * The name of the test case.
     *
     * @var    string
     */
    protected $name = NULL;

    /**
     * @var    array
     */
    protected $dependencies = array();

    /**
     * @var    string
     */
    protected $exceptionMessage = NULL;

    /**
     * @var    integer
     */
    protected $exceptionCode = 0;

    /**
     * @var    Array
     */
    protected $iniSettings = array();

    /**
     * @var    Array
     */
    protected $locale = array();

    /**
     * @var    Array
     */
    protected $mockObjects = array();

    /**
     * @var    integer
     */
    protected $status;

    /**
     * @var    string
     */
    protected $statusMessage = '';

    /**
     * @var    integer
     */
    protected $numAssertions = 0;

    /**
     * @var    array
     */
    protected static $superGlobalArrays = array(
      '_ENV',
      '_POST',
      '_GET',
      '_COOKIE',
      '_SERVER',
      '_FILES',
      '_REQUEST'
    );

    /**
     * @var    array
     */
    protected static $superGlobalArraysLong = array(
      'HTTP_ENV_VARS',
      'HTTP_POST_VARS',
      'HTTP_GET_VARS',
      'HTTP_COOKIE_VARS',
      'HTTP_SERVER_VARS',
      'HTTP_POST_FILES'
    );

    /**
     * @var PHPUnit_Framework_TestResult
     */
    protected $result;

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
          '%s(%s)',

          $this->getName(FALSE),
          $class->name
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
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = 0)
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
            $method            = new ReflectionMethod(get_class($this), $this->name);
            $methodDocComment  = $method->getDocComment();
            $expectedException = PHPUnit_Util_Test::getExpectedException($methodDocComment);

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
     * @throws InvalidArgumentException
     */
    public function run(PHPUnit_Framework_TestResult $result = NULL)
    {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        $this->setExpectedExceptionFromAnnotation();

        $this->result = $result;
        $result->run($this);
        $this->result = NULL;

        return $result;
    }

    /**
     * Runs the bare test sequence.
     *
     */
    public function runBare()
    {
        $this->numAssertions = 0;

        if (!empty($this->dependencies)) {
            $className = get_class($this);
            $passed    = $this->result->passed();

            foreach ($this->dependencies as $dependency) {
                if (strpos($dependency, '::') === FALSE) {
                    $dependency = $className . '::' . $dependency;
                }

                if (!isset($passed[$dependency])) {
                    $this->markTestSkipped(
                      sprintf(
                        'This test depends on "%s" to pass.',

                        $dependency
                      )
                    );
                }
            }
        }

        // Backup the $GLOBALS array.
        if ($this->backupGlobals === NULL || $this->backupGlobals === TRUE) {
            $this->backupGlobals();
        }

        // Set up the fixture.
        $this->setUp();

        // Clean up stat cache.
        clearstatcache();

        // Run the test.
        try {
            // Assert pre-conditions.
            $this->assertPreConditions();

            $this->runTest();

            // Assert post-conditions.
            $this->assertPostConditions();

            // Verify Mock Object conditions.
            foreach ($this->mockObjects as $mockObject) {
                $this->numAssertions++;
                $mockObject->__phpunit_verify();
            }

            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
        }

        catch (Exception $e) {
            if ($e instanceof PHPUnit_Framework_IncompleteTest) {
                $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            }

            if ($e instanceof PHPUnit_Framework_SkippedTest) {
                $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            }

            if ($e instanceof PHPUnit_Framework_AssertionFailedError) {
                $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            }

            $this->statusMessage = $e->getMessage();
        }

        $this->mockObjects = array();

        // Tear down the fixture.
        $this->tearDown();

        // Clean up stat cache.
        clearstatcache();

        // Restore the $GLOBALS array.
        if ($this->backupGlobals === NULL || $this->backupGlobals === TRUE) {
            $this->restoreGlobals();
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

        // Workaround for missing "finally".
        if (isset($e)) {
            throw $e;
        }
    }

    /**
     * Override to run the test and assert its state.
     *
     * @throws RuntimeException
     */
    protected function runTest()
    {
        if ($this->name === NULL) {
            throw new RuntimeException(
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
            if (empty($this->data)) {
                $method->invoke($this);
            } else {
                $method->invokeArgs($this, $this->data);
            }
        }

        catch (Exception $e) {
            if (!$e instanceof PHPUnit_Framework_IncompleteTest &&
                !$e instanceof PHPUnit_Framework_SkippedTest &&
                is_string($this->expectedException) &&
                $e instanceof $this->expectedException) {
                if (is_string($this->expectedExceptionMessage) &&
                    !empty($this->expectedExceptionMessage)) {
                    $this->assertContains(
                      $this->expectedExceptionMessage,
                      $e->getMessage()
                    );
                }

                if (is_int($this->expectedExceptionCode) &&
                    $this->expectedExceptionCode !== 0) {
                    $this->assertEquals(
                      $this->expectedExceptionCode, $e->getCode()
                    );
                }

                $this->numAssertions++;

                return;
            } else {
                throw $e;
            }
        }

        if ($this->expectedException !== NULL) {
            $this->numAssertions++;
            $this->fail('Expected exception ' . $this->expectedException);
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
     * @param  array $dependencies
     * @since  Method available since Release 3.4.0
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
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
     * Sets the shared fixture.
     *
     * @param  mixed $sharedFixture
     * @since  Method available since Release 3.1.0
     */
    public function setSharedFixture($sharedFixture)
    {
        $this->sharedFixture = $sharedFixture;
    }

    /**
     * This method is a wrapper for the ini_set() function that automatically
     * resets the modified php.ini setting to its original value after the
     * test is run.
     *
     * @param  string  $varName
     * @param  string  $newValue
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @since  Method available since Release 3.0.0
     */
    protected function iniSet($varName, $newValue)
    {
        if (!is_string($varName)) {
            throw new InvalidArgumentException;
        }

        $currentValue = ini_set($varName, $newValue);

        if ($currentValue !== FALSE) {
            $this->iniSettings[$varName] = $currentValue;
        } else {
            throw new RuntimeException;
        }
    }

    /**
     * This method is a wrapper for the setlocale() function that automatically
     * resets the locale to its original value after the test is run.
     *
     * @param  integer $category
     * @param  string  $locale
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @since  Method available since Release 3.1.0
     */
    protected function setLocale()
    {
        $args = func_get_args();

        if (count($args) < 2) {
            throw new InvalidArgumentException;
        }

        $category = $args[0];
        $locale   = $args[1];

        if (!in_array($category, array(LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_NUMERIC, LC_TIME, LC_MESSAGES))) {
            throw new InvalidArgumentException;
        }

        if (!is_array($locale) && !is_string($locale)) {
            throw new InvalidArgumentException;
        }

        $this->locale[$category] = setlocale($category, NULL);

        $result = call_user_func_array( 'setlocale', $args );

        if ($result === FALSE) {
            throw new RuntimeException(
              'The locale functionality is not implemented on your platform, ' .
              'the specified locale does not exist or the category name is ' .
              'invalid.'
            );
        }
    }

    /**
     * Returns a mock object for the specified class.
     *
     * @param  string  $className
     * @param  array   $methods
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return object
     * @since  Method available since Release 3.0.0
     */
    protected function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE)
    {
        if (!is_string($originalClassName) || !is_string($mockClassName)) {
            throw new InvalidArgumentException;
        }

        if (!is_array($methods) && !is_null($methods)) {
            throw new InvalidArgumentException;
        }

        if ($mockClassName != '' && class_exists($mockClassName, FALSE)) {
            throw new RuntimeException(
              sprintf(
                'Class "%s" already exists.',
                $mockClassName
              )
            );
        }

        $mock = PHPUnit_Framework_MockObject_Generator::generate(
          $originalClassName,
          $methods,
          $mockClassName,
          $callOriginalConstructor,
          $callOriginalClone,
          $callAutoload
        );

        if (!class_exists($mock['mockClassName'], FALSE)) {
            eval($mock['code']);
        }

        if (count($arguments) == 0) {
            $mockObject = new $mock['mockClassName'];
        } else {
            $mockClass  = new ReflectionClass($mock['mockClassName']);
            $mockObject = $mockClass->newInstanceArgs($arguments);
        }

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
     * @return object
     * @since  Method available since Release 3.4.0
     */
    protected function getMockFromWsdl($wsdlFile, $originalClassName = '', $mockClassName = '', array $methods = array(), $callOriginalConstructor = TRUE)
    {
        if ($originalClassName === '') {
            $originalClassName = str_replace(
              '.wsdl', '', basename($wsdlFile)
            );
        }

        eval(
          PHPUnit_Framework_MockObject_Generator::generateClassFromWsdl(
            $wsdlFile, $originalClassName, $methods
          )
        );

        return $this->getMock(
          $originalClassName,
          $methods,
          array(),
          $mockClassName,
          $callOriginalConstructor,
          FALSE,
          FALSE
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
    protected function any()
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
    protected function never()
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
    protected function atLeastOnce()
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
    protected function once()
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
    protected function exactly($count)
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
    protected function at($index)
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
    protected function returnValue($value)
    {
        return new PHPUnit_Framework_MockObject_Stub_Return($value);
    }

    /**
     *
     *
     * @param  integer $argumentIndex
     * @return PHPUnit_Framework_MockObject_Stub_ReturnArgument
     * @since  Method available since Release 3.3.0
     */
    protected function returnArgument($argumentIndex)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnArgument($argumentIndex);
    }

    /**
     *
     *
     * @param  mixed $callback
     * @return PHPUnit_Framework_MockObject_Stub_ReturnCallback
     * @since  Method available since Release 3.3.0
     */
    protected function returnCallback($callback)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnCallback($callback);
    }

    /**
     *
     *
     * @param  Exception $exception
     * @return PHPUnit_Framework_MockObject_Stub_Exception
     * @since  Method available since Release 3.1.0
     */
    protected function throwException(Exception $exception)
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
    protected function onConsecutiveCalls()
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

        foreach ($data as $_data) {
            if (is_array($_data)) {
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
     * @since Method available since Release 3.3.0
     */
    protected function backupGlobals()
    {
        $this->globalsBackup = array();

        if (ini_get('register_long_arrays') == '1') {
            $superGlobalArrays = array_merge(
              self::$superGlobalArrays, self::$superGlobalArraysLong
            );
        } else {
            $superGlobalArrays = self::$superGlobalArrays;
        }

        foreach ($superGlobalArrays as $superGlobalArray) {
            if (!in_array($superGlobalArray, $this->backupGlobalsBlacklist)) {
                $this->backupSuperGlobalArray($superGlobalArray);
            }
        }

        foreach (array_keys($GLOBALS) as $key) {
            if ($key != 'GLOBALS' && !in_array($key, $superGlobalArrays) &&
                !in_array($key, $this->backupGlobalsBlacklist)) {
                $this->globalsBackup['GLOBALS'][$key] = serialize($GLOBALS[$key]);
            }
        }
    }

    /**
     * @since Method available since Release 3.3.0
     */
    protected function restoreGlobals()
    {
        if (ini_get('register_long_arrays') == '1') {
            $superGlobalArrays = array_merge(
              self::$superGlobalArrays, self::$superGlobalArraysLong
            );
        } else {
            $superGlobalArrays = self::$superGlobalArrays;
        }

        foreach ($superGlobalArrays as $superGlobalArray) {
            if (!in_array($superGlobalArray, $this->backupGlobalsBlacklist)) {
                $this->restoreSuperGlobalArray($superGlobalArray);
            }
        }

        foreach (array_keys($GLOBALS) as $key) {
            if ($key != 'GLOBALS' && !in_array($key, $superGlobalArrays) &&
                !in_array($key, $this->backupGlobalsBlacklist)) {
                if (isset($this->globalsBackup['GLOBALS'][$key])) {
                    $GLOBALS[$key] = unserialize($this->globalsBackup['GLOBALS'][$key]);
                } else {
                    unset($GLOBALS[$key]);
                }
            }
        }

        $this->globalsBackup = array();
    }

    protected function backupSuperGlobalArray($superGlobalArray)
    {
        $this->globalsBackup[$superGlobalArray] = array();

        if (isset($GLOBALS[$superGlobalArray])) {
            foreach ($GLOBALS[$superGlobalArray] as $key => $value) {
                $this->globalsBackup[$superGlobalArray][$key] = serialize($value);
            }
        }
    }

    protected function restoreSuperGlobalArray($superGlobalArray)
    {
        if (isset($GLOBALS[$superGlobalArray])) {
            foreach ($GLOBALS[$superGlobalArray] as $key => $value) {
                if (isset($this->globalsBackup[$superGlobalArray][$key])) {
                    $GLOBALS[$superGlobalArray][$key] = unserialize($this->globalsBackup[$superGlobalArray][$key]);
                } else {
                    unset($GLOBALS[$superGlobalArray][$key]);
                }
            }
        }

        $this->globalsBackup[$superGlobalArray] = array();
    }
}
?>
