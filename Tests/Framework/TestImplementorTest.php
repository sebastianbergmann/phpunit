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
// $Id: TestImplementorTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestResult.php';

require_once 'PHPUnit2/Tests/DoubleTestCase.php';
require_once 'PHPUnit2/Tests/Success.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Framework_TestImplementorTest extends PHPUnit2_Framework_TestCase {
    private $test;

    public function __construct() {
        $this->test = new PHPUnit2_Tests_DoubleTestCase(
          new PHPUnit2_Tests_Success
        );
    }

    public function testSuccessfulRun() {
        $result = new PHPUnit2_Framework_TestResult;

        $this->test->run($result);

        $this->assertEquals($this->test->countTestCases(), $result->runCount());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
    }
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
