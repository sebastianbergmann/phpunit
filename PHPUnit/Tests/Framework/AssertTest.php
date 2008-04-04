<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
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
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
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

    public function testAssertArrayContainsOnlyIntegers()
    {
        $this->assertContainsOnly('integer', array(1, 2, 3));

        try {
            $this->assertContainsOnly('integer', array("1", 2, 3));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayNotContainsOnlyIntegers()
    {
        $this->assertNotContainsOnly('integer', array("1", 2, 3));

        try {
            $this->assertNotContainsOnly('integer', array(1, 2, 3));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayContainsOnlyStdClass()
    {
        $this->assertContainsOnly('StdClass', array(new StdClass));

        try {
            $this->assertContainsOnly('StdClass', array('StdClass'));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertArrayNotContainsOnlyStdClass()
    {
        $this->assertNotContainsOnly('StdClass', array('StdClass'));

        try {
            $this->assertNotContainsOnly('StdClass', array(new StdClass));
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

    public function testAssertXmlFileEqualsXmlFile()
    {
        $this->assertXmlFileEqualsXmlFile(
          dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml',
          dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml'
        );

        try {
            $this->assertXmlFileEqualsXmlFile(
              dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml',
              dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'bar.xml'
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertXmlFileNotEqualsXmlFile()
    {
        $this->assertXmlFileNotEqualsXmlFile(
          dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml',
          dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'bar.xml'
        );

        try {
            $this->assertXmlFileNotEqualsXmlFile(
              dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml',
              dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml'
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertXmlStringEqualsXmlString()
    {
        $this->assertXmlStringEqualsXmlString('<root/>', '<root/>');

        try {
            $this->assertXmlStringEqualsXmlString('<foo/>', '<bar/>');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertXmlStringNotEqualsXmlString()
    {
        $this->assertXmlStringNotEqualsXmlString('<foo/>', '<bar/>');

        try {
            $this->assertXmlStringNotEqualsXmlString('<root/>', '<root/>');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsDOMDocument()
    {
        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML('<root></root>');

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML('<root/>');

        $this->assertEquals($expected, $actual);

        try {
            $this->assertNotEquals($expected, $actual);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsDOMDocument2()
    {
        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML('<foo></foo>');

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML('<bar/>');        

        $this->assertNotEquals($expected, $actual);

        try {
            $this->assertEquals($expected, $actual);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsDOMDocument3()
    {
        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML('<foo attr="bar"></foo>');

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML('<foo attr="bar"/>');

        $this->assertEquals($expected, $actual);

        try {
            $this->assertNotEquals($expected, $actual);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsDOMDocument4()
    {
        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML('<root><foo attr="bar"></foo></root>');

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML('<root><foo attr="bar"/></root>');

        $this->assertEquals($expected, $actual);

        try {
            $this->assertNotEquals($expected, $actual);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsDOMDocument5()
    {
        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML('<foo attr1="bar"/>');

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML('<foo attr1="foobar"/>');

        $this->assertNotEquals($expected, $actual);

        try {
            $this->assertEquals($expected, $actual);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsDOMDocument6()
    {
        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML('<foo> bar </foo>');

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML('<foo />');

        $this->assertNotEquals($expected, $actual);        

        try {
            $this->assertEquals($expected, $actual);        
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsDOMDocument7()
    {
        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML('<foo xmlns="urn:myns:bar"/>');

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML('<foo xmlns="urn:notmyns:bar"/>');

        $this->assertNotEquals($expected, $actual);

        try {
            $this->assertEquals($expected, $actual);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsDOMDocument8()
    {
        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML("<root>\n  <child/>\n</root>");

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML('<root><child/></root>');

        $this->assertEquals($expected, $actual);        

        try {
            $this->assertNotEquals($expected, $actual);        
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertEqualsDOMDocument9()
    {
        $expected = new DOMDocument;
        $expected->preserveWhiteSpace = FALSE;
        $expected->loadXML('<foo> bar </foo>');

        $actual = new DOMDocument;
        $actual->preserveWhiteSpace = FALSE;
        $actual->loadXML('<foo> bir </foo>');

        $this->assertNotEquals($expected, $actual);        

        try {
            $this->assertEquals($expected, $actual);        
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertStringEqualsNumeric()
    {
        $this->assertEquals('0', 0);

        try {
            $this->assertEquals('0', 1);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertStringEqualsNumeric2()
    {
        $this->assertNotEquals('A', 0);
    }

    public function testAssertFileExists()
    {
        $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'AllTests.php');

        try {
            $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertFileNotExists()
    {
        $this->assertFileNotExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        try {
            $this->assertFileNotExists(__DIR__ . DIRECTORY_SEPARATOR . 'AllTests.php');
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

    public function testAssertSame2()
    {
        $this->assertSame(TRUE, TRUE);
        $this->assertSame(FALSE, FALSE);

        try {
            $this->assertSame(TRUE, FALSE);
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

    public function testAssertNotSame2()
    {
        $this->assertNotSame(TRUE, FALSE);
        $this->assertNotSame(FALSE, TRUE);

        try {
            $this->assertNotSame(TRUE, TRUE);
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

    public function testGreaterThan()
    {
        $this->assertGreaterThan(1, 2);

        try {
            $this->assertGreaterThan(2, 1);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAttributeGreaterThan()
    {
        $this->assertAttributeGreaterThan(
          1, 'bar', new ClassWithNonPublicAttributes
        );

        try {
            $this->assertAttributeGreaterThan(
              1, 'foo', new ClassWithNonPublicAttributes
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testGreaterThanOrEqual()
    {
        $this->assertGreaterThanOrEqual(1, 2);

        try {
            $this->assertGreaterThanOrEqual(2, 1);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAttributeGreaterThanOrEqual()
    {
        $this->assertAttributeGreaterThanOrEqual(
          1, 'bar', new ClassWithNonPublicAttributes
        );

        try {
            $this->assertAttributeGreaterThanOrEqual(
              2, 'foo', new ClassWithNonPublicAttributes
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testLessThan()
    {
        $this->assertLessThan(2, 1);

        try {
            $this->assertLessThan(1, 2);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAttributeLessThan()
    {
        $this->assertAttributeLessThan(
          2, 'foo', new ClassWithNonPublicAttributes
        );

        try {
            $this->assertAttributeLessThan(
              1, 'bar', new ClassWithNonPublicAttributes
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testLessThanOrEqual()
    {
        $this->assertLessThanOrEqual(2, 1);

        try {
            $this->assertLessThanOrEqual(1, 2);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAttributeLessThanOrEqual()
    {
        $this->assertAttributeLessThanOrEqual(
          2, 'foo', new ClassWithNonPublicAttributes
        );

        try {
            $this->assertAttributeLessThanOrEqual(
              1, 'bar', new ClassWithNonPublicAttributes
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testGetObjectAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertEquals('foo', $this->readAttribute($obj, 'publicAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedAttribute'));
        $this->assertEquals('baz', $this->readAttribute($obj, 'privateAttribute'));
        $this->assertEquals('parent', $this->readAttribute($obj, 'privateParentAttribute'));
    }

    public function testGetStaticAttribute()
    {
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'publicStaticAttribute'));
        $this->assertEquals('bar', $this->readAttribute('ClassWithNonPublicAttributes', 'protectedStaticAttribute'));
        $this->assertEquals('baz', $this->readAttribute('ClassWithNonPublicAttributes', 'privateStaticAttribute'));
        $this->assertEquals('parent', $this->readAttribute('ClassWithNonPublicAttributes', 'privateStaticParentAttribute'));
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

    public function testAssertPublicAttributeContainsOnly()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeContainsOnly('string', 'publicArray', $obj);

        try {
            $this->assertAttributeContainsOnly('integer', 'publicArray', $obj);
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

    public function testAssertPublicAttributeNotContainsOnly()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotContainsOnly('integer', 'publicArray', $obj);

        try {
            $this->assertAttributeNotContainsOnly('string', 'publicArray', $obj);
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

    public function testAssertPublicAttributeSame()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeSame('foo', 'publicAttribute', $obj);

        try {
            $this->assertAttributeSame('bar', 'publicAttribute', $obj);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPublicAttributeNotSame()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotSame('bar', 'publicAttribute', $obj);

        try {
            $this->assertAttributeNotSame('foo', 'publicAttribute', $obj);
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

    public function testAssertPublicStaticAttributeEquals()
    {
        $this->assertAttributeEquals('foo', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertAttributeEquals('bar', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPublicStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('bar', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertAttributeNotEquals('foo', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertProtectedStaticAttributeEquals()
    {
        $this->assertAttributeEquals('bar', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertAttributeEquals('foo', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertProtectedStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('foo', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertAttributeNotEquals('bar', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPrivateStaticAttributeEquals()
    {
        $this->assertAttributeEquals('baz', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertAttributeEquals('foo', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertPrivateStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('foo', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertAttributeNotEquals('baz', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testClassHasPublicAttribute()
    {
        $this->assertClassHasAttribute('publicAttribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertClassHasAttribute('attribute', 'ClassWithNonPublicAttributes');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testClassNotHasPublicAttribute()
    {
        $this->assertClassNotHasAttribute('attribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertClassNotHasAttribute('publicAttribute', 'ClassWithNonPublicAttributes');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testClassHasPublicStaticAttribute()
    {
        $this->assertClassHasStaticAttribute('publicStaticAttribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertClassHasStaticAttribute('attribute', 'ClassWithNonPublicAttributes');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testClassNotHasPublicStaticAttribute()
    {
        $this->assertClassNotHasStaticAttribute('attribute', 'ClassWithNonPublicAttributes');

        try {
            $this->assertClassNotHasStaticAttribute('publicStaticAttribute', 'ClassWithNonPublicAttributes');
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

    public function testAssertThatAttributeEquals()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->attribute(
            $this->equalTo('foo'),
            'publicAttribute'
          )
        );
    }

    public function testAssertThatAttributeEqualTo()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->attributeEqualTo('publicAttribute', 'foo')
        );
    }

    public function testAssertThatAnything()
    {
        $this->assertThat('anything', $this->anything());
    }

    public function testAssertThatAnythingAndAnything()
    {
        $this->assertThat(
          'anything',
          $this->logicalAnd(
            $this->anything(), $this->anything()
          )
        );
    }

    public function testAssertThatAnythingOrAnything()
    {
        $this->assertThat(
          'anything',
          $this->logicalOr(
            $this->anything(), $this->anything()
          )
        );
    }

    public function testAssertThatAnythingXorNotAnything()
    {
        $this->assertThat(
          'anything',
          $this->logicalXor(
            $this->anything(),
            $this->logicalNot($this->anything())
          )
        );
    }

    public function testAssertThatContains()
    {
        $this->assertThat(array('foo'), $this->contains('foo'));
    }

    public function testAssertThatStringContains()
    {
        $this->assertThat('barfoobar', $this->stringContains('foo'));
    }

    public function testAssertThatContainsOnly()
    {
        $this->assertThat(array('foo'), $this->containsOnly('string'));
    }

    public function testAssertThatArrayHasKey()
    {
        $this->assertThat(array('foo' => 'bar'), $this->arrayHasKey('foo'));
    }

    public function testAssertThatClassHasAttribute()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->classHasAttribute('publicAttribute')
        );
    }

    public function testAssertThatClassHasStaticAttribute()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->classHasStaticAttribute('publicStaticAttribute')
        );
    }

    public function testAssertThatObjectHasAttribute()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->objectHasAttribute('publicAttribute')
        );
    }

    public function testAssertThatEqualTo()
    {
        $this->assertThat('foo', $this->equalTo('foo'));
    }

    public function testAssertThatIdenticalTo()
    {
        $value      = new StdClass;
        $constraint = $this->identicalTo($value);

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatIsInstanceOf()
    {
        $this->assertThat(new StdClass, $this->isInstanceOf('StdClass'));
    }

    public function testAssertThatIsType()
    {
        $this->assertThat('string', $this->isType('string'));
    }

    public function testAssertThatFileExists()
    {
        $this->assertThat(
          __DIR__ . DIRECTORY_SEPARATOR . 'AllTests.php',
          $this->fileExists()
        );
    }

    public function testAssertThatGreaterThan()
    {
        $this->assertThat(2, $this->greaterThan(1));
    }

    public function testAssertThatGreaterThanOrEqual()
    {
        $this->assertThat(2, $this->greaterThanOrEqual(1));
    }

    public function testAssertThatLessThan()
    {
        $this->assertThat(1, $this->lessThan(2));
    }

    public function testAssertThatLessThanOrEqual()
    {
        $this->assertThat(1, $this->lessThanOrEqual(2));
    }

    public function testAssertThatMatchesRegularExpression()
    {
        $this->assertThat('foobar', $this->matchesRegularExpression('/foo/'));
    }

    /**
     * @expectedException PHPUnit_Framework_IncompleteTestError incomplete 0
     */
    public function testMarkTestIncomplete()
    {
        $this->markTestIncomplete('incomplete');
    }

    /**
     * @expectedException PHPUnit_Framework_SkippedTestError
     */
    public function testMarkTestSkipped()
    {
        $this->markTestSkipped();
    }
}
?>
