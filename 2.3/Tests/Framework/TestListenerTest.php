<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
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
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
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
