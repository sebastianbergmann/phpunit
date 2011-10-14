<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
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
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
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
     * The tests in the suite that are ready to run.
     *
     * @var    array
     */
    protected $preparedTests = array();

    /**
     * The tests in the suite that are running.
     * Only used during parallelism.
     *
     * @var    array
     */
    protected $runningTests = array();

    /**
     * The tests in the suite that have finished running.
     * Only used during parallelism.
     *
     * @var    array
     */
    protected $finishedTestPids = array();

    /**
     * The tests in the order in which they'll be reported
     * Only used during parallelism.
     *
     * @var    array
     */
    protected $reportOrderTestPids = array();

    /**
     * The subsuites in the test suite that are ready to run.
     *
     * @var    array
     */
    protected $preparedSubsuites = array();

    /**
     * The subsuites in the test suite that have started running tests.
     * Only used during parallelism.
     *
     * @var    array
     */
    protected $runningSubsuites = array();

    /**
     * The subsuites in the order in which they'll be reported
     * Only used during parallelism.
     *
     * @var    array
     */
    protected $reportOrderSubsuites = array();

    /**
     * Whether this suite has begun reporting its results
     * Only used during parallelism.
     *
     * @var    array
     */
    protected $reportStarted = FALSE;

    /**
     * Whether this suite has finished reporting its results
     * Only used during parallelism.
     *
     * @var    array
     */
    protected $reportFinished = FALSE;

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
     * @throws InvalidArgumentException
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
            throw new InvalidArgumentException;
        }

        if (!$theClass->isSubclassOf('PHPUnit_Framework_TestCase')) {
            throw new InvalidArgumentException(
              'Class does not extend PHPUnit_Framework_TestCase.'
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
            if (strpos($method->getDeclaringClass()->getName(), 'PHPUnit_') !== 0) {
                $this->addTestMethod($theClass, $method);
            }
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
     * @throws InvalidArgumentException
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
            throw new InvalidArgumentException;
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
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
     * @throws RuntimeException
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

                    if ($data instanceof PHPUnit_Framework_Warning) {
                        $test->addTest($data);
                    }

                    else {
                        $groups = PHPUnit_Util_Test::getGroups($className, $name);

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
            throw new RuntimeException('No valid test provided.');
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
     * Prepares the a set of tests and test suites for running
     * Defaults to preparing this suite's list of tests
     *
     * @param  array   $excludeGroups
     * @param  bool    $processIsolation
     * @param  string  $filter
     * @param  boolean $backupGlobals
     * @param  boolean $backupStaticAttributes
     * @param  array   $tests
     */
    public function prepareTests($excludeGroups, $processIsolation, $filter, $backupGlobals, $backupStaticAttributes, $tests = NULL)
    {
        if (is_null($tests)) {
            $tests = $this->tests;
        }
        $this->preparedTests = array();
        $this->runningTests = array();
        $this->finishedTestPids = array();
        $this->reportOrderTestPids = array();
        $this->preparedSubsuites = array();
        $this->runningSubsuites = array();
        $this->reportOrderSubsuites = array();
        $this->reportStarted = FALSE;
        $this->reportFinished = FALSE;
        foreach ($tests as $test) {
            $include = TRUE;
            if (!empty($excludeGroups)) {
                foreach ($this->groups as $_group => $_tests) {
                    if (in_array($_group, $excludeGroups)) {
                        foreach ($_tests as $_test) {
                            if ($test === $_test) {
                                $include = FALSE;
                                break 2;
                            }
                        }
                    }
                }
            }
            if ($include && !$this->isTestFilteredOut($test, $filter)) {
                if ($test instanceof PHPUnit_Framework_TestSuite) {
                    $this->preparedSubsuites[] = $test;
                    $test->prepareTests($excludeGroups, $processIsolation, $filter, $backupGlobals, $backupStaticAttributes);
                } else {
                    if ($test instanceof PHPUnit_Framework_TestCase) {
                        $test->setBackupGlobals($backupGlobals);
                        $test->setBackupStaticAttributes($backupStaticAttributes);
                        $test->setRunTestInSeparateProcess($processIsolation);
                    }
                    $this->preparedTests[] = $test;
                }
            }
        }
    }


    
    /**
     * Runs the tests and collects their result in a TestResult.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @param  mixed                        $filter
     * @param  array                        $groups
     * @param  array                        $excludeGroups
     * @param  boolean                      $processIsolation
     * @param  int                          $parallelism
     * @return PHPUnit_Framework_TestResult
     * @throws InvalidArgumentException
     */
    public function run(PHPUnit_Framework_TestResult $result = NULL, $filter = FALSE, array $groups = array(), array $excludeGroups = array(), $processIsolation = FALSE, $parallelism = 1)
    {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        if (empty($groups)) {
            $this->prepareTests($excludeGroups, $processIsolation, $filter, $this->backupGlobals, $this->backupStaticAttributes);
        } else {
            $group_tests = new SplObjectStorage;

            foreach ($groups as $group) {
                if (isset($this->groups[$group])) {
                    foreach ($this->groups[$group] as $test) {
                        $group_tests->attach($test);
                    }
                }
            }
            $this->prepareTests($excludeGroups, $processIsolation, $filter, $this->backupGlobals, $this->backupStaticAttributes, $group_tests);
        }

        if ($parallelism == 1) {
            $result = $this->runTestsSerial($result);
        } else {
            $result = $this->runTestsParallel($result, $parallelism);
        }

        return $result;
    }

    /**
     * Given a test (not necessarily a Phpunit_Framework_TestCase),
     * decide what its name is (like, suitename::casename)
     *
     * @param  PHPUnit_Framework_Test        $test
     * @return string
     */
    public static function guessTestName($test) {
        $tmp = PHPUnit_Util_Test::describe($test, FALSE);

        if ($tmp[0] != '') {
            return join('::', $tmp);
        } else {
            return $tmp[1];
        }
    }
    
    
    /**
     * Given a test and a text filter, says whether the filter
     * should block the test from being run
     *
     * @param  PHPUnit_Framework_Test        $test
     * @param  string                        $filter
     * @return boolean
     */
    public function isTestFilteredOut($test, $filter)
    {
        if ($filter !== FALSE ) {
            $name = PHPUnit_Framework_TestSuite::guessTestName($test);

            if (preg_match($filter, $name) == 0) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Runs my prepared tests in serial, recursing through subsuites.
     *
     * @param  PHPUnit_Framework_TestResult $testResult
     */
    public function runTestsSerial(PHPUnit_Framework_TestResult $result)
    {
        $result->startTestSuite($this);

        $doSetup = (!empty($this->preparedSubsuites) && !empty($this->preparedTests));

        if ($doSetup) {
            try {
                $this->setUp();

                if ($this->testCase &&
                    method_exists($this->name, 'setUpBeforeClass')) {
                    call_user_func(array($this->name, 'setUpBeforeClass'));
                }
            }

            catch (PHPUnit_Framework_SkippedTestSuiteError $e) {
                $numTests = count($this);

                for ($i = 0; $i < $numTests; $i++) {
                    $result->addFailure($this, $e, 0);
                }

                return $result;
            }

            catch (Exception $e) {
                $numTests = count($this);

                for ($i = 0; $i < $numTests; $i++) {
                    $result->addError($this, $e, 0);
                }

                return $result;
            }
        }
        $result->startTestSuite($this);

        foreach ($this->preparedSubsuites as $suite) {
            $result = $suite->runTestsSerial($result);
        }
        foreach ($this->preparedTests as $test) {
            if ($result->shouldStop()) {
                break;
            }
            $this->runTest($test, $result);
        }

        if ($doSetup) {
            if ($this->testCase &&
                method_exists($this->name, 'tearDownAfterClass')) {
                call_user_func(array($this->name, 'tearDownAfterClass'));
            }

            $this->tearDown();
        }
        
        $result->endTestSuite($this);
        return $result;
    }

    /**
     * Starts the next test I have prepared. Can get tests from subsuites.
     * Returns the suite the test came from, along with the test, and the pid.
     * @param  PHPUnit_Framework_TestResult $result
     * @param  PHPUnit_Util_PHP             $php
     * @return array
     */
    public function startNextPreparedTest(PHPUnit_Framework_TestResult $result, PHPUnit_Util_PHP $php)
    {
        $test = NULL;
        $tests_suite = NULL;
        foreach ($this->runningSubsuites as $i => $suite) {
            if ($suite->hasTestsPrepared()) {
                list($tests_suite, $test) = $suite->startNextPreparedTest($result, $php);
                break;
            }
        }
        if (is_null($test) && !empty($this->preparedSubsuites)) {
            $suite = array_shift($this->preparedSubsuites);
            $this->reportOrderSubsuites[] = $suite;
            if ($suite->hasTestsPrepared()) {
                list($tests_suite, $test) = $suite->startNextPreparedTest($result, $php);
                $this->runningSubsuites[] = $suite;
            }
        }
        if (is_null($test) && !empty($this->preparedTests)) {
            $test = array_shift($this->preparedTests);
            $tests_suite = $this;
            $pid = $test->startInAnotherProcess($result, $php);
            $this->reportOrderTestPids[] = $pid;
            $this->runningTests[$pid] = $test;
        }
        return array($tests_suite, $test);
    }

    /**
     * Finishes any running test that has completed, checking in the order in which
     * they were started. Return pid of finished test, or -1 if no test finished.
     * @param  PHPUnit_Framework_TestResult $result
     * @param  PHPUnit_Util_PHP             $php
     * @return int
     */
    public function tryToFinishARunningTest(PHPUnit_Framework_TestResult $result, PHPUnit_Util_PHP $php)
    {
        $finished_pid = -1;
        foreach ($this->runningSubsuites as $i => $suite) {
            $finished_pid = $suite->tryToFinishARunningTest($result, $php);
            if ($finished_pid > 0) {
                if ($suite->countRunning() == 0 && !$suite->hasTestsPrepared()) {
                    unset($this->runningSubsuites[$i]);
                }
                break;
            }
        }
        if ($finished_pid < 0) {
            foreach ($this->runningTests as $pid => $test) {
                if ($php->isJobFinished($pid)) {
                    $finished_pid = $pid;
                    $php->finishJob($finished_pid);
                    
                    unset($this->runningTests[$finished_pid]);
                    $this->finishedTestPids[] = $finished_pid;
                    break;
                }
            }
        }
        return $finished_pid;
    }

    /**
     * Tells whether I have any tests prepared to run
     * @return boolean
     */
    public function hasTestsPrepared()
    {
        if (!empty($this->preparedTests)) {
            return TRUE;
        }
        if (empty($this->preparedSubsuites) && empty($this->runningSubsuites)) {
            return FALSE;
        }
        foreach ($this->preparedSubsuites as $suite) {
            if ($suite->hasTestsPrepared()) {
                return TRUE;
            }
        }
        foreach ($this->runningSubsuites as $suite) {
            if ($suite->hasTestsPrepared()) {
                return TRUE;
            }
        }
        return FALSE;
    }
    


    /**
     * Recursively says how many tests are running under this suite
     * @return int
     */
    public function countRunning()
    {
        $count = count($this->runningTests);
        foreach ($this->runningSubsuites as $suite) {
            $count += $suite->countRunning();
        }
        return $count;
    }

    /**
     * Runs my prepared tests in parallel.
     *
     * @param  PHPUnit_Framework_TestResult  $testResult
     * @param  int                           $parallelism
     */
    public function runTestsParallel(PHPUnit_Framework_TestResult $result, $parallelism)
    {
        $php = PHPUnit_Util_PHP::factory($result);
        while ($this->hasTestsPrepared() || $this->countRunning() > 0) {
            while ($this->countRunning() < $parallelism && $this->hasTestsPrepared()) {
                $this->startNextPreparedTest($result, $php);
            }
            while ($this->countRunning() == $parallelism || 
                    (!$this->hasTestsPrepared() && $this->countRunning() > 0)) {
                $finished_pid = $this->tryToFinishARunningTest($result, $php);
                if ($finished_pid > 0) {
                    $this->report($result, $php);
                }
                usleep(10000);
            }
        }
    }
    

    /**
     * Reports any finished tests or suites to the $result.
     * Safe to call any time during a parallel run. Will report
     * start/end events in the same order as if they'd been run in
     * serial, regardless of the order in which they finish.
     * Returns whether the suite is finished reporting.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @param  PHPUnit_Util_PHP             $php
     * @return boolean
     */
    public function report(PHPUnit_Framework_TestResult $result, PHPUnit_Util_PHP $php)
    {
        if (!$this->reportStarted) {
            $this->reportStarted = TRUE;
            $result->startTestSuite($this);
        }
        while (!empty($this->reportOrderSubsuites)) {
            $suite = array_shift($this->reportOrderSubsuites);
            $done = $suite->report($result, $php);
            if (!$done) {
                array_unshift($this->reportOrderSubsuites, $suite);
                return FALSE;
            }
        }
        while (!empty($this->reportOrderTestPids)) {
            $pid = array_shift($this->reportOrderTestPids);
            if (in_array($pid, $this->finishedTestPids)) {
                $php->reportJobStarted($pid);
                $php->reportJobFinished($pid);
            } else {
                array_unshift($this->reportOrderTestPids, $pid);
                return FALSE;
            }
        }
                
        if (!$this->reportFinished) {
            $this->reportFinished = TRUE;
            $result->endTestSuite($this);
        }
        return TRUE;
    }

    /**
     * Runs a test.
     *
     * @param  PHPUnit_Framework_Test        $test
     * @param  PHPUnit_Framework_TestResult  $testResult
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
