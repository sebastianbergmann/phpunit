<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Runner/BaseTestRunner.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/Fileloader.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/InvalidArgumentHelper.php';
require_once 'PHPUnit/Util/Test.php';
require_once 'PHPUnit/Util/TestSuiteIterator.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

if (!class_exists('PHPUnit_Framework_TestSuite', FALSE)) {

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
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
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
     * Whether or not the tests of this test suite are
     * to be run in separate PHP processes.
     *
     * @var    boolean
     */
    protected $runTestsInSeparateProcesses = NULL;

    /**
     * Fixture that is shared between the tests of this test suite.
     *
     * @var    mixed
     */
    protected $sharedFixture;

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
     * @var array
     */
    protected static $setUpBeforeClassCalled = array();

    /**
     * @var array
     */
    protected static $tearDownAfterClassCalled = array();

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

        else if (is_string($theClass) && $theClass !== ''
                 && class_exists($theClass, FALSE)) {
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

        $filename = $theClass->getFilename();

        if (strpos($filename, 'eval()') === FALSE) {
            PHPUnit_Util_Filter::addFileToFilter(realpath($filename), 'TESTS');
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

        $names = array();

        foreach ($theClass->getMethods() as $method) {
            if (strpos($method->getDeclaringClass()->getName(), 'PHPUnit_') !== 0) {
                $this->addTestMethod($theClass, $method, $names);
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

            if ($test instanceof PHPUnit_Framework_TestSuite && empty($groups)) {
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
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'class name or object');
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
                        $this->addTest($method->invoke(NULL, $testClass->getName()));
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
     * @param  boolean $syntaxCheck
     * @param  array   $phptOptions Array with ini settings for the php instance
     *                              run, key being the name if the setting,
     *                              value the ini value.
     * @throws InvalidArgumentException
     * @since  Method available since Release 2.3.0
     * @author Stefano F. Rausch <stefano@rausch-e.net>
     */
    public function addTestFile($filename, $syntaxCheck = FALSE, $phptOptions = array())
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (file_exists($filename) && substr($filename, -5) == '.phpt') {
            require_once 'PHPUnit/Extensions/PhptTestCase.php';

            $this->addTest(
              new PHPUnit_Extensions_PhptTestCase($filename, $phptOptions)
            );

            return;
        }

        if (!file_exists($filename)) {
            $includePaths = explode(PATH_SEPARATOR, get_include_path());

            foreach ($includePaths as $includePath) {
                $file = $includePath . DIRECTORY_SEPARATOR . $filename;

                if (file_exists($file)) {
                    $filename = $file;
                    break;
                }
            }
        }

        PHPUnit_Util_Class::collectStart();
        PHPUnit_Util_Fileloader::checkAndLoad($filename, $syntaxCheck);
        $newClasses = PHPUnit_Util_Class::collectEnd();
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
    public function addTestFiles($filenames, $syntaxCheck = FALSE)
    {
        if (!(is_array($filenames) ||
             (is_object($filenames) && $filenames instanceof Iterator))) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'array or iterator');
        }

        foreach ($filenames as $filename) {
            $this->addTestFile((string)$filename, $syntaxCheck);
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
     * @param  array           $classGroups
     * @return PHPUnit_Framework_Test
     */
    public static function createTest(ReflectionClass $theClass, $name, array $classGroups = array())
    {
        $className                = $theClass->getName();
        $classDocComment          = $theClass->getDocComment();
        $method                   = new ReflectionMethod($className, $name);
        $methodDocComment         = $method->getDocComment();
        $runTestInSeparateProcess = FALSE;
        $backupSettings           = PHPUnit_Util_Test::getBackupSettings($className, $name);

        if (!$theClass->isInstantiable()) {
            return self::warning(
              sprintf('Cannot instantiate class "%s".', $className)
            );
        }

        $constructor = $theClass->getConstructor();

        // @runTestsInSeparateProcesses on TestCase
        // @runInSeparateProcess        on TestCase::testMethod()
        if (strpos($classDocComment, '@runTestsInSeparateProcesses') !== FALSE ||
            strpos($methodDocComment, '@runInSeparateProcess') !== FALSE) {
            $runTestInSeparateProcess = TRUE;
        }

        if ($constructor !== NULL) {
            $parameters = $constructor->getParameters();

            // TestCase() or TestCase($name)
            if (count($parameters) < 2) {
                $test = new $className;
            }

            // TestCase($name, $data)
            else {
                $data = PHPUnit_Util_Test::getProvidedData($className, $name);

                if (is_array($data) || $data instanceof Iterator) {
                    $test = new PHPUnit_Framework_TestSuite(
                      $className . '::' . $name
                    );

                    foreach ($data as $_dataName => $_data) {
                        $test->addTest(
                          new $className($name, $_data, $_dataName),
                          PHPUnit_Util_Test::getGroups($className, $name)
                        );

                        if ($runTestInSeparateProcess) {
                            $test->setRunTestInSeparateProcess(TRUE);
                        }

                        if ($backupSettings['backupGlobals'] !== NULL) {
                            $test->setBackupGlobals($backupSettings['backupGlobals']);
                        }

                        if ($backupSettings['backupStaticAttributes'] !== NULL) {
                            $test->setBackupStaticAttributes($backupSettings['backupStaticAttributes']);
                        }
                    }
                } else {
                    $test = new $className;
                }
            }
        }

        if ($test instanceof PHPUnit_Framework_TestCase) {
            $test->setName($name);

            if ($runTestInSeparateProcess) {
                $test->setRunTestInSeparateProcess(TRUE);
            }

            if ($backupSettings['backupGlobals'] !== NULL) {
                $test->setBackupGlobals($backupSettings['backupGlobals']);
            }

            if ($backupSettings['backupStaticAttributes'] !== NULL) {
                $test->setBackupStaticAttributes($backupSettings['backupStaticAttributes']);
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
     * @throws InvalidArgumentException
     */
    public function run(PHPUnit_Framework_TestResult $result = NULL, $filter = FALSE, array $groups = array(), array $excludeGroups = array(), $processIsolation = FALSE)
    {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        try {
            $this->setUp();
        }

        catch (PHPUnit_Framework_SkippedTestSuiteError $e) {
            $numTests = count($this);

            for ($i = 0; $i < $numTests; $i++) {
                $result->addFailure($this, $e, 0);
            }

            return $result;
        }

        $result->startTestSuite($this);

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

        $currentClass = '';

        foreach ($tests as $test) {
            if ($result->shouldStop()) {
                break;
            }

            if ($test instanceof PHPUnit_Framework_TestSuite) {
                $test->setBackupGlobals($this->backupGlobals);
                $test->setBackupStaticAttributes($this->backupStaticAttributes);
                $test->setSharedFixture($this->sharedFixture);

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
                        $test->setBackupStaticAttributes($this->backupStaticAttributes);
                        $test->setSharedFixture($this->sharedFixture);
                        $test->setRunTestInSeparateProcess($processIsolation);

                        $_currentClass = get_class($test);

                        if ($_currentClass != $currentClass) {
                            if ($currentClass != '') {
                                call_user_func(array($currentClass, 'tearDownAfterClass'));
                                self::$tearDownAfterClassCalled[$currentClass] = TRUE;
                            }

                            $currentClass = $_currentClass;
                        }

                        if (!isset(self::$setUpBeforeClassCalled[$currentClass])) {
                            call_user_func(array($currentClass, 'setUpBeforeClass'));
                            self::$setUpBeforeClassCalled[$currentClass] = TRUE;
                        }
                    }

                    $this->runTest($test, $result);
                }
            }
        }

        if ($currentClass != '' &&
            !isset(self::$tearDownAfterClassCalled[$currentClass])) {
            call_user_func(array($currentClass, 'tearDownAfterClass'));
        }

        $result->endTestSuite($this);
        $this->tearDown();

        return $result;
    }

    /**
     * Runs a test.
     *
     * @param  PHPUnit_Framework_Test        $test
     * @param  PHPUnit_Framework_TestResult  $testResult
     */
    public function runTest(PHPUnit_Framework_Test $test, PHPUnit_Framework_TestResult $result)
    {
        if ($this->runTestsInSeparateProcesses === TRUE && $test instanceof PHPUnit_Framework_TestCase) {
            $test->setRunTestInSeparateProcess(TRUE);
        }

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
     * @param  ReflectionClass  $class
     * @param  ReflectionMethod $method
     * @param  array            $names
     */
    protected function addTestMethod(ReflectionClass $class, ReflectionMethod $method, array &$names)
    {
        $name = $method->getName();

        if (in_array($name, $names)) {
            return;
        }

        if ($this->isPublicTestMethod($method)) {
            $names[] = $name;

            $test = self::createTest($class, $name);

            if (!$test instanceof PHPUnit_Framework_TestSuite) {
                $test->setDependencies(
                  PHPUnit_Util_Test::getDependencies($class->getName(), $name)
                );
            }

            $this->addTest($test, PHPUnit_Util_Test::getGroups($class->getName(), $name));
        }

        else if ($this->isTestMethod($method)) {
            $this->addTest(
              self::warning(
                sprintf(
                  'Test method "%s" is not public.',

                  $name
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
        if (is_null($this->backupStaticAttributes) && is_bool($backupStaticAttributes)) {
            $this->backupStaticAttributes = $backupStaticAttributes;
        }
    }

    /**
     * @param  boolean $runTestsInSeparateProcesses
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.4.0
     */
    public function setRunTestsInSeparateProcesses($runTestsInSeparateProcesses)
    {
        if (is_null($this->runTestsInSeparateProcesses) && is_bool($runTestsInSeparateProcesses)) {
            $this->runTestsInSeparateProcesses = $runTestsInSeparateProcesses;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Sets the shared fixture for the tests of this test suite.
     *
     * @param  mixed $sharedFixture
     * @since  Method available since Release 3.1.0
     */
    public function setSharedFixture($sharedFixture)
    {
        $this->sharedFixture = $sharedFixture;
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

}
?>
