<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once '_files/ClassWithNonPublicAttributes.php';
require_once '_files/SampleClass.php';
require_once '_files/Struct.php';
require_once '_files/TestIterator.php';
require_once '_files/WasRun.php';

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class Framework_AssertTest extends PHPUnit_Framework_TestCase
{
    public function testFail()
    {
        try {
            $this->fail();
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;

        $this->assertContains($a, array($a));

        try {
            $this->assertContains($a, array($b));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayContainsString()
    {
        $this->assertContains('foo', array('foo'));

        try {
            $this->assertContains('foo', array('bar'));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayHasIntegerKey()
    {
        $this->assertArrayHasKey(0, array('foo'));

        try {
            $this->assertArrayHasKey(1, array('foo'));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayNotHasIntegerKey()
    {
        $this->assertArrayNotHasKey(1, array('foo'));

        try {
            $this->assertArrayNotHasKey(0, array('foo'));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayHasStringKey()
    {
        $this->assertArrayHasKey('foo', array('foo' => 'bar'));

        try {
            $this->assertArrayHasKey('bar', array('foo' => 'bar'));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayNotHasStringKey()
    {
        $this->assertArrayNotHasKey('bar', array('foo' => 'bar'));

        try {
            $this->assertArrayNotHasKey('foo', array('foo' => 'bar'));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertIteratorContainsObject()
    {
        $foo = new stdClass;

        $this->assertContains($foo, new TestIterator(array($foo)));

        try {
            $this->assertContains($foo, new TestIterator(array(new stdClass)));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertIteratorContainsString()
    {
        $this->assertContains('foo', new TestIterator(array('foo')));

        try {
            $this->assertContains('foo', new TestIterator(array('bar')));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertStringContainsString()
    {
        $this->assertContains('foo', 'foobar');

        try {
            $this->assertContains('foo', 'bar');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayNotContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;

        $this->assertNotContains($a, array($b));

        try {
            $this->assertNotContains($a, array($a));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayNotContainsString()
    {
        $this->assertNotContains('foo', array('bar'));

        try {
            $this->assertNotContains('foo', array('foo'));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertStringNotContainsString()
    {
        $this->assertNotContains('foo', 'bar');

        try {
            $this->assertNotContains('foo', 'foo');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsArray()
    {
        $this->assertEquals(array('a', 'b' => array(1, 2)), array('a', 'b' => array(1, 2)));

        try {
            $this->assertEquals(array('a', 'b' => array(1, 2)), array('a', 'b' => array(2, 1)));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsArray()
    {
        $this->assertNotEquals(array('a', 'b' => array(1, 2)), array('a', 'b' => array(2, 1)));

        try {
            $this->assertNotEquals(array('a', 'b' => array(1, 2)), array('a', 'b' => array(1, 2)));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsFloat()
    {
        $this->assertEquals(2.3, 2.3);

        try {
            $this->assertEquals(2.3, 4.2);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsFloat()
    {
        $this->assertNotEquals(2.3, 4.2);

        try {
            $this->assertNotEquals(2.3, 2.3);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsFloatDelta()
    {
        $this->assertEquals(2.3, 2.5, '', 0.5);

        try {
            $this->assertEquals(2.3, 4.2, '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsFloatDelta()
    {
        $this->assertNotEquals(2.3, 4.2, '', 0.5);

        try {
            $this->assertNotEquals(2.3, 2.5, '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsArrayFloatDelta()
    {
        $this->assertEquals(array(2.3), array(2.5), '', 0.5);

        try {
            $this->assertEquals(array(2.3), array(4.2), '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsArrayFloatDelta()
    {
        $this->assertNotEquals(array(2.3), array(4.2), '', 0.5);

        try {
            $this->assertNotEquals(array(2.3), array(2.5), '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsStructFloatDelta()
    {
        $this->assertEquals(new Struct(2.3), new Struct(2.5), '', 0.5);

        try {
            $this->assertEquals(new Struct(2.3), new Struct(4.2), '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsStructFloatDelta()
    {
        $this->assertNotEquals(new Struct(2.3), new Struct(4.2), '', 0.5);

        try {
            $this->assertNotEquals(new Struct(2.3), new Struct(2.5), '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsArrayStructFloatDelta()
    {
        $this->assertEquals(array(new Struct(2.3)), array(new Struct(2.5)), '', 0.5);

        try {
            $this->assertEquals(array(new Struct(2.3)), array(new Struct(4.2)), '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsArrayStructFloatDelta()
    {
        $this->assertNotEquals(array(new Struct(2.3)), array(new Struct(4.2)), '', 0.5);

        try {
            $this->assertNotEquals(array(new Struct(2.3)), array(new Struct(2.5)), '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsArrayOfArrayFloatDelta()
    {
        $this->assertEquals(array(array(2.3)), array(array(2.5)), '', 0.5);

        try {
            $this->assertEquals(array(array(2.3)), array(array(4.2)), '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsArrayOfArrayFloatDelta()
    {
        $this->assertNotEquals(array(array(2.3)), array(array(4.2)), '', 0.5);

        try {
            $this->assertNotEquals(array(array(2.3)), array(array(2.5)), '', 0.5);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsInteger()
    {
        $this->assertEquals(23, 23);

        try {
            $this->assertEquals(23, 42);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsInteger()
    {
        $this->assertNotEquals(23, 42);

        try {
            $this->assertNotEquals(23, 23);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsObject()
    {
        $a = new SampleClass( 4,  8, 15);
        $b = new SampleClass(16, 23, 42);

        $this->assertEquals($a, $a);

        try {
            $this->assertEquals($a, $b);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsObject()
    {
        $a = new SampleClass( 4,  8, 15);
        $b = new SampleClass(16, 23, 42);

        $this->assertNotEquals($a, $b);

        try {
            $this->assertNotEquals($a, $a);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsString()
    {
        $this->assertEquals('ab', 'ab');

        try {
            $this->assertEquals('ab', 'ba');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotEqualsString()
    {
        $this->assertNotEquals('ab', 'ba');

        try {
            $this->assertNotEquals('ab', 'ab');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertFileExists()
    {
        $this->assertFileExists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AllTests.php');

        try {
            $this->assertFileExists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'NotExisting');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertFileNotExists()
    {
        $this->assertFileNotExists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'NotExisting');

        try {
            $this->assertFileNotExists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AllTests.php');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertObjectHasAttribute()
    {
        $o = new WasRun('runTest');

        $this->assertObjectHasAttribute('wasRun', $o);

        try {
            $this->assertObjectHasAttribute('foo', $o);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertObjectNotHasAttribute()
    {
        $o = new WasRun('runTest');

        $this->assertObjectNotHasAttribute('foo', $o);

        try {
            $this->assertObjectNotHasAttribute('wasRun', $o);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNull()
    {
        $this->assertNull(NULL);

        try {
            $this->assertNull(new stdClass);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotNull()
    {
        $this->assertNotNull(new stdClass);

        try {
            $this->assertNotNull(NULL);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTrue()
    {
        $this->assertTrue(TRUE);

        try {
            $this->assertTrue(FALSE);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertFalse()
    {
        $this->assertFalse(FALSE);

        try {
            $this->assertFalse(TRUE);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertRegExp()
    {
        $this->assertRegExp('/foo/', 'foobar');

        try {
            $this->assertRegExp('/foo/', 'bar');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotRegExp()
    {
        $this->assertNotRegExp('/foo/', 'bar');

        try {
            $this->assertNotRegExp('/foo/', 'foobar');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertSame()
    {
        $o = new stdClass;

        $this->assertSame($o, $o);

        try {
            $this->assertSame(
              new stdClass,
              new stdClass
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotSame()
    {
        $this->assertNotSame(
          new stdClass,
          NULL
        );

        $this->assertNotSame(
          NULL,
          new stdClass
        );

        $this->assertNotSame(
          new stdClass,
          new stdClass
        );

        $o = new stdClass;

        try {
            $this->assertNotSame($o, $o);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotSameFailsNull()
    {
        try {
            $this->assertNotSame(NULL, NULL);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeArray()
    {
        $this->assertType('array', array());

        try {
            $this->assertType('array', 'string');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeArray()
    {
        $this->assertNotType('array', 'string');

        try {
            $this->assertNotType('array', array());
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeBool()
    {
        $this->assertType('bool', TRUE);

        try {
            $this->assertType('bool', 'string');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeBool()
    {
        $this->assertNotType('bool', 'string');

        try {
            $this->assertNotType('bool', TRUE);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeClass()
    {
        $this->assertType('stdClass', new stdClass);

        try {
            $this->assertType('stdClass', new Exception);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeClass()
    {
        $this->assertNotType('stdClass', new Exception);

        try {
            $this->assertNotType('stdClass', new stdClass);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeFloat()
    {
        $this->assertType('float', 22.04);

        try {
            $this->assertType('integer', 'string');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeFloat()
    {
        $this->assertNotType('float', 'string');

        try {
            $this->assertNotType('float', 22.04);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeInteger()
    {
        $this->assertType('integer', 2204);

        try {
            $this->assertType('integer', 'string');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeInteger()
    {
        $this->assertNotType('integer', 'string');

        try {
            $this->assertNotType('integer', 2204);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeNull()
    {
        $this->assertType('null', NULL);

        try {
            $this->assertType('null', 'string');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeNull()
    {
        $this->assertNotType('null', 'string');

        try {
            $this->assertNotType('null', NULL);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeObject()
    {
        $this->assertType('object', new stdClass);

        try {
            $this->assertType('object', 'string');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeObject()
    {
        $this->assertNotType('object', 'string');

        try {
            $this->assertNotType('object', new stdClass);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTypeString()
    {
        $this->assertType('string', 'string');

        try {
            $this->assertType('string', 2204);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertNotTypeString()
    {
        $this->assertNotType('string', 2204);

        try {
            $this->assertNotType('string', 'string');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testGetAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertEquals('foo', $this->getAttribute($obj, 'publicAttribute'));
        $this->assertEquals('bar', $this->getAttribute($obj, 'protectedAttribute'));
        $this->assertEquals('baz', $this->getAttribute($obj, 'privateAttribute'));
    }

    public function testAssertPublicAttributeContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeContains('foo', 'publicArray', $obj);

        try {
            $this->assertAttributeContains('bar', 'publicArray', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPublicAttributeNotContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('bar', 'publicArray', $obj);

        try {
            $this->assertAttributeNotContains('foo', 'publicArray', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertProtectedAttributeContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeContains('bar', 'protectedArray', $obj);

        try {
            $this->assertAttributeContains('foo', 'protectedArray', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertProtectedAttributeNotContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('foo', 'protectedArray', $obj);

        try {
            $this->assertAttributeNotContains('bar', 'protectedArray', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPrivateAttributeContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeContains('baz', 'privateArray', $obj);

        try {
            $this->assertAttributeContains('foo', 'privateArray', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPrivateAttributeNotContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('foo', 'privateArray', $obj);

        try {
            $this->assertAttributeNotContains('baz', 'privateArray', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPublicAttributeEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('foo', 'publicAttribute', $obj);

        try {
            $this->assertAttributeEquals('bar', 'publicAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPublicAttributeNotEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('bar', 'publicAttribute', $obj);

        try {
            $this->assertAttributeNotEquals('foo', 'publicAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertProtectedAttributeEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('bar', 'protectedAttribute', $obj);

        try {
            $this->assertAttributeEquals('foo', 'protectedAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertProtectedAttributeNotEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('foo', 'protectedAttribute', $obj);

        try {
            $this->assertAttributeNotEquals('bar', 'protectedAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPrivateAttributeEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('baz', 'privateAttribute', $obj);

        try {
            $this->assertAttributeEquals('foo', 'privateAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPrivateAttributeNotEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('foo', 'privateAttribute', $obj);

        try {
            $this->assertAttributeNotEquals('baz', 'privateAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectHasPublicAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('publicAttribute', $obj);

        try {
            $this->assertObjectHasAttribute('attribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectNotHasPublicAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        try {
            $this->assertObjectNotHasAttribute('publicAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectHasOnTheFlyAttribute()
    {
        $obj = new StdClass;
        $obj->foo = 'bar';

        $this->assertObjectHasAttribute('foo', $obj);

        try {
            $this->assertObjectHasAttribute('bar', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectNotHasOnTheFlyAttribute()
    {
        $obj = new StdClass;
        $obj->foo = 'bar';

        $this->assertObjectNotHasAttribute('bar', $obj);

        try {
            $this->assertObjectNotHasAttribute('foo', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectHasProtectedAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('protectedAttribute', $obj);

        try {
            $this->assertObjectHasAttribute('attribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectNotHasProtectedAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        try {
            $this->assertObjectNotHasAttribute('protectedAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectHasPrivateAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('privateAttribute', $obj);

        try {
            $this->assertObjectHasAttribute('attribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectNotHasPrivateAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        try {
            $this->assertObjectNotHasAttribute('privateAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertThatAnything()
    {
        $constraint = $this->anything();
        $value      = 'anything';

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatContains()
    {
        $constraint = $this->contains('foo');
        $value      = array('foo');

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatStringContains()
    {
        $constraint = $this->stringContains('foo');
        $value      = 'barfoobar';

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatArrayHasKey()
    {
        $constraint = $this->arrayHasKey('foo');
        $value      = array('foo' => 'bar');

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatObjectHasAttribute()
    {
        $constraint = $this->objectHasAttribute('publicAttribute');
        $value      = new ClassWithNonPublicAttributes;

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatEqualTo()
    {
        $constraint = $this->equalTo('foo');
        $value      = 'foo';

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatIdenticalTo()
    {
        $value      = new StdClass;
        $constraint = $this->identicalTo($value);

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatIsInstanceOf()
    {
        $constraint = $this->isInstanceOf('StdClass');
        $value      = new StdClass;

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatIsType()
    {
        $constraint = $this->isType('string');
        $value      = 'string';

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatFileExists()
    {
        $constraint = $this->fileExists();
        $value      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AllTests.php';

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatGreaterThan()
    {
        $constraint = $this->greaterThan(1);
        $value      = 2;

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatLessThan()
    {
        $constraint = $this->lessThan(2);
        $value      = 1;

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatMatchesRegularExpression()
    {
        $constraint = $this->matchesRegularExpression('/foo/');
        $value      = 'foobar';

        $this->assertThat($value, $constraint);
    }
}
?>
