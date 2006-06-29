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
// $Id: ExceptionTestCaseTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';

require_once 'PHPUnit2/Tests/ThrowExceptionTestCase.php';
require_once 'PHPUnit2/Tests/ThrowNoExceptionTestCase.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Extensions_ExceptionTestCaseTest extends PHPUnit2_Framework_TestCase {
    public function testException() {
        $test = new PHPUnit2_Tests_ThrowExceptionTestCase(
          'test',
          'Exception'
        );

        $result = $test->run();

        $this->assertEquals(1, $result->runCount());
        $this->assertTrue($result->wasSuccessful());
    }

    public function testNoException() {
        $test = new PHPUnit2_Tests_ThrowNoExceptionTestCase(
          'test',
          'Exception'
        );

        $result = $test->run();

        $this->assertEquals(1, $result->failureCount());
        $this->assertEquals(1, $result->runCount());
    }

    public function testWrongException() {
        $test = new PHPUnit2_Tests_ThrowExceptionTestCase(
          'test',
          'ReflectionException'
        );

        $result = $test->run();

        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(1, $result->runCount());
    }
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
