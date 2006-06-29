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
// $Id: TestListenerTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Framework/TestResult.php';

require_once 'PHPUnit2/Tests/Error.php';
require_once 'PHPUnit2/Tests/Failure.php';
require_once 'PHPUnit2/Tests/Success.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Framework_TestListenerTest extends PHPUnit2_Framework_TestCase implements PHPUnit2_Framework_TestListener {
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

    public function endTest(PHPUnit2_Framework_Test $test) {
        $this->endCount++;
    }

    public function startTest(PHPUnit2_Framework_Test $test) {
        $this->startCount++;
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
        $test = new PHPUnit2_Tests_Error;
        $test->run($this->result);

        $this->assertEquals(1, $this->errorCount);
        $this->assertEquals(1, $this->endCount);
    }

    public function testFailure() {
        $test = new PHPUnit2_Tests_Failure;
        $test->run($this->result);

        $this->assertEquals(1, $this->failureCount);
        $this->assertEquals(1, $this->endCount);
    }

    public function testStartStop() {
        $test = new PHPUnit2_Tests_Success;
        $test->run($this->result);

        $this->assertEquals(1, $this->startCount);
        $this->assertEquals(1, $this->endCount);
    }
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
