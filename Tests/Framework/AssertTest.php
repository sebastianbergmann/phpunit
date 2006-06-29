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
 * @version    CVS: $Id: AssertTest.php 539 2006-02-13 16:08:42Z sb $
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
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
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
