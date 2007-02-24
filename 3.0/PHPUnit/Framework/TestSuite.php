<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Runner/BaseTestRunner.php';
require_once 'PHPUnit/Util/Fileloader.php';
require_once 'PHPUnit/Util/Filter.php';

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
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Framework_TestSuite implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing
{
    /**
     * The name of the test suite.
     *
     * @var    string
     * @access private
     */
    private $name = '';

    /**
     * The tests in the test suite.
     *
     * @var    array
     * @access private
     */
    private $tests = array();

    /**
     * The number of tests in the test suite.
     *
     * @var    integer
     * @access private
     */
    private $numTests = -1;

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
     * @access public
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

        PHPUnit_Util_Filter::addFileToFilter(
          realpath($theClass->getFilename()),
          'TESTS'
        );

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
            $this->addTestMethod($method, $names, $theClass);
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
     * @access public
     */
    public function toString()
    {
        return $this->getName();
    }

    /**
     * Adds a test to the suite.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
     */
    public function addTest(PHPUnit_Framework_Test $test)
    {
        $this->tests[]  = $test;
        $this->numTests = -1;
    }

    /**
     * Adds the tests from the given class to the suite.
     *
     * @param  mixed $testClass
     * @throws InvalidArgumentException
     * @access public
     */
    public function addTestSuite($testClass)
    {
        if (is_string($testClass) && class_exists($testClass, FALSE)) {
            $testClass = new ReflectionClass($testClass);
        }

        if (!is_object($testClass)) {
            throw new InvalidArgumentException;
        }

        if ($testClass instanceof PHPUnit_Framework_TestSuite) {
            $this->addTest($testClass);
        }

        else if ($testClass instanceof ReflectionClass) {
            $this->addTest(new PHPUnit_Framework_TestSuite($testClass));
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
     * @throws InvalidArgumentException
     * @access public
     * @since  Method available since Release 2.3.0
     * @author Stefano F. Rausch <stefano@rausch-e.net>
     */
    public function addTestFile($filename)
    {
        if (!is_string($filename)) {
            throw new InvalidArgumentException;
        }

        if (!file_exists($filename)) {
            $includePaths = PHPUnit_Util_Fileloader::getIncludePaths();

            foreach ($includePaths as $includePath) {
                $file = $includePath . DIRECTORY_SEPARATOR . $filename;

                if (file_exists($file)) {
                    $filename = $file;
                    break;
                }
            }
        }

        $declaredClasses = get_declared_classes();

        PHPUnit_Util_Fileloader::checkAndLoad($filename);

        $newClasses = array_values(
          array_diff(get_declared_classes(), $declaredClasses)
        );

        $testsFound = FALSE;

        foreach ($newClasses as $className) {
            $class = new ReflectionClass($className);

            if (!$class->isAbstract()) {
                if ($class->hasMethod(PHPUnit_Runner_BaseTestRunner::SUITE_METHODNAME)) {
                    $method = $class->getMethod(
                      PHPUnit_Runner_BaseTestRunner::SUITE_METHODNAME
                    );

                    if ($method->isStatic()) {
                        $this->addTest($method->invoke(NULL));

                        $testsFound = TRUE;
                    }
                }

                else if ($class->implementsInterface('PHPUnit_Framework_Test')) {
                    $this->addTestSuite($class);

                    $testsFound = TRUE;
                }
            }
        }

        if (!$testsFound) {
            $this->addTest(
              new PHPUnit_Framework_Warning(
                'No tests found in file "' . $filename . '".'
              )
            );
        }

        $this->numTests = -1;
    }

    /**
     * Wrapper for addTestFile() that adds multiple test files.
     *
     * @param  array $filenames
     * @throws InvalidArgumentException
     * @access public
     * @since  Method available since Release 2.3.0
     */
    public function addTestFiles(array $filenames)
    {
        foreach ($filenames as $filename) {
            $this->addTestFile($filename);
        }
    }

    /**
     * Counts the number of test cases that will be run by this test.
     *
     * @return integer
     * @access public
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
     * @access public
     * @static
     */
    public static function createTest(ReflectionClass $theClass, $name)
    {
        if (!$theClass->isInstantiable()) {
            return self::warning(
              sprintf(
                'Cannot instantiate class "%s".',
                $theClass->getName()
              )
            );
        }

        $constructor = $theClass->getConstructor();

        if ($constructor !== NULL) {
            $parameters = $constructor->getParameters();

            if (count($parameters) == 0) {
                $test = $theClass->newInstance();

                if ($test instanceof PHPUnit_Framework_TestCase) {
                    $test->setName($name);
                }
            }

            else if (count($parameters) == 1 &&
                     $parameters[0]->getClass() === NULL) {
                $test = $theClass->newInstance($name);
            }

            else {
                return self::warning(
                  sprintf(
                    'Constructor of class "%s" is not TestCase($name) or TestCase().',
                    $theClass->getName()
                  )
                );
            }
        }

        return $test;
    }

    /**
     * Creates a default TestResult object.
     *
     * @return PHPUnit_Framework_TestResult
     * @access protected
     */
    protected function createResult()
    {
        return new PHPUnit_Framework_TestResult;
    }

    /**
     * Returns the name of the suite.
     *
     * @return string
     * @access public
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Runs the tests and collects their result in a TestResult.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @param  mixed                        $filter
     * @return PHPUnit_Framework_TestResult
     * @throws InvalidArgumentException
     * @access public
     */
    public function run(PHPUnit_Framework_TestResult $result = NULL, $filter = FALSE)
    {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        $result->startTestSuite($this);

        foreach ($this->tests as $test) {
            if ($result->shouldStop()) {
                break;
            }

            if ($test instanceof PHPUnit_Framework_TestSuite) {
                $test->run($result, $filter);
            } else {
                $runTest = TRUE;

                if ($filter !== FALSE ) {
                    $name = $test->getName();

                    if ($name !== NULL && preg_match($filter, $name) == 0) {
                        $runTest = FALSE;
                    }
                }

                if ($runTest) {
                    $this->runTest($test, $result);
                }
            }
        }

        $result->endTestSuite($this);

        return $result;
    }

    /**
     * Runs a test.
     *
     * @param  PHPUnit_Framework_Test        $test
     * @param  PHPUnit_Framework_TestResult  $testResult
     * @access public
     */
    public function runTest(PHPUnit_Framework_Test $test, PHPUnit_Framework_TestResult $result)
    {
        $test->run($result);
    }

    /**
     * Sets the name of the suite.
     *
     * @param  string
     * @access public
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
     * @access public
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
     * Returns the number of tests in this suite.
     *
     * @return integer
     * @access public
     */
    public function testCount()
    {
        return count($this->tests);
    }

    /**
     * Returns the tests as an enumeration.
     *
     * @return array
     * @access public
     */
    public function tests()
    {
        return $this->tests;
    }

    /**
     * @param  ReflectionMethod $method
     * @param  array            $names
     * @param  ReflectionClass  $theClass
     * @access private
     */
    private function addTestMethod(ReflectionMethod $method, Array &$names, ReflectionClass $theClass)
    {
        $name = $method->getName();

        if (in_array($name, $names)) {
            return;
        }

        if ($this->isPublicTestMethod($method)) {
            $names[] = $name;

            $this->addTest(
              self::createTest(
                $theClass,
                $name
              )
            );
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
     * @access private
     */
    private function isPublicTestMethod(ReflectionMethod $method)
    {
        return ($this->isTestMethod($method) &&
                $method->isPublic());
    }

    /**
     * @param  ReflectionMethod $method
     * @return boolean
     * @access private
     */
    private function isTestMethod(ReflectionMethod $method)
    {
        if (substr($method->name, 0, 4) == 'test') {
            return TRUE;
        }

        return strpos($method->getDocComment(), '@test') !== FALSE;
    }

    /**
     * @param  string  $message
     * @return PHPUnit_Framework_Warning
     * @access private
     */
    private static function warning($message)
    {
        return new PHPUnit_Framework_Warning($message);
    }
}

}
?>
