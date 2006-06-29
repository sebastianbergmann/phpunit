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
 * @version    CVS: $Id: TestDecorator.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/Assert.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestResult.php';

/**
 * A Decorator for Tests.
 *
 * Use TestDecorator as the base class for defining new
 * test decorators. Test decorator subclasses can be introduced
 * to add behaviour before or after a test is run.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class PHPUnit2_Extensions_TestDecorator extends PHPUnit2_Framework_Assert implements PHPUnit2_Framework_Test {
    /**
     * The Test to be decorated.
     *
     * @var    object
     * @access protected
     */
    protected $test = NULL;

    /**
     * Constructor.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function __construct(PHPUnit2_Framework_Test $test) {
        $this->test = $test;
    }

    /**
     * Returns a string representation of the test.
     *
     * @return string
     * @access public
     */
    public function toString() {
        return $this->test->toString();
    }

    /**
     * Runs the test and collects the
     * result in a TestResult.
     *
     * @param  PHPUnit2_Framework_TestResult $result
     * @access public
     */
    public function basicRun(PHPUnit2_Framework_TestResult $result) {
        $this->test->run($result);
    }

    /**
     * Counts the number of test cases that
     * will be run by this test.
     *
     * @return integer
     * @access public
     */
    public function countTestCases() {
        return $this->test->countTestCases();
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
     * Returns the test to be run.
     *
     * @return PHPUnit2_Framework_Test
     * @access public
     */
    public function getTest() {
        return $this->test;
    }

    /**
     * Runs the decorated test and collects the
     * result in a TestResult.
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

        $this->basicRun($result);

        return $result;
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
