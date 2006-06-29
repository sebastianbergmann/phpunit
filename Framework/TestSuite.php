<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: TestSuite.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestResult.php';

/**
 * A TestSuite is a composite of Tests. It runs a collection of test cases.
 *
 * Here is an example using the dynamic test definition.
 *
 * <code>
 * <?php
 * $suite = new PHPUnit2_Framework_TestSuite;
 * $suite->addTest(new MathTest('testPass'));
 * ?>
 * </code>
 *
 * Alternatively, a TestSuite can extract the tests to be run automatically.
 * To do so you pass a ReflectionClass instance for your
 * PHPUnit2_Framework_TestCase class to the PHPUnit2_Framework_TestSuite
 * constructor.
 *
 * <code>
 * <?php
 * $suite = new PHPUnit2_Framework_TestSuite(
 *   new ReflectionClass('MathTest')
 * );
 * ?>
 * </code>
 *
 * This constructor creates a suite with all the methods starting with
 * "test" that take no arguments.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Framework
 */
class PHPUnit2_Framework_TestSuite implements PHPUnit2_Framework_Test {
    // {{{ Instance Variables

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

    // }}}
    // {{{ public function __construct($theClass = '', $name = '')

    /**
    * Constructs a new TestSuite:
    *
    *   - PHPUnit2_Framework_TestSuite() constructs an empty TestSuite.
    *
    *   - PHPUnit2_Framework_TestSuite(ReflectionClass) constructs a
    *     TestSuite from the given class.
    *
    *   - PHPUnit2_Framework_TestSuite(ReflectionClass, String)
    *     constructs a TestSuite from the given class with the given
    *     name.
    *
    *   - PHPUnit2_Framework_TestSuite(String) either constructs a
    *     TestSuite from the given class (if the passed string is the
    *     name of an existing class) or constructs an empty TestSuite
    *     with the given name.
    *
    * @param  mixed  $theClass
    * @param  string $name
    * @access public
    */
    public function __construct($theClass = '', $name = '') {
        if (is_string($theClass)) {
            if (class_exists($theClass)) {
                if ($name == '') {
                    $name = $theClass;
                }

                $theClass = new ReflectionClass($theClass);
            } else {
                $this->setName($theClass);

                return;
            }
        }

        if ($name != '') {
            $this->setName($name);
        } else {
            $this->setName($theClass->getName());
        }

        $constructor = $theClass->getConstructor();

        if ($constructor === NULL ||
            !$constructor->isPublic()) {
            $this->addTest(
              self::warning(
                sprintf(
                  'Class %s has no public constructor',

                  $theClass->getName()
                )
              )
            );

            return;
        }

        $methods = $theClass->getMethods();
        $names   = array();

        foreach ($methods as $method) {
            $this->addTestMethod($method, $names, $theClass);
        }

        if (empty($this->tests)) {
            $this->addTest(
              self::warning(
                sprintf(
                  'No tests found in %s',

                  $theClass->getName()
                )
              )
            );
        }
    }

    // }}}
    // {{{ public function toString()

    /**
    * Returns a string representation of the test suite.
    *
    * @return string
    * @access public
    */
    public function toString() {
        return $this->getName();
    }

    // }}}
    // {{{ public function addTest(PHPUnit2_Framework_Test $test)

    /**
    * Adds a test to the suite.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @access public
    */
    public function addTest(PHPUnit2_Framework_Test $test) {
        $this->tests[] = $test;
    }

    // }}}
    // {{{ public function addTestSuite($testClass)

    /**
    * Adds the tests from the given class to the suite.
    *
    * @param  mixed $testClass
    * @access public
    */
    public function addTestSuite($testClass) {
        if (is_string($testClass) &&
            class_exists($testClass)) {
            $testClass = new ReflectionClass($testClass);
        }

        if (is_object($testClass) &&
            $testClass instanceof ReflectionClass) {
            $this->addTest(new PHPUnit2_Framework_TestSuite($testClass));
        }
    }

    // }}}
    // {{{ public function countTestCases()

    /**
    * Counts the number of test cases that will be run by this test.
    *
    * @return integer
    * @access public
    */
    public function countTestCases() {
        $count = 0;

        foreach ($this->tests as $test) {
            $count += $test->countTestCases();
        }

        return $count;
    }

    // }}}
    // {{{ public static function createTest(ReflectionClass $theClass, $name)

