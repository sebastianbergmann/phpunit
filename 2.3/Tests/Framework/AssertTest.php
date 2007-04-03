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

require_once 'TestIterator.php';

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
class Framework_AssertTest extends PHPUnit2_Framework_TestCase {
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

        $this->assertContains($foo, new TestIterator(array($foo)));

        try {
            $this->assertContains($foo, new TestIterator(array(new StdClass)));
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertIteratorContainsString() {
        $this->assertContains('foo', new TestIterator(array('foo')));

        try {
            $this->assertContains('foo', new TestIterator(array('bar')));
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

    public function testAssertEqualsArray() {
        $this->assertEquals(array('a', 'b'), array('a', 'b'));

        try {
            $this->assertEquals(array('a', 'b'), array('b', 'a'));
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsArray() {
        $this->assertNotEquals(array('a', 'b'), array('b', 'a'));

        try {
            $this->assertNotEquals(array('a', 'b'), array('a', 'b'));
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsFloat() {
        $this->assertEquals(2.3, 2.3);

        try {
            $this->assertEquals(2.3, 4.2);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsFloat() {
        $this->assertNotEquals(2.3, 4.2);

        try {
            $this->assertNotEquals(2.3, 2.3);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsFloatDelta() {
        $this->assertEquals(2.3, 2.5, '', 0.5);

        try {
            $this->assertEquals(2.3, 4.2, '', 0.5);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsFloatDelta() {
        $this->assertNotEquals(2.3, 4.2, '', 0.5);

        try {
            $this->assertNotEquals(2.3, 2.5, '', 0.5);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsInteger() {
        $this->assertEquals(23, 23);

        try {
            $this->assertEquals(23, 42);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsInteger() {
        $this->assertNotEquals(23, 42);

        try {
            $this->assertNotEquals(23, 23);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsObject() {
        $this->assertEquals(new StdClass, new StdClass);

        try {
            $this->assertEquals(new StdClass, new Exception);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsObject() {
        $this->assertNotEquals(new StdClass, new Exception);

        try {
            $this->assertNotEquals(new StdClass, new StdClass);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsString() {
        $this->assertEquals('ab', 'ab');

        try {
            $this->assertEquals('ab', 'ba');
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsString() {
        $this->assertNotEquals('ab', 'ba');

        try {
            $this->assertNotEquals('ab', 'ab');
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

    public function testAssertTypeClass() {
        $this->assertType('StdClass', new StdClass);

        try {
            $this->assertType('StdClass', new Exception);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
}

    public function testAssertNotTypeClass() {
        $this->assertNotType('StdClass', new Exception);

        try {
            $this->assertNotType('StdClass', new StdClass);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeInteger() {
        $this->assertType('integer', 2204);

        try {
            $this->assertType('integer', 'string');
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeInteger() {
        $this->assertNotType('integer', 'string');

        try {
            $this->assertNotType('integer', 2204);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeString() {
        $this->assertType('string', 'string');

        try {
            $this->assertType('string', 2204);
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeString() {
        $this->assertNotType('string', 2204);

        try {
            $this->assertNotType('string', 'string');
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
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
