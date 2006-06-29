<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: TestCase.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/Assert.php';
require_once 'PHPUnit2/Framework/Error.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestResult.php';

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
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 * @abstract
 */
abstract class PHPUnit2_Framework_TestCase extends PHPUnit2_Framework_Assert implements PHPUnit2_Framework_Test {
    /**
     * The name of the test case.
     *
     * @var    string
     * @access private
     */
    private $name = NULL;

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

    /**
     * Counts the number of test cases executed by run(TestResult result).
     *
     * @return integer
     * @access public
     */
    public function countTestCases() {
        return 1;
    }

    /**
     * Gets the name of a TestCase.
     *
     * @return string
     * @access public
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Runs the test case and collects the results in a TestResult object.
     * If no TestResult object is passed a new one will be created.
     *
     * @param  PHPUnit2_Framework_TestResult $result
     * @return PHPUnit2_Framework_TestResult
     * @throws Exception
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

    /**
     * Override to run the test and assert its state.
     *
     * @throws PHPUnit2_Framework_Error
     * @access protected
     */
    protected function runTest() {
        if ($this->name === NULL) {
            throw new PHPUnit2_Framework_Error(
              'PHPUnit2_Framework_TestCase::$name must not be NULL.'
            );
        }

        try {
            $class  = new ReflectionClass($this);
            $method = $class->getMethod($this->name);
        }

        catch (ReflectionException $e) {
            $this->fail($e->getMessage());
        }

        $method->invoke($this);
    }

    /**
     * Sets the name of a TestCase.
     *
     * @param  string
     * @access public
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Creates a default TestResult object.
     *
     * @return PHPUnit2_Framework_TestResult
     * @access protected
     */
    protected function createResult() {
        return new PHPUnit2_Framework_TestResult;
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
