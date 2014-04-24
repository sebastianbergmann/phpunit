<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

/**
 * A TestSuite is a composite of Tests. It runs a collection of test cases.
 *
 * Here is an example using the dynamic test definition.
 *
 * <code>
 * <?php
 * $suite = new PHPUnit_Framework_TestSuite;
 * $suite->addTest(new MathTest('testPass'));
 * ?>
 * </code>
 *
 * Alternatively, a TestSuite can extract the tests to be run automatically.
 * To do so you pass a ReflectionClass instance for your
 * PHPUnit_Framework_TestCase class to the PHPUnit_Framework_TestSuite
 * constructor.
 *
 * <code>
 * <?php
 * $suite = new PHPUnit_Framework_TestSuite(
 *   new ReflectionClass('MathTest')
 * );
 * ?>
 * </code>
 *
 * This constructor creates a suite with all the methods starting with
 * "test" that take no arguments.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Framework_TestSuite implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing, IteratorAggregate
{
    /**
     * Enable or disable the backup and restoration of the $GLOBALS array.
     *
     * @var    boolean
     */
    protected $backupGlobals = NULL;

    /**
     * Enable or disable the backup and restoration of static attributes.
     *
     * @var    boolean
     */
    protected $backupStaticAttributes = NULL;

    /**
     * The name of the test suite.
     *
     * @var    string
     */
    protected $name = '';

    /**
     * The test groups of the test suite.
     *
     * @var    array
     */
    protected $groups = array();

    /**
     * The tests in the test suite.
     *
     * @var    array
     */
    protected $tests = array();

    /**
     * The number of tests in the test suite.
     *
     * @var    integer
     */
    protected $numTests = -1;

    /**
     * @var boolean
     */
    protected $testCase = FALSE;

    /**
     * Constructs a new TestSuite:
     *
     *   - PHPUnit_Framework_TestSuite() constructs an empty TestSuite.
     *
     *   - PHPUnit_Framework_TestSuite(ReflectionClass) constructs a
     *     TestSuite from the given class.
     *
     *   - PHPUnit_Framework_TestSuite(ReflectionClass, String)
     *     constructs a TestSuite from the given class with the given
     *     name.
     *
     *   - PHPUnit_Framework_TestSuite(String) either constructs a
     *     TestSuite from the given class (if the passed string is the
     *     name of an existing class) or constructs an empty TestSuite
     *     with the given name.
     *
     * @param  mixed  $theClass
     * @param  string $name
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct($theClass = '', $name = '')
    {
        $argumentsValid = FALSE;

        if (is_object($theClass) &&
            $theClass instanceof ReflectionClass) {
            $argumentsValid = TRUE;
        }

        else if (is_string($theClass) &&
                 $theClass !== '' &&
                 class_exists($theClass, FALSE)) {
            $argumentsValid = TRUE;

            if ($name == '') {
                $name = $theClass;
            }

            $theClass = new ReflectionClass($theClass);
        }

        else if (is_string($theClass)) {
            $this->setName($theClass);
            return;
        }

        if (!$argumentsValid) {
            throw new PHPUnit_Framework_Exception;
        }

        if (!$theClass->isSubclassOf('PHPUnit_Framework_TestCase')) {
            throw new PHPUnit_Framework_Exception(
              'Class "' . $theClass->name . '" does not extend PHPUnit_Framework_TestCase.'
            );
        }

        if ($name != '') {
            $this->setName($name);
        } else {
            $this->setName($theClass->getName());
        }

        $constructor = $theClass->getConstructor();

        if ($constructor !== NULL &&
            !$constructor->isPublic()) {
            $this->addTest(
              self::warning(
                sprintf(
                  'Class "%s" has no public constructor.',

                  $theClass->getName()
                )
              )
            );

            return;
        }

        foreach ($theClass->getMethods() as $method) {
            $this->addTestMethod($theClass, $method);
        }

        if (empty($this->tests)) {
            $this->addTest(
              self::warning(
                sprintf(
                  'No tests found in class "%s".',

                  $theClass->getName()
                )
              )
            );
        }

        $this->testCase = TRUE;
    }

    /**
     * Returns a string representation of the test suite.
     *
     * @return string
     */
    public function toString()
    {
        return $this->getName();
    }

    /**
     * Adds a test to the suite.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  array                  $groups
     */
    public function addTest(PHPUnit_Framework_Test $test, $groups = array())
    {
        $class = new ReflectionClass($test);

        if (!$class->isAbstract()) {
            $this->tests[]  = $test;
            $this->numTests = -1;

            if ($test instanceof PHPUnit_Framework_TestSuite &&
                empty($groups)) {
                $groups = $test->getGroups();
            }

            if (empty($groups)) {
                $groups = array('__nogroup__');
            }

            foreach ($groups as $group) {
                if (!isset($this->groups[$group])) {
                    $this->groups[$group] = array($test);
                } else {
                    $this->groups[$group][] = $test;
                }
            }
        }
    }

    /**
     * Adds the tests from the given class to the suite.
     *
     * @param  mixed $testClass
     * @throws PHPUnit_Framework_Exception
     */
    public function addTestSuite($testClass)
    {
        if (is_string($testClass) && class_exists($testClass)) {
            $testClass = new ReflectionClass($testClass);
        }

        if (!is_object($testClass)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
              1, 'class name or object'
            );
        }

        if ($testClass instanceof PHPUnit_Framework_TestSuite) {
            $this->addTest($testClass);
        }

        else if ($testClass instanceof ReflectionClass) {
            $suiteMethod = FALSE;

            if (!$testClass->isAbstract()) {
                if ($testClass->hasMethod(PHPUnit_Runner_BaseTestRunner::SUITE_METHODNAME)) {
                    $method = $testClass->getMethod(
                      PHPUnit_Runner_BaseTestRunner::SUITE_METHODNAME
                    );

                    if ($method->isStatic()) {
                        $this->addTest(
                          $method->invoke(NULL, $testClass->getName())
                        );

                        $suiteMethod = TRUE;
                    }
                }
            }

            if (!$suiteMethod && !$testClass->isAbstract()) {
                $this->addTest(new PHPUnit_Framework_TestSuite($testClass));
            }
        }

        else {
            throw new PHPUnit_Framework_Exception;
        }
    }

    /**
     * Wraps both <code>addTest()</code> and <code>addTestSuite</code>
     * as well as the separate import statements for the user's convenience.
     *
     * If the named file cannot be read or there are no new tests that can be
     * added, a <code>PHPUnit_Framework_Warning</code> will be created instead,
     * leaving the current test run untouched.
     *
     * @param  string  $filename
     * @param  array   $phptOptions Array with ini settings for the php instance
     *                              run, key being the name if the setting,
     *                              value the ini value.
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 2.3.0
     * @author Stefano F. Rausch <stefano@rausch-e.net>
     */
    public function addTestFile($filename, $phptOptions = array())
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (file_exists($filename) && substr($filename, -5) == '.phpt') {
            $this->addTest(
              new PHPUnit_Extensions_PhptTestCase($filename, $phptOptions)
            );

            return;
        }

        PHPUnit_Util_Class::collectStart();
        $filename   = PHPUnit_Util_Fileloader::checkAndLoad($filename);
        $newClasses = PHPUnit_Util_Class::collectEnd();
        $baseName   = str_replace('.php', '', basename($filename));

        foreach ($newClasses as $className) {
            if (substr($className, 0 - strlen($baseName)) == $baseName) {
                $class = new ReflectionClass($className);

                if ($class->getFileName() == $filename) {
                    $newClasses = array($className);
                    break;
                }
            }
        }

        $testsFound = FALSE;

        foreach ($newClasses as $className) {
            $class = new ReflectionClass($className);

            if (!$class->isAbstract()) {
                if ($class->hasMethod(PHPUnit_Runner_BaseTestRunner::SUITE_METHODNAME)) {
                    $method = $class->getMethod(
                      PHPUnit_Runner_BaseTestRunner::SUITE_METHODNAME
                    );

                    if ($method->isStatic()) {
                        $this->addTest($method->invoke(NULL, $className));

                        $testsFound = TRUE;
                    }
                }

                else if ($class->implementsInterface('PHPUnit_Framework_Test')) {
                    $this->addTestSuite($class);

                    $testsFound = TRUE;
                }
            }
        }

        $this->numTests = -1;
    }

    /**
     * Wrapper for addTestFile() that adds multiple test files.
     *
     * @param  array|Iterator $filenames
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 2.3.0
     */
    public function addTestFiles($filenames)
    {
        if (!(is_array($filenames) ||
             (is_object($filenames) && $filenames instanceof Iterator))) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
              1, 'array or iterator'
            );
        }

        foreach ($filenames as $filename) {
            $this->addTestFile((string)$filename);
        }
    }

    /**
     * Counts the number of test cases that will be run by this test.
     *
     * @return integer
     */
    public function count()
    {
        if ($this->numTests > -1) {
            return $this->numTests;
        }

        $this->numTests = 0;

        foreach ($this->tests as $test) {
            $this->numTests += count($test);
        }

        return $this->numTests;
    }

    /**
     * @param  ReflectionClass $theClass
     * @param  string          $name
     * @return PHPUnit_Framework_Test
     * @throws PHPUnit_Framework_Exception
     */
    public static function createTest(ReflectionClass $theClass, $name)
    {
        $className = $theClass->getName();

        if (!$theClass->isInstantiable()) {
            return self::warning(
              sprintf('Cannot instantiate class "%s".', $className)
            );
        }

        $backupSettings           = PHPUnit_Util_Test::getBackupSettings(
                                      $className, $name
                                    );
        $preserveGlobalState      = PHPUnit_Util_Test::getPreserveGlobalStateSettings(
                                      $className, $name
                                    );
        $runTestInSeparateProcess = PHPUnit_Util_Test::getProcessIsolationSettings(
                                      $className, $name
                                    );

        $constructor = $theClass->getConstructor();

        if ($constructor !== NULL) {
            $parameters = $constructor->getParameters();

            // TestCase() or TestCase($name)
            if (count($parameters) < 2) {
                $test = new $className;
            }

            // TestCase($name, $data)
            else {
                try {
                    $data = PHPUnit_Util_Test::getProvidedData(
                      $className, $name
                    );
                }

                catch (Exception $e) {
                    $message = sprintf(
                      'The data provider specified for %s::%s is invalid.',
                      $className,
                      $name
                    );

                    $_message = $e->getMessage();

                    if (!empty($_message)) {
                        $message .= "\n" . $_message;
                    }

                    $data = self::warning($message);
                }

                // Test method with @dataProvider.
                if (isset($data)) {
                    $test = new PHPUnit_Framework_TestSuite_DataProvider(
                      $className . '::' . $name
                    );

                    if (empty($data)) {
                        $data = self::warning(
                          sprintf(
                            'No tests found in suite "%s".',
                            $test->getName()
                          )
                        );
                    }

                    $groups = PHPUnit_Util_Test::getGroups($className, $name);

                    if ($data instanceof PHPUnit_Framework_Warning) {
                        $test->addTest($data, $groups);
                    }

                    else {
                        foreach ($data as $_dataName => $_data) {
                            $_test = new $className($name, $_data, $_dataName);

                            if ($runTestInSeparateProcess) {
                                $_test->setRunTestInSeparateProcess(TRUE);

                                if ($preserveGlobalState !== NULL) {
                                    $_test->setPreserveGlobalState($preserveGlobalState);
                                }
                            }

                            if ($backupSettings['backupGlobals'] !== NULL) {
                                $_test->setBackupGlobals(
                                  $backupSettings['backupGlobals']
                                );
                            }

                            if ($backupSettings['backupStaticAttributes'] !== NULL) {
                                $_test->setBackupStaticAttributes(
                                  $backupSettings['backupStaticAttributes']
                                );
                            }

                            $test->addTest($_test, $groups);
                        }
                    }
                }

                else {
                    $test = new $className;
                }
            }
        }

        if (!isset($test)) {
            throw new PHPUnit_Framework_Exception('No valid test provided.');
        }

        if ($test instanceof PHPUnit_Framework_TestCase) {
            $test->setName($name);

            if ($runTestInSeparateProcess) {
                $test->setRunTestInSeparateProcess(TRUE);

                if ($preserveGlobalState !== NULL) {
                    $test->setPreserveGlobalState($preserveGlobalState);
                }
            }

            if ($backupSettings['backupGlobals'] !== NULL) {
                $test->setBackupGlobals($backupSettings['backupGlobals']);
            }

            if ($backupSettings['backupStaticAttributes'] !== NULL) {
                $test->setBackupStaticAttributes(
                  $backupSettings['backupStaticAttributes']
                );
            }
        }

        return $test;
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
     * Returns the name of the suite.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the test groups of the suite.
     *
     * @return array
     * @since  Method available since Release 3.2.0
     */
    public function getGroups()
    {
        return array_keys($this->groups);
    }

    /**
     * Runs the tests and collects their result in a TestResult.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @param  mixed                        $filter
     * @param  array                        $groups
     * @param  array                        $excludeGroups
     * @param  boolean                      $processIsolation
     * @return PHPUnit_Framework_TestResult
     * @throws PHPUnit_Framework_Exception
     */
    public function run(PHPUnit_Framework_TestResult $result = NULL, $filter = FALSE, array $groups = array(), array $excludeGroups = array(), $processIsolation = FALSE)
    {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        $result->startTestSuite($this);

        $doSetup = TRUE;

        if (!empty($excludeGroups)) {
            foreach ($this->groups as $_group => $_tests) {
                if (in_array($_group, $excludeGroups) &&
                    count($_tests) == count($this->tests)) {
                    $doSetup = FALSE;
                }
            }
        }

        if ($doSetup) {
            try {
                $this->setUp();

                if ($this->testCase &&
                    // Some extensions use test names that are not classes;
                    // The method_exists() triggers an autoload call that causes issues with die()ing autoloaders.
                    class_exists($this->name, false) &&
                    method_exists($this->name, 'setUpBeforeClass')) {
                    call_user_func(array($this->name, 'setUpBeforeClass'));
                }
            }

            catch (PHPUnit_Framework_SkippedTestSuiteError $e) {
                $numTests = count($this);

                for ($i = 0; $i < $numTests; $i++) {
                    $result->addFailure($this, $e, 0);
                }

                $result->endTestSuite($this);

                return $result;
            }

            catch (Exception $e) {
                $numTests = count($this);

                for ($i = 0; $i < $numTests; $i++) {
                    $result->addError($this, $e, 0);
                }

                $result->endTestSuite($this);

                return $result;
            }
        }

        if (empty($groups)) {
            $tests = $this->tests;
        } else {
            $tests = new SplObjectStorage;

            foreach ($groups as $group) {
                if (isset($this->groups[$group])) {
                    foreach ($this->groups[$group] as $test) {
                        $tests->attach($test);
                    }
                }
            }
        }

        foreach ($tests as $test) {
            if ($result->shouldStop()) {
                break;
            }

            if ($test instanceof PHPUnit_Framework_TestSuite) {
                $test->setBackupGlobals($this->backupGlobals);
                $test->setBackupStaticAttributes($this->backupStaticAttributes);

                $test->run(
                  $result, $filter, $groups, $excludeGroups, $processIsolation
                );
            } else {
                $runTest = TRUE;

                if ($filter !== FALSE ) {
                    $tmp = PHPUnit_Util_Test::describe($test, FALSE);

                    if ($tmp[0] != '') {
                        $name = join('::', $tmp);
                    } else {
                        $name = $tmp[1];
                    }

                    if (preg_match($filter, $name) == 0) {
                        $runTest = FALSE;
                    }
                }

                if ($runTest && !empty($excludeGroups)) {
                    foreach ($this->groups as $_group => $_tests) {
                        if (in_array($_group, $excludeGroups)) {
                            foreach ($_tests as $_test) {
                                if ($test === $_test) {
                                    $runTest = FALSE;
                                    break 2;
                                }
                            }
                        }
                    }
                }

                if ($runTest) {
                    if ($test instanceof PHPUnit_Framework_TestCase) {
                        $test->setBackupGlobals($this->backupGlobals);
                        $test->setBackupStaticAttributes(
                          $this->backupStaticAttributes
                        );
                        $test->setRunTestInSeparateProcess($processIsolation);
                    }

                    $this->runTest($test, $result);
                }
            }
        }

        if ($doSetup) {
            if ($this->testCase &&
                // Some extensions use test names that are not classes;
                // The method_exists() triggers an autoload call that causes issues with die()ing autoloaders.
                class_exists($this->name, false) &&
                method_exists($this->name, 'tearDownAfterClass')) {
                call_user_func(array($this->name, 'tearDownAfterClass'));
            }

            $this->tearDown();
        }

        $result->endTestSuite($this);

        return $result;
    }

    /**
     * Runs a test.
     *
     * @param  PHPUnit_Framework_Test        $test
     * @param  PHPUnit_Framework_TestResult  $result
     */
    public function runTest(PHPUnit_Framework_Test $test, PHPUnit_Framework_TestResult $result)
    {
        $test->run($result);
    }

    /**
     * Sets the name of the suite.
     *
     * @param  string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the test at the given index.
     *
     * @param  integer
     * @return PHPUnit_Framework_Test
     */
    public function testAt($index)
    {
        if (isset($this->tests[$index])) {
            return $this->tests[$index];
        } else {
            return FALSE;
        }
    }

    /**
     * Returns the tests as an enumeration.
     *
     * @return array
     */
    public function tests()
    {
        return $this->tests;
    }

    /**
     * Mark the test suite as skipped.
     *
     * @param  string  $message
     * @throws PHPUnit_Framework_SkippedTestSuiteError
     * @since  Method available since Release 3.0.0
     */
    public function markTestSuiteSkipped($message = '')
    {
        throw new PHPUnit_Framework_SkippedTestSuiteError($message);
    }

    /**
     * @param ReflectionClass  $class
     * @param ReflectionMethod $method
     */
    protected function addTestMethod(ReflectionClass $class, ReflectionMethod $method)
    {
        $name = $method->getName();

        if ($this->isPublicTestMethod($method)) {
            $test = self::createTest($class, $name);

            if ($test instanceof PHPUnit_Framework_TestCase ||
                $test instanceof PHPUnit_Framework_TestSuite_DataProvider) {
                $test->setDependencies(
                  PHPUnit_Util_Test::getDependencies($class->getName(), $name)
                );
            }

            $this->addTest($test, PHPUnit_Util_Test::getGroups(
              $class->getName(), $name)
            );
        }

        else if ($this->isTestMethod($method)) {
            $this->addTest(
              self::warning(
                sprintf(
                  'Test method "%s" in test class "%s" is not public.',
                  $name,
                  $class->getName()
                )
              )
            );
        }
    }

    /**
     * @param  ReflectionMethod $method
     * @return boolean
     */
    public static function isPublicTestMethod(ReflectionMethod $method)
    {
        return (self::isTestMethod($method) && $method->isPublic());
    }

    /**
     * @param  ReflectionMethod $method
     * @return boolean
     */
    public static function isTestMethod(ReflectionMethod $method)
    {
        if (strpos($method->name, 'test') === 0) {
            return TRUE;
        }

        // @scenario on TestCase::testMethod()
        // @test     on TestCase::testMethod()
        return strpos($method->getDocComment(), '@test')     !== FALSE ||
               strpos($method->getDocComment(), '@scenario') !== FALSE;
    }

    /**
     * @param  string  $message
     * @return PHPUnit_Framework_Warning
     */
    protected static function warning($message)
    {
        return new PHPUnit_Framework_Warning($message);
    }

    /**
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
     * Returns an iterator for this test suite.
     *
     * @return RecursiveIteratorIterator
     * @since  Method available since Release 3.1.0
     */
    public function getIterator()
    {
        return new RecursiveIteratorIterator(
          new PHPUnit_Util_TestSuiteIterator($this)
        );
    }

    /**
     * Template Method that is called before the tests
     * of this test suite are run.
     *
     * @since  Method available since Release 3.1.0
     */
    protected function setUp()
    {
    }

    /**
     * Template Method that is called after the tests
     * of this test suite have finished running.
     *
     * @since  Method available since Release 3.1.0
     */
    protected function tearDown()
    {
    }
}
