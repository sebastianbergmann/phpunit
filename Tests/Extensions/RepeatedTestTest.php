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
// $Id: RepeatedTestTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/Extensions/RepeatedTest.php';

require_once 'PHPUnit2/Tests/Success.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Extensions_RepeatedTestTest extends PHPUnit2_Framework_TestCase {
    private $suite;

    public function __construct() {
        $this->suite = new PHPUnit2_Framework_TestSuite;

        $this->suite->addTest(new PHPUnit2_Tests_Success);
        $this->suite->addTest(new PHPUnit2_Tests_Success);
    }

    public function testRepeatedOnce() {
        $test = new PHPUnit2_Extensions_RepeatedTest($this->suite, 1);
        $this->assertEquals(2, $test->countTestCases());

        $result = $test->run();
        $this->assertEquals(2, $result->runCount());
    }

    public function testRepeatedMoreThanOnce() {
        $test = new PHPUnit2_Extensions_RepeatedTest($this->suite, 3);
        $this->assertEquals(6, $test->countTestCases());

        $result = $test->run();
        $this->assertEquals(6, $result->runCount());
    }

    public function testRepeatedZero() {
        $test = new PHPUnit2_Extensions_RepeatedTest($this->suite, 0);
        $this->assertEquals(0, $test->countTestCases());

        $result = $test->run();
        $this->assertEquals(0, $result->runCount());
    }

    public function testRepeatedNegative() {
        try {
            $test = new PHPUnit2_Extensions_RepeatedTest($this->suite, -1);
        }

        catch (Exception $e) {
            return;
        }

        $this->fail('Should throw an Exception');
    }
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
