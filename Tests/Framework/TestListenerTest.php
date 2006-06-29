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
 * @version    CVS: $Id: TestListenerTest.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Framework/TestResult.php';

require_once 'Error.php';
require_once 'Failure.php';
require_once 'Success.php';

/**
 * 
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
class Framework_TestListenerTest extends PHPUnit2_Framework_TestCase implements PHPUnit2_Framework_TestListener {
    private $endCount;
    private $errorCount;
    private $failureCount;
    private $notImplementedCount;
    private $result;
    private $startCount;

    public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->errorCount++;
    }

    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
        $this->failureCount++;
    }

    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->notImplementedCount++;
    }

    public function startTestSuite(PHPUnit2_Framework_TestSuite $suite) {
    }

    public function endTestSuite(PHPUnit2_Framework_TestSuite $suite) {
    }

    public function startTest(PHPUnit2_Framework_Test $test) {
        $this->startCount++;
    }

    public function endTest(PHPUnit2_Framework_Test $test) {
        $this->endCount++;
    }

    protected function setUp() {
        $this->result = new PHPUnit2_Framework_TestResult;
        $this->result->addListener($this);

        $this->endCount            = 0;
        $this->failureCount        = 0;
        $this->notImplementedCount = 0;
        $this->startCount          = 0;
    }

    public function testError() {
        $test = new Error;
        $test->run($this->result);

        $this->assertEquals(1, $this->errorCount);
        $this->assertEquals(1, $this->endCount);
    }

    public function testFailure() {
        $test = new Failure;
        $test->run($this->result);

        $this->assertEquals(1, $this->failureCount);
        $this->assertEquals(1, $this->endCount);
    }

    public function testStartStop() {
        $test = new Success;
        $test->run($this->result);

        $this->assertEquals(1, $this->startCount);
        $this->assertEquals(1, $this->endCount);
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
