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
 * @version    CVS: $Id: TestCaseTest.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/TestCase.php';

require_once 'Error.php';
require_once 'Failure.php';
require_once 'NoArgTestCaseTest.php';
require_once 'SetupFailure.php';
require_once 'Success.php';
require_once 'TearDownFailure.php';
require_once 'TornDown2.php';
require_once 'TornDown3.php';
require_once 'TornDown4.php';
require_once 'TornDown5.php';
require_once 'WasRun.php';

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
class Framework_TestCaseTest extends PHPUnit2_Framework_TestCase {
    public function testCaseToString() {
        $this->assertEquals(
          'testCaseToString(Framework_TestCaseTest)',
          $this->toString()
        );
    }

    public function testError() {
        $this->verifyError(new Error);
    }

    public function testExceptionRunningAndTearDown() {
        $result = new PHPUnit2_Framework_TestResult();
        $t      = new TornDown5;

        $t->run($result);

        $errors = $result->errors();

        $this->assertEquals(
          'tearDown',
          $errors[0]->thrownException()->getMessage()
        );
    }

    public function testFailure() {
        $this->verifyFailure(new Failure);
    }

    /* PHP does not support anonymous classes
    public function testNamelessTestCase() {
    }
    */

    public function testNoArgTestCasePasses() {
        $result = new PHPUnit2_Framework_TestResult();
        $t      = new PHPUnit2_Framework_TestSuite('NoArgTestCaseTest');

        $t->run($result);

        $this->assertEquals(1, $result->runCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->errorCount());
    }

    public function testRunAndTearDownFails() {
        $fails = new TornDown3;

		    $this->verifyError($fails);
		    $this->assertTrue($fails->tornDown);
    }

    public function testSetupFails() {
        $this->verifyError(new SetupFailure);
    }

    public function testSuccess() {
        $this->verifySuccess(new Success);
    }

    public function testTearDownAfterError() {
        $fails = new TornDown2;

		    $this->verifyError($fails);
		    $this->assertTrue($fails->tornDown);
    }

    public function testTearDownFails() {
        $this->verifyError(new TearDownFailure);
    }

    public function testTearDownSetupFails() {
        $fails = new TornDown4;

		    $this->verifyError($fails);
		    $this->assertFalse($fails->tornDown);
    }

    public function testWasRun() {
        $test = new WasRun;
        $test->run();

        $this->assertTrue($test->wasRun);
    }

    protected function verifyError(PHPUnit2_Framework_TestCase $test) {
        $result = $test->run();

        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(1, $result->runCount());
    }

    protected function verifyFailure(PHPUnit2_Framework_TestCase $test) {
        $result = $test->run();

        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        $this->assertEquals(1, $result->runCount());
    }

    protected function verifySuccess(PHPUnit2_Framework_TestCase $test) {
        $result = $test->run();

        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(1, $result->runCount());
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
