<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: TestCaseTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';

require_once 'PHPUnit2/Tests/Error.php';
require_once 'PHPUnit2/Tests/Failure.php';
require_once 'PHPUnit2/Tests/NoArgTestCaseTest.php';
require_once 'PHPUnit2/Tests/SetupFailure.php';
require_once 'PHPUnit2/Tests/Success.php';
require_once 'PHPUnit2/Tests/TearDownFailure.php';
require_once 'PHPUnit2/Tests/TornDown2.php';
require_once 'PHPUnit2/Tests/TornDown3.php';
require_once 'PHPUnit2/Tests/TornDown4.php';
require_once 'PHPUnit2/Tests/TornDown5.php';
require_once 'PHPUnit2/Tests/WasRun.php';

/**
 * A test case testing the testing framework.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Framework_TestCaseTest extends PHPUnit2_Framework_TestCase {
    public function testCaseToString() {
        $this->assertEquals(
          'testCaseToString(PHPUnit2_Tests_Framework_TestCaseTest)',
          $this->toString()
        );
    }

    public function testError() {
        $this->verifyError(new PHPUnit2_Tests_Error);
    }

    public function testExceptionRunningAndTearDown() {
        $result = new PHPUnit2_Framework_TestResult();
        $t      = new PHPUnit2_Tests_TornDown5;

        $t->run($result);

        $errors = $result->errors();

        $this->assertEquals(
          'tearDown',
          $errors[0]->thrownException()->getMessage()
        );
    }

    public function testFailure() {
        $this->verifyFailure(new PHPUnit2_Tests_Failure);
    }

    /* PHP does not support anonymous classes
    public function testNamelessTestCase() {
    }
    */

    public function testNoArgTestCasePasses() {
        $result = new PHPUnit2_Framework_TestResult();
        $t      = new PHPUnit2_Framework_TestSuite('PHPUnit2_Tests_NoArgTestCaseTest');

        $t->run($result);

        $this->assertEquals(1, $result->runCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->errorCount());
    }

    public function testRunAndTearDownFails() {
        $fails = new PHPUnit2_Tests_TornDown3;

		    $this->verifyError($fails);
		    $this->assertTrue($fails->tornDown);
    }

    public function testSetupFails() {
        $this->verifyError(new PHPUnit2_Tests_SetupFailure);
    }

    public function testSuccess() {
        $this->verifySuccess(new PHPUnit2_Tests_Success);
    }

    public function testTearDownAfterError() {
        $fails = new PHPUnit2_Tests_TornDown2;

		    $this->verifyError($fails);
		    $this->assertTrue($fails->tornDown);
    }

    public function testTearDownFails() {
        $this->verifyError(new PHPUnit2_Tests_TearDownFailure);
    }

    public function testTearDownSetupFails() {
        $fails = new PHPUnit2_Tests_TornDown4;

		    $this->verifyError($fails);
		    $this->assertFalse($fails->tornDown);
    }

    public function testWasRun() {
        $test = new PHPUnit2_Tests_WasRun;
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
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