    /**
    * @param  ReflectionClass $theClass
    * @param  string          $name
    * @return PHPUnit2_Framework_Test
    * @access public
    * @static
    */
    public static function createTest(ReflectionClass $theClass, $name) {
        if (!$theClass->isInstantiable()) {
            return self::warning(
              sprintf(
                'Cannot instantiate test case %s.',
                $theClass->getName()
              )
            );
        }

        $constructor = $theClass->getConstructor();

        if ($constructor !== NULL) {
            $parameters = $constructor->getParameters();

            if (sizeof($parameters) == 0) {
                $test = $theClass->newInstance();

                if ($test instanceof PHPUnit2_Framework_TestCase) {
                    $test->setName($name);
                }
            }

            else if (sizeof($parameters) == 1 &&
                     $parameters[0]->getClass() === NULL) {
                $test = $theClass->newInstance($name);
            }

            else {
                return self::warning(
                  sprintf(
                    'Constructor of class %s is not TestCase($name) or TestCase().',
                    $theClass->getName()
                  )
                );
            }
        }

        return $test;
    }

    // }}}
    // {{{ protected function createResult()

    /**
    * Creates a default TestResult object.
    *
    * @return PHPUnit2_Framework_TestResult
    * @access protected
    */
    protected function createResult() {
        return new PHPUnit2_Framework_TestResult;
    }

    // }}}
    // {{{ public function getName()

    /**
    * Returns the name of the suite.
    *
    * @return string
    * @access public
    */
    public function getName() {
        return $this->name;
    }

    // }}}
    // {{{ public function run($result = NULL)

    /**
    * Runs the tests and collects their result in a TestResult.
    *
    * @param  PHPUnit2_Framework_TestResult $result
    * @return PHPUnit2_Framework_TestResult
    * @access public
    */
    public function run($result = NULL) {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        // XXX: Workaround for missing ability to declare type-hinted parameters as optional.
        else if (!($result instanceof PHPUnit2_Framework_TestResult)) {
            throw new Exception(
              'Argument 1 must be an instance of PHPUnit2_Framework_TestResult.'
            );
        }

        $result->startTestSuite($this);

        foreach ($this->tests as $test) {
            if ($result->shouldStop()) {
                break;
            }

            $this->runTest($test, $result);
        }

        $result->endTestSuite($this);

        return $result;
    }

    // }}}
    // {{{ public function runTest(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_TestResult $result)

    /**
    * Runs a test.
    *
    * @param  PHPUnit2_Framework_Test        $test
    * @param  PHPUnit2_Framework_TestResult  $testResult
    * @access public
    */
    public function runTest(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_TestResult $result) {
        $test->run($result);
    }

    // }}}
    // {{{ public function setName($name)

    /**
    * Sets the name of the suite.
    *
    * @param  string
    * @access public
    */
    public function setName($name) {
        $this->name = $name;
    }

    // }}}
    // {{{ public function testAt($index)

    /**
    * Returns the test at the given index.
    *
    * @param  integer
    * @return PHPUnit2_Framework_Test
    * @access public
    */
    public function testAt($index) {
        if (isset($this->tests[$index])) {
            return $this->tests[$index];
        } else {
            return FALSE;
        }
    }

    // }}}
    // {{{ public function testCount()

    /**
    * Returns the number of tests in this suite.
    *
    * @return integer
    * @access public
    */
    public function testCount() {
        return sizeof($this->tests);
    }

    // }}}
    // {{{ public function tests()

    /**
    * Returns the tests as an enumeration.
    *
    * @return array
    * @access public
    */
    public function tests() {
        return $this->tests;
    }

    // }}}
    // {{{ public function addTestMethod(ReflectionMethod $method, &$names, ReflectionClass $theClass)

    /**
    * @param  ReflectionMethod $method
    * @param  array            $names
    * @param  ReflectionClass  $theClass
    * @access private
    */
    private function addTestMethod(ReflectionMethod $method, &$names, ReflectionClass $theClass) {
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
                  'Test method is not public: %s',

                  $name
                )
              )
            );
        }
    }

    // }}}
    // {{{ private function isPublicTestMethod(ReflectionMethod $method)

    /**
    * @param  ReflectionMethod $method
    * @return boolean
    * @access private
    */
    private function isPublicTestMethod(ReflectionMethod $method) {
        return ($this->isTestMethod($method) &&
                $method->isPublic());
    }

    // }}}
    // {{{ private function isTestMethod(ReflectionMethod $method)

    /**
    * @param  ReflectionMethod $method
    * @return boolean
    * @access private
    */
    private function isTestMethod(ReflectionMethod $method) {
        return (substr($method->name, 0, 4) == 'test');
    }

    // }}}
    // {{{ private static function warning($message)

    /**
    * @param  string  $message
    * @return PHPUnit2_Framework_Warning
    * @access private
    */
    private static function warning($message) {
        require_once 'PHPUnit2/Framework/Warning.php';
        return new PHPUnit2_Framework_Warning($message);
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
