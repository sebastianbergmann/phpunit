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
// $Id: TestCase.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/Assert.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Util/Filter.php';

/**
 * A TestCase defines the fixture to run multiple tests.
 *
 * To define a TestCase
 *
 *   1) Implement a subclass of PHPUnit2_Framework_TestCase.
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
 * require_once 'PHPUnit2/Framework/TestCase.php';
 *
 * class MathTest extends PHPUnit2_Framework_TestCase {
 *     public $value1;
 *     public $value2;
 *
 *     public function __construct($name) {
 *         parent::__construct($name);
 *     }
 *
 *     public function setUp() {
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
 * public function testPass() {
 *     $this->assertTrue($this->value1 + $this->value2 == 5);
 * }
 * ?>
 * </code>
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Framework
 * @abstract
 */
abstract class PHPUnit2_Framework_TestCase extends PHPUnit2_Framework_Assert implements PHPUnit2_Framework_Test {
    // {{{ Members

    /**
    * Code Coverage information provided by Xdebug.
    *
    * @var    array
    * @access private
    */
    private $codeCoverageInformation = array();

    /**
    * The name of the test case.
    *
    * @var    string
    * @access private
    */
    private $name = NULL;

    // }}}
    // {{{ public function __construct($name = NULL)

    /**
    * Constructs a test case with the given name.
    *
    * @param  string
    * @access public
    */
    public function __construct($name = NULL) {
        if ($name !== NULL) {
            $this->setName($name);
        }
    }

    // }}}
    // {{{ public function toString()

    /**
    * Returns a string representation of the test case.
    *
    * @return string
    * @access public
    */
    public function toString() {
        $class = new ReflectionClass($this);

        return sprintf(
          '%s(%s)',

          $this->getName(),
          $class->name
        );
    }

    // }}}
    // {{{ public function countTestCases()

    /**
    * Counts the number of test cases executed by run(TestResult result).
    *
    * @return integer
    * @access public
    */
    public function countTestCases() {
        return 1;
    }

    // }}}
    // {{{ public function getCodeCoverageInformation()

    /**
    * Returns the Code Coverage information provided by Xdebug.
    *
    * @return array
    * @access public
    */
    public function getCodeCoverageInformation() {
        return $this->codeCoverageInformation;
    }

    // }}}
    // {{{ public function getName()

    /**
    * Gets the name of a TestCase.
    *
    * @return string
    * @access public
    */
    public function getName() {
        return $this->name;
    }

    // }}}
    // {{{ public function run(PHPUnit2_Framework_TestResult)

    /**
    * Runs the test case and collects the results in a TestResult object.
    * If no TestResult object is passed a new one will be created.
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

        $result->run($this);

        return $result;
    }

    // }}}
    // {{{ public function runBare()

    /**
    * Runs the bare test sequence.
    *
    * @access public
    */
    public function runBare() {
        $catchedException = NULL;

        $this->setUp();

        try {
            $this->runTest();
        }

        catch (Exception $e) {
            $catchedException = $e;
        }

        $this->tearDown();

        // Workaround for missing "finally".
        if ($catchedException !== NULL) {
            throw $catchedException;
        }
    }

    // }}}
    // {{{ protected function runTest()

    /**
    * Override to run the test and assert its state.
    *
    * @access protected
    */
    protected function runTest() {
        self::assertNotNull($this->name);

        try {
            $class  = new ReflectionClass($this);
            $method = $class->getMethod($this->name);
        }

        catch (ReflectionException $e) {
            $this->fail($e->getMessage());
        }

        PHPUnit2_Util_Filter::addFileToFilter($class->getFileName());

        if (extension_loaded('xdebug')) {
            xdebug_start_code_coverage();
        }

        $method->invoke($this);

        if (extension_loaded('xdebug')) {
            $this->codeCoverageInformation = PHPUnit2_Util_Filter::getFilteredCodeCoverage(
              xdebug_get_code_coverage()
            );

            xdebug_stop_code_coverage();
        }
    }

    // }}}
    // {{{ public function setName($name)

    /**
    * Sets the name of a TestCase.
    *
    * @param  string
    * @access public
    */
    public function setName($name) {
        $this->name = $name;
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
    // {{{ protected function setUp()

    /**
    * Sets up the fixture, for example, open a network connection.
    * This method is called before a test is executed.
    *
    * @access protected
    */
    protected function setUp() {}

    // }}}
    // {{{ protected function tearDown()

    /**
    * Tears down the fixture, for example, close a network connection.
    * This method is called after a test is executed.
    *
    * @access protected
    */
    protected function tearDown() {}

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
