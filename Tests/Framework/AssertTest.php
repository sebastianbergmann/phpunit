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
// $Id: AssertTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';

require_once 'PHPUnit2/Tests/Iterator.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
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

    public function testAssertArrayContainsObject() {
        $a = new StdClass;
        $b = new StdClass;

        $this->assertContains($a, array($a));

        try {
            $this->assertContains($a, array($b));
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayContainsString() {
        $this->assertContains('foo', array('foo'));

        try {
            $this->assertContains('foo', array('bar'));
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertIteratorContainsObject() {
        $foo = new StdClass;

        $this->assertContains($foo, new PHPUnit2_Tests_Iterator(array($foo)));

        try {
            $this->assertContains($foo, new PHPUnit2_Tests_Iterator(array(new StdClass)));
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertIteratorContainsString() {
        $this->assertContains('foo', new PHPUnit2_Tests_Iterator(array('foo')));

        try {
            $this->assertContains('foo', new PHPUnit2_Tests_Iterator(array('bar')));
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertStringContainsString() {
        $this->assertContains('foo', 'foobar');

        try {
            $this->assertContains('foo', 'bar');
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayNotContainsObject() {
        $a = new StdClass;
        $b = new StdClass;

        $this->assertNotContains($a, array($b));

        try {
            $this->assertNotContains($a, array($a));
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayNotContainsString() {
        $this->assertNotContains('foo', array('bar'));

        try {
            $this->assertNotContains('foo', array('foo'));
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertStringNotContainsString() {
        $this->assertNotContains('foo', 'bar');

        try {
            $this->assertNotContains('foo', 'foo');
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
        $this->assertEquals(NULL, NULL);
    }

    public function testAssertStringEquals() {
        $this->assertEquals('a', 'a');
    }

    public function testAssertNullNotEqualsString() {
        try {
            $this->assertEquals(NULL, 'foo');
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertStringNotEqualsNull() {
        try {
            $this->assertEquals('', NULL);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNullNotEqualsNull() {
        try {
            $this->assertEquals(NULL, new StdClass);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNull() {
        $this->assertNull(NULL);

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
            $this->assertNotNull(NULL);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTrue() {
        $this->assertTrue(TRUE);

        try {
            $this->assertTrue(FALSE);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertFalse() {
        $this->assertFalse(FALSE);

        try {
            $this->assertFalse(TRUE);
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
          NULL
        );

        $this->assertNotSame(
          NULL,
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
            $this->assertNotSame(NULL, NULL);
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
