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
// $Id: AssertTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Framework_AssertTest extends PHPUnit2_Framework_TestCase {
    public function testFail() {
        try {
            $this->fail();
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEquals() {
        $o = new StdClass;

        $this->assertEquals($o, $o);
    }

    public function testAssertEqualsNull() {
        $this->assertEquals(null, null);
    }

    public function testAssertStringEquals() {
        $this->assertEquals('a', 'a');
    }

    public function testAssertNullNotEqualsString() {
        try {
            $this->assertEquals(null, 'foo');
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertStringNotEqualsNull() {
        try {
            $this->assertEquals('', null);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNullNotEqualsNull() {
        try {
            $this->assertEquals(null, new StdClass);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNull() {
        $this->assertNull(null);

        try {
            $this->assertNull(new StdClass);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotNull() {
        $this->assertNotNull(new StdClass);

        try {
            $this->assertNotNull(null);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTrue() {
        $this->assertTrue(true);

        try {
            $this->assertTrue(false);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertFalse() {
        $this->assertFalse(false);

        try {
            $this->assertFalse(true);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertSame() {
        $o = new StdClass;

        $this->assertSame($o, $o);

        try {
            $this->assertSame(
              new StdClass,
              new StdClass
            );
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotSame() {
        $this->assertNotSame(
          new StdClass,
          null
        );

        $this->assertNotSame(
          null,
          new StdClass
        );

        $this->assertNotSame(
          new StdClass,
          new StdClass
        );

        $o = new StdClass;

        try {
            $this->assertNotSame($o, $o);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotSameFailsNull() {
        try {
            $this->assertNotSame(null, null);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
