<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2012, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'SampleClass.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'Struct.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'TestIterator.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'Author.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'Book.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithToString.php';

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class Framework_AssertTest extends PHPUnit_Framework_TestCase
{
    protected $filesDirectory;

    protected function setUp()
    {
        $this->filesDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;

        $this->html = file_get_contents(
            $this->filesDirectory . 'SelectorAssertionsFixture.html'
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::fail
     */
    public function testFail()
    {
        try {
            $this->fail();
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        throw new PHPUnit_Framework_AssertionFailedError('Fail did not throw fail exception');
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertContains
     */
    public function testAssertSplObjectStorageContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;
        $c = new SplObjectStorage;
        $c->attach($a);

        $this->assertContains($a, $c);

        try {
            $this->assertContains($b, $c);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertContains
     */
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

    /**
     * @covers            PHPUnit_Framework_Assert::assertArrayHasKey
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertArrayHasKeyThrowsException()
    {
        $this->assertArrayHasKey(NULL, array());
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertArrayHasKey
     */
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

    /**
     * @covers            PHPUnit_Framework_Assert::assertArrayNotHasKey
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertArrayNotHasKeyThrowsException()
    {
        $this->assertArrayNotHasKey(NULL, array());
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertArrayNotHasKey
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertArrayHasKey
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertArrayNotHasKey
     */
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

    /**
     * @covers            PHPUnit_Framework_Assert::assertContains
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertContainsThrowsException()
    {
        $this->assertContains(NULL, NULL);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertContains
     */
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

    /**
     * @covers            PHPUnit_Framework_Assert::assertNotContains
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotContainsThrowsException()
    {
        $this->assertNotContains(NULL, NULL);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotContains
     */
    public function testAssertSplObjectStorageNotContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;
        $c = new SplObjectStorage;
        $c->attach($a);

        $this->assertNotContains($b, $c);

        try {
            $this->assertNotContains($a, $c);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertNotContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertNotContains
     */
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

    /**
     * @covers            PHPUnit_Framework_Assert::assertContainsOnly
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertContainsOnlyThrowsException()
    {
        $this->assertContainsOnly(NULL, NULL);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertContainsOnly
     */
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

    /**
     * @covers            PHPUnit_Framework_Assert::assertNotContainsOnly
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotContainsOnlyThrowsException()
    {
        $this->assertNotContainsOnly(NULL, NULL);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotContainsOnly
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertContainsOnly
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertNotContainsOnly
     */
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

    protected function createDOMDocument($content)
    {
        $document = new DOMDocument;
        $document->preserveWhiteSpace = FALSE;
        $document->loadXML($content);

        return $document;
    }

    protected function sameValues()
    {
        $object = new SampleClass(4, 8, 15);
        // cannot use $filesDirectory, because neither setUp() nor
        // setUpBeforeClass() are executed before the data providers
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml';
        $resource = fopen($file, 'r');

        return array(
            // NULL
            array(NULL, NULL),
            // strings
            array('a', 'a'),
            // integers
            array(0, 0),
            // floats
            array(2.3, 2.3),
            array(1/3, 1 - 2/3),
            array(log(0), log(0)),
            // arrays
            array(array(), array()),
            array(array(0 => 1), array(0 => 1)),
            array(array(0 => NULL), array(0 => NULL)),
            array(array('a', 'b' => array(1, 2)), array('a', 'b' => array(1, 2))),
            // objects
            array($object, $object),
            // resources
            array($resource, $resource),
        );
    }

    protected function notEqualValues()
    {
        // cyclic dependencies
        $book1 = new Book;
        $book1->author = new Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2 = new Book;
        $book2->author = new Author('Terry Pratch');
        $book2->author->books[] = $book2;

        $book3 = new Book;
        $book3->author = 'Terry Pratchett';
        $book4 = new stdClass;
        $book4->author = 'Terry Pratchett';

        $object1 = new SampleClass( 4,  8, 15);
        $object2 = new SampleClass(16, 23, 42);
        $object3 = new SampleClass( 4,  8, 15);
        $storage1 = new SplObjectStorage;
        $storage1->attach($object1);
        $storage2 = new SplObjectStorage;
        $storage2->attach($object3); // same content, different object

        // cannot use $filesDirectory, because neither setUp() nor
        // setUpBeforeClass() are executed before the data providers
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml';

        return array(
            // strings
            array('a', 'b'),
            array('a', 'A'),
            // integers
            array(1, 2),
            array(2, 1),
            // floats
            array(2.3, 4.2),
            array(2.3, 4.2, 0.5),
            array(array(2.3), array(4.2), 0.5),
            array(array(array(2.3)), array(array(4.2)), 0.5),
            array(new Struct(2.3), new Struct(4.2), 0.5),
            array(array(new Struct(2.3)), array(new Struct(4.2)), 0.5),
            // arrays
            array(array(), array(0 => 1)),
            array(array(0 => 1), array()),
            array(array(0 => NULL), array()),
            array(array(0 => 1, 1 => 2), array(0 => 1, 1 => 3)),
            array(array('a', 'b' => array(1, 2)), array('a', 'b' => array(2, 1))),
            // objects
            array(new SampleClass(4, 8, 15), new SampleClass(16, 23, 42)),
            array($object1, $object2),
            array($book1, $book2),
            array($book3, $book4), // same content, different class
            // resources
            array(fopen($file, 'r'), fopen($file, 'r')),
            // SplObjectStorage
            array($storage1, $storage2),
            // DOMDocument
            array(
                $this->createDOMDocument('<root></root>'),
                $this->createDOMDocument('<bar/>'),
            ),
            array(
                $this->createDOMDocument('<foo attr1="bar"/>'),
                $this->createDOMDocument('<foo attr1="foobar"/>'),
            ),
            array(
                $this->createDOMDocument('<foo> bar </foo>'),
                $this->createDOMDocument('<foo />'),
            ),
            array(
                $this->createDOMDocument('<foo xmlns="urn:myns:bar"/>'),
                $this->createDOMDocument('<foo xmlns="urn:notmyns:bar"/>'),
            ),
            array(
                $this->createDOMDocument('<foo> bar </foo>'),
                $this->createDOMDocument('<foo> bir </foo>'),
            ),
            // Exception
            //array(new Exception('Exception 1'), new Exception('Exception 2')),
            // different types
            array(new SampleClass(4, 8, 15), FALSE),
            array(FALSE, new SampleClass(4, 8, 15)),
            array(array(0 => 1, 1 => 2), FALSE),
            array(FALSE, array(0 => 1, 1 => 2)),
            array(array(), new stdClass),
            array(new stdClass, array()),
            // PHP: 0 == 'Foobar' => TRUE!
            // We want these values to differ
            array(0, 'Foobar'),
            array('Foobar', 0),
            array(3, acos(8)),
            array(acos(8), 3)
        );
    }

    protected function equalValues()
    {
        // cyclic dependencies
        $book1 = new Book;
        $book1->author = new Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2 = new Book;
        $book2->author = new Author('Terry Pratchett');
        $book2->author->books[] = $book2;

        $object1 = new SampleClass(4, 8, 15);
        $object2 = new SampleClass(4, 8, 15);
        $storage1 = new SplObjectStorage;
        $storage1->attach($object1);
        $storage2 = new SplObjectStorage;
        $storage2->attach($object1);

        return array(
            // strings
            array('a', 'A', 0, FALSE, TRUE), // ignore case
            // arrays
            array(array('a' => 1, 'b' => 2), array('b' => 2, 'a' => 1)),
            array(array(1), array('1')),
            array(array(3, 2, 1), array(2, 3, 1), 0, TRUE), // canonicalized comparison
            // floats
            array(2.3, 2.5, 0.5),
            array(array(2.3), array(2.5), 0.5),
            array(array(array(2.3)), array(array(2.5)), 0.5),
            array(new Struct(2.3), new Struct(2.5), 0.5),
            array(array(new Struct(2.3)), array(new Struct(2.5)), 0.5),
            // numeric with delta
            array(1, 2, 1),
            // objects
            array($object1, $object2),
            array($book1, $book2),
            // SplObjectStorage
            array($storage1, $storage2),
            // DOMDocument
            array(
                $this->createDOMDocument('<root></root>'),
                $this->createDOMDocument('<root/>'),
            ),
            array(
                $this->createDOMDocument('<root attr="bar"></root>'),
                $this->createDOMDocument('<root attr="bar"/>'),
            ),
            array(
                $this->createDOMDocument('<root><foo attr="bar"></foo></root>'),
                $this->createDOMDocument('<root><foo attr="bar"/></root>'),
            ),
            array(
                $this->createDOMDocument("<root>\n  <child/>\n</root>"),
                $this->createDOMDocument('<root><child/></root>'),
            ),
            // Exception
            //array(new Exception('Exception 1'), new Exception('Exception 1')),
            // mixed types
            array(0, '0'),
            array('0', 0),
            array(2.3, '2.3'),
            array('2.3', 2.3),
            array((string)(1/3), 1 - 2/3),
            array(1/3, (string)(1 - 2/3)),
            array('string representation', new ClassWithToString),
            array(new ClassWithToString, 'string representation'),
        );
    }

    public function equalProvider()
    {
        // same |= equal
        return array_merge($this->equalValues(), $this->sameValues());
    }

    public function notEqualProvider()
    {
        return $this->notEqualValues();
    }

    public function sameProvider()
    {
        return $this->sameValues();
    }

    public function notSameProvider()
    {
        // not equal |= not same
        // equal, ¬same |= not same
        return array_merge($this->notEqualValues(), $this->equalValues());
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertEquals
     * @dataProvider equalProvider
     */
    public function testAssertEqualsSucceeds($a, $b, $delta = 0, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertEquals
     * @dataProvider notEqualProvider
     */
    public function testAssertEqualsFails($a, $b, $delta = 0, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        try {
            $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotEquals
     * @dataProvider notEqualProvider
     */
    public function testAssertNotEqualsSucceeds($a, $b, $delta = 0, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotEquals
     * @dataProvider equalProvider
     */
    public function testAssertNotEqualsFails($a, $b, $delta = 0, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        try {
            $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSame
     * @dataProvider sameProvider
     */
    public function testAssertSameSucceeds($a, $b)
    {
        $this->assertSame($a, $b);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSame
     * @dataProvider notSameProvider
     */
    public function testAssertSameFails($a, $b)
    {
        try {
            $this->assertSame($a, $b);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotSame
     * @dataProvider notSameProvider
     */
    public function testAssertNotSameSucceeds($a, $b)
    {
        $this->assertNotSame($a, $b);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotSame
     * @dataProvider sameProvider
     */
    public function testAssertNotSameFails($a, $b)
    {
        try {
            $this->assertNotSame($a, $b);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertXmlFileEqualsXmlFile
     */
    public function testAssertXmlFileEqualsXmlFile()
    {
        $this->assertXmlFileEqualsXmlFile(
          $this->filesDirectory . 'foo.xml',
          $this->filesDirectory . 'foo.xml'
        );

        try {
            $this->assertXmlFileEqualsXmlFile(
              $this->filesDirectory . 'foo.xml',
              $this->filesDirectory . 'bar.xml'
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertXmlFileNotEqualsXmlFile
     */
    public function testAssertXmlFileNotEqualsXmlFile()
    {
        $this->assertXmlFileNotEqualsXmlFile(
          $this->filesDirectory . 'foo.xml',
          $this->filesDirectory . 'bar.xml'
        );

        try {
            $this->assertXmlFileNotEqualsXmlFile(
              $this->filesDirectory . 'foo.xml',
              $this->filesDirectory . 'foo.xml'
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertXmlStringEqualsXmlFile
     */
    public function testAssertXmlStringEqualsXmlFile()
    {
        $this->assertXmlStringEqualsXmlFile(
          $this->filesDirectory . 'foo.xml',
          file_get_contents($this->filesDirectory . 'foo.xml')
        );

        try {
            $this->assertXmlStringEqualsXmlFile(
              $this->filesDirectory . 'foo.xml',
              file_get_contents($this->filesDirectory . 'bar.xml')
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlFile
     */
    public function testXmlStringNotEqualsXmlFile()
    {
        $this->assertXmlStringNotEqualsXmlFile(
          $this->filesDirectory . 'foo.xml',
          file_get_contents($this->filesDirectory . 'bar.xml')
        );

        try {
            $this->assertXmlStringNotEqualsXmlFile(
              $this->filesDirectory . 'foo.xml',
              file_get_contents($this->filesDirectory . 'foo.xml')
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertXmlStringEqualsXmlString
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlString
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertEqualXMLStructure
     */
    public function testXMLStructureIsSame()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureExpected.xml');

        $this->assertEqualXMLStructure(
          $expected->firstChild, $actual->firstChild, TRUE
        );
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertEqualXMLStructure
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     */
    public function testXMLStructureWrongNumberOfAttributes()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureWrongNumberOfAttributes.xml');

        $this->assertEqualXMLStructure(
          $expected->firstChild, $actual->firstChild, TRUE
        );
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertEqualXMLStructure
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     */
    public function testXMLStructureWrongNumberOfNodes()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureWrongNumberOfNodes.xml');

        $this->assertEqualXMLStructure(
          $expected->firstChild, $actual->firstChild, TRUE
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertEqualXMLStructure
     */
    public function testXMLStructureIsSameButDataIsNot()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureIsSameButDataIsNot.xml');

        $this->assertEqualXMLStructure(
          $expected->firstChild, $actual->firstChild, TRUE
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertEqualXMLStructure
     */
    public function testXMLStructureAttributesAreSameButValuesAreNot()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureAttributesAreSameButValuesAreNot.xml');

        $this->assertEqualXMLStructure(
          $expected->firstChild, $actual->firstChild, TRUE
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertEqualXMLStructure
     */
    public function testXMLStructureIgnoreTextNodes()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureIgnoreTextNodes.xml');

        $this->assertEqualXMLStructure(
          $expected->firstChild, $actual->firstChild, TRUE
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertNotEquals
     */
    public function testAssertStringEqualsNumeric2()
    {
        $this->assertNotEquals('A', 0);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertFileExists
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertFileExistsThrowsException()
    {
        $this->assertFileExists(NULL);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertFileExists
     */
    public function testAssertFileExists()
    {
        $this->assertFileExists(__FILE__);

        try {
            $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertFileNotExists
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertFileNotExistsThrowsException()
    {
        $this->assertFileNotExists(NULL);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertFileNotExists
     */
    public function testAssertFileNotExists()
    {
        $this->assertFileNotExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        try {
            $this->assertFileNotExists(__FILE__);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectHasAttribute
     */
    public function testAssertObjectHasAttribute()
    {
        $o = new Author('Terry Pratchett');

        $this->assertObjectHasAttribute('name', $o);

        try {
            $this->assertObjectHasAttribute('foo', $o);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectNotHasAttribute
     */
    public function testAssertObjectNotHasAttribute()
    {
        $o = new Author('Terry Pratchett');

        $this->assertObjectNotHasAttribute('foo', $o);

        try {
            $this->assertObjectNotHasAttribute('name', $o);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNull
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertNotNull
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertTrue
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertFalse
     */
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

    /**
     * @covers            PHPUnit_Framework_Assert::assertRegExp
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertRegExpThrowsException()
    {
        $this->assertRegExp(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertRegExp
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertRegExpThrowsException2()
    {
        $this->assertRegExp('', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertNotRegExp
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotRegExpThrowsException()
    {
        $this->assertNotRegExp(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertNotRegExp
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotRegExpThrowsException2()
    {
        $this->assertNotRegExp('', NULL);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertRegExp
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertNotRegExp
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertSame
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertSame
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertNotSame
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertNotSame
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertNotSame
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertGreaterThan
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeGreaterThan
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertGreaterThanOrEqual
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeGreaterThanOrEqual
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertLessThan
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeLessThan
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertLessThanOrEqual
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeLessThanOrEqual
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::readAttribute
     */
    public function testReadAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertEquals('foo', $this->readAttribute($obj, 'publicAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedAttribute'));
        $this->assertEquals('baz', $this->readAttribute($obj, 'privateAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedParentAttribute'));
        //$this->assertEquals('bar', $this->readAttribute($obj, 'privateParentAttribute'));
    }

    /**
     * @covers PHPUnit_Framework_Assert::readAttribute
     */
    public function testReadAttribute2()
    {
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'publicStaticAttribute'));
        $this->assertEquals('bar', $this->readAttribute('ClassWithNonPublicAttributes', 'protectedStaticAttribute'));
        $this->assertEquals('baz', $this->readAttribute('ClassWithNonPublicAttributes', 'privateStaticAttribute'));
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'protectedStaticParentAttribute'));
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'privateStaticParentAttribute'));
    }

    /**
     * @covers            PHPUnit_Framework_Assert::readAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testReadAttribute3()
    {
        $this->readAttribute('StdClass', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::readAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testReadAttribute4()
    {
        $this->readAttribute('NotExistingClass', 'foo');
    }

    /**
     * @covers            PHPUnit_Framework_Assert::readAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testReadAttribute5()
    {
        $this->readAttribute(NULL, 'foo');
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeContainsOnly
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotContainsOnly
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotContains
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeSame
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotSame
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeEquals
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotEquals
     */
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

    /**
     * @covers            PHPUnit_Framework_Assert::assertClassHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasAttributeThrowsException()
    {
        $this->assertClassHasAttribute(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertClassHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasAttributeThrowsException2()
    {
        $this->assertClassHasAttribute('foo', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertClassNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasAttributeThrowsException()
    {
        $this->assertClassNotHasAttribute(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertClassNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasAttributeThrowsException2()
    {
        $this->assertClassNotHasAttribute('foo', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertClassHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasStaticAttributeThrowsException()
    {
        $this->assertClassHasStaticAttribute(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertClassHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasStaticAttributeThrowsException2()
    {
        $this->assertClassHasStaticAttribute('foo', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertClassNotHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasStaticAttributeThrowsException()
    {
        $this->assertClassNotHasStaticAttribute(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertClassNotHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasStaticAttributeThrowsException2()
    {
        $this->assertClassNotHasStaticAttribute('foo', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertObjectHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectHasAttributeThrowsException()
    {
        $this->assertObjectHasAttribute(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertObjectHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectHasAttributeThrowsException2()
    {
        $this->assertObjectHasAttribute('foo', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertObjectNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectNotHasAttributeThrowsException()
    {
        $this->assertObjectNotHasAttribute(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertObjectNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectNotHasAttributeThrowsException2()
    {
        $this->assertObjectNotHasAttribute('foo', NULL);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertClassHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertClassNotHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertClassHasStaticAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertClassNotHasStaticAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectNotHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectNotHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectNotHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertObjectNotHasAttribute
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::attribute
     * @covers PHPUnit_Framework_Assert::equalTo
     */
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

    /**
     * @covers            PHPUnit_Framework_Assert::assertThat
     * @covers            PHPUnit_Framework_Assert::attribute
     * @covers            PHPUnit_Framework_Assert::equalTo
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertThatAttributeEquals2()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->attribute(
            $this->equalTo('bar'),
            'publicAttribute'
          )
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::attribute
     * @covers PHPUnit_Framework_Assert::equalTo
     */
    public function testAssertThatAttributeEqualTo()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->attributeEqualTo('publicAttribute', 'foo')
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::anything
     */
    public function testAssertThatAnything()
    {
        $this->assertThat('anything', $this->anything());
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::anything
     * @covers PHPUnit_Framework_Assert::logicalAnd
     */
    public function testAssertThatAnythingAndAnything()
    {
        $this->assertThat(
          'anything',
          $this->logicalAnd(
            $this->anything(), $this->anything()
          )
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::anything
     * @covers PHPUnit_Framework_Assert::logicalOr
     */
    public function testAssertThatAnythingOrAnything()
    {
        $this->assertThat(
          'anything',
          $this->logicalOr(
            $this->anything(), $this->anything()
          )
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::anything
     * @covers PHPUnit_Framework_Assert::logicalNot
     * @covers PHPUnit_Framework_Assert::logicalXor
     */
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

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::contains
     */
    public function testAssertThatContains()
    {
        $this->assertThat(array('foo'), $this->contains('foo'));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::stringContains
     */
    public function testAssertThatStringContains()
    {
        $this->assertThat('barfoobar', $this->stringContains('foo'));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::containsOnly
     */
    public function testAssertThatContainsOnly()
    {
        $this->assertThat(array('foo'), $this->containsOnly('string'));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::arrayHasKey
     */
    public function testAssertThatArrayHasKey()
    {
        $this->assertThat(array('foo' => 'bar'), $this->arrayHasKey('foo'));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::classHasAttribute
     */
    public function testAssertThatClassHasAttribute()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->classHasAttribute('publicAttribute')
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::classHasStaticAttribute
     */
    public function testAssertThatClassHasStaticAttribute()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->classHasStaticAttribute('publicStaticAttribute')
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::objectHasAttribute
     */
    public function testAssertThatObjectHasAttribute()
    {
        $this->assertThat(
          new ClassWithNonPublicAttributes,
          $this->objectHasAttribute('publicAttribute')
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::equalTo
     */
    public function testAssertThatEqualTo()
    {
        $this->assertThat('foo', $this->equalTo('foo'));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::identicalTo
     */
    public function testAssertThatIdenticalTo()
    {
        $value      = new StdClass;
        $constraint = $this->identicalTo($value);

        $this->assertThat($value, $constraint);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::isInstanceOf
     */
    public function testAssertThatIsInstanceOf()
    {
        $this->assertThat(new StdClass, $this->isInstanceOf('StdClass'));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::isType
     */
    public function testAssertThatIsType()
    {
        $this->assertThat('string', $this->isType('string'));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::fileExists
     */
    public function testAssertThatFileExists()
    {
        $this->assertThat(__FILE__, $this->fileExists());
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::greaterThan
     */
    public function testAssertThatGreaterThan()
    {
        $this->assertThat(2, $this->greaterThan(1));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::greaterThanOrEqual
     */
    public function testAssertThatGreaterThanOrEqual()
    {
        $this->assertThat(2, $this->greaterThanOrEqual(1));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::lessThan
     */
    public function testAssertThatLessThan()
    {
        $this->assertThat(1, $this->lessThan(2));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::lessThanOrEqual
     */
    public function testAssertThatLessThanOrEqual()
    {
        $this->assertThat(1, $this->lessThanOrEqual(2));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertThat
     * @covers PHPUnit_Framework_Assert::matchesRegularExpression
     */
    public function testAssertThatMatchesRegularExpression()
    {
        $this->assertThat('foobar', $this->matchesRegularExpression('/foo/'));
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagTypeTrue()
    {
        $matcher = array('tag' => 'html');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagTypeFalse()
    {
        $matcher = array('tag' => 'code');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagIdTrue()
    {
        $matcher = array('id' => 'test_text');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagIdFalse()
    {
        $matcher = array('id' => 'test_text_doesnt_exist');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagStringContentTrue()
    {
        $matcher = array('id' => 'test_text',
                         'content' => 'My test tag content');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagStringContentFalse()
    {
        $matcher = array('id' => 'test_text',
                         'content' => 'My non existent tag content');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagRegexpContentTrue()
    {
        $matcher = array('id' => 'test_text',
                         'content' => 'regexp:/test tag/');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagRegexpModifierContentTrue()
    {
        $matcher = array('id' => 'test_text',
                         'content' => 'regexp:/TEST TAG/i');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagRegexpContentFalse()
    {
        $matcher = array('id' => 'test_text',
                         'content' => 'regexp:/asdf/');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesTrueA()
    {
        $matcher = array('tag' => 'span',
                         'attributes' => array('class' => 'test_class'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesTrueB()
    {
        $matcher = array('tag' => 'div',
                         'attributes' => array('id' => 'test_child_id'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAttributesFalse()
    {
        $matcher = array('tag' => 'span',
                         'attributes' => array('class' => 'test_missing_class'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesRegexpTrueA()
    {
        $matcher = array('tag' => 'span',
                         'attributes' => array('class' => 'regexp:/.+_class/'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesRegexpTrueB()
    {
        $matcher = array('tag' => 'div',
                         'attributes' => array('id' => 'regexp:/.+_child_.+/'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesRegexpModifierTrue()
    {
        $matcher = array('tag' => 'div',
                         'attributes' => array('id' => 'regexp:/.+_CHILD_.+/i'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAttributesRegexpModifierFalse()
    {
        $matcher = array('tag' => 'div',
                         'attributes' => array('id' => 'regexp:/.+_CHILD_.+/'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAttributesRegexpFalse()
    {
        $matcher = array('tag' => 'span',
                         'attributes' => array('class' => 'regexp:/.+_missing_.+/'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesMultiPartClassTrueA()
    {
        $matcher = array('tag' => 'div',
                         'id'  => 'test_multi_class',
                         'attributes' => array('class' => 'multi class'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesMultiPartClassTrueB()
    {
        $matcher = array('tag' => 'div',
                         'id'  => 'test_multi_class',
                         'attributes' => array('class' => 'multi'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAttributesMultiPartClassFalse()
    {
        $matcher = array('tag' => 'div',
                         'id'  => 'test_multi_class',
                         'attributes' => array('class' => 'mul'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagParentTrue()
    {
        $matcher = array('tag' => 'head',
                         'parent' => array('tag' => 'html'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagParentFalse()
    {
        $matcher = array('tag' => 'head',
                         'parent' => array('tag' => 'div'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildTrue()
    {
        $matcher = array('tag' => 'html',
                         'child' => array('tag' => 'head'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildFalse()
    {
        $matcher = array('tag' => 'html',
                         'child' => array('tag' => 'div'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAncestorTrue()
    {
        $matcher = array('tag' => 'div',
                         'ancestor' => array('tag' => 'html'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAncestorFalse()
    {
        $matcher = array('tag' => 'html',
                         'ancestor' => array('tag' => 'div'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagDescendantTrue()
    {
        $matcher = array('tag' => 'html',
                         'descendant' => array('tag' => 'div'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagDescendantFalse()
    {
        $matcher = array('tag' => 'div',
                         'descendant' => array('tag' => 'html'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildrenCountTrue()
    {
        $matcher = array('tag' => 'ul',
                         'children' => array('count' => 3));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildrenCountFalse()
    {
        $matcher = array('tag' => 'ul',
                         'children' => array('count' => 5));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildrenLessThanTrue()
    {
        $matcher = array('tag' => 'ul',
                         'children' => array('less_than' => 10));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildrenLessThanFalse()
    {
        $matcher = array('tag' => 'ul',
                         'children' => array('less_than' => 2));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildrenGreaterThanTrue()
    {
        $matcher = array('tag' => 'ul',
                         'children' => array('greater_than' => 2));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildrenGreaterThanFalse()
    {
        $matcher = array('tag' => 'ul',
                         'children' => array('greater_than' => 10));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildrenOnlyTrue()
    {
        $matcher = array('tag' => 'ul',
                         'children' => array('only' => array('tag' =>'li')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildrenOnlyFalse()
    {
        $matcher = array('tag' => 'ul',
                         'children' => array('only' => array('tag' =>'div')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagTypeIdTrueA()
    {
        $matcher = array('tag' => 'ul', 'id' => 'my_ul');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagTypeIdTrueB()
    {
        $matcher = array('id' => 'my_ul', 'tag' => 'ul');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagTypeIdTrueC()
    {
        $matcher = array('tag' => 'input', 'id'  => 'input_test_id');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagTypeIdFalse()
    {
        $matcher = array('tag' => 'div', 'id'  => 'my_ul');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagContentAttributes()
    {
        $matcher = array('tag' => 'div',
                         'content'    => 'Test Id Text',
                         'attributes' => array('id' => 'test_id',
                                               'class' => 'my_test_class'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertParentContentAttributes()
    {
        $matcher = array('tag'        => 'div',
                         'content'    => 'Test Id Text',
                         'attributes' => array('id'    => 'test_id',
                                               'class' => 'my_test_class'),
                         'parent'     => array('tag' => 'body'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertChildContentAttributes()
    {
        $matcher = array('tag'        => 'div',
                         'content'    => 'Test Id Text',
                         'attributes' => array('id'    => 'test_id',
                                               'class' => 'my_test_class'),
                         'child'      => array('tag'        => 'div',
                                               'attributes' => array('id' => 'test_child_id')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertChildSubChildren()
    {
        $matcher = array('id' => 'test_id',
                         'child' => array('id' => 'test_child_id',
                                          'child' => array('id' => 'test_subchild_id')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertAncestorContentAttributes()
    {
        $matcher = array('id'         => 'test_subchild_id',
                         'content'    => 'My Subchild',
                         'attributes' => array('id' => 'test_subchild_id'),
                         'ancestor'   => array('tag'        => 'div',
                                               'attributes' => array('id' => 'test_id')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertDescendantContentAttributes()
    {
        $matcher = array('id'         => 'test_id',
                         'content'    => 'Test Id Text',
                         'attributes' => array('id'  => 'test_id'),
                         'descendant' => array('tag'        => 'span',
                                               'attributes' => array('id' => 'test_subchild_id')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertChildrenContentAttributes()
    {
        $matcher = array('id'         => 'test_children',
                         'content'    => 'My Children',
                         'attributes' => array('class'  => 'children'),

                         'children' => array('less_than'    => '25',
                                             'greater_than' => '2',
                                             'only'         => array('tag' => 'div',
                                                                     'attributes' => array('class' => 'my_child'))
                                            ));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotTag
     */
    public function testAssertNotTagTypeIdFalse()
    {
        $matcher = array('tag' => 'div', 'id'  => 'my_ul');
        $this->assertNotTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertNotTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertNotTagContentAttributes()
    {
        $matcher = array('tag' => 'div',
                         'content'    => 'Test Id Text',
                         'attributes' => array('id' => 'test_id',
                                               'class' => 'my_test_class'));
        $this->assertNotTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountPresentTrue()
    {
        $selector = 'div#test_id';
        $count    = TRUE;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountPresentFalse()
    {
        $selector = 'div#non_existent';
        $count    = TRUE;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountNotPresentTrue()
    {
        $selector = 'div#non_existent';
        $count    = FALSE;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectNotPresentFalse()
    {
        $selector = 'div#test_id';
        $count    = FALSE;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountChildTrue()
    {
        $selector = '#my_ul > li';
        $count    = 3;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountChildFalse()
    {
        $selector = '#my_ul > li';
        $count    = 4;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountDescendantTrue()
    {
        $selector = '#my_ul li';
        $count    = 3;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountDescendantFalse()
    {
        $selector = '#my_ul li';
        $count    = 4;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountGreaterThanTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('>' => 2);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountGreaterThanFalse()
    {
        $selector = '#my_ul > li';
        $range    = array('>' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountGreaterThanEqualToTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('>=' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountGreaterThanEqualToFalse()
    {
        $selector = '#my_ul > li';
        $range    = array('>=' => 4);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountLessThanTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('<' => 4);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountLessThanFalse()
    {
        $selector = '#my_ul > li';
        $range    = array('<' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountLessThanEqualToTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('<=' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountLessThanEqualToFalse()
    {
        $selector = '#my_ul > li';
        $range  = array('<=' => 2);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountRangeTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('>' => 2, '<' => 4);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountRangeFalse()
    {
        $selector = '#my_ul > li';
        $range    = array('>' => 1, '<' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectEquals
     */
    public function testAssertSelectEqualsContentPresentTrue()
    {
        $selector = 'span.test_class';
        $content  = 'Test Class Text';

        $this->assertSelectEquals($selector, $content, TRUE, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectEquals
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectEqualsContentPresentFalse()
    {
        $selector = 'span.test_class';
        $content  = 'Test Nonexistent';

        $this->assertSelectEquals($selector, $content, TRUE, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectEquals
     */
    public function testAssertSelectEqualsContentNotPresentTrue()
    {
        $selector = 'span.test_class';
        $content  = 'Test Nonexistent';

        $this->assertSelectEquals($selector, $content, FALSE, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectEquals
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectEqualsContentNotPresentFalse()
    {
        $selector = 'span.test_class';
        $content  = 'Test Class Text';

        $this->assertSelectEquals($selector, $content, FALSE, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectRegExp
     */
    public function testAssertSelectRegExpContentPresentTrue()
    {
        $selector = 'span.test_class';
        $regexp   = '/Test.*Text/';

        $this->assertSelectRegExp($selector, $regexp, TRUE, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectRegExp
     */
    public function testAssertSelectRegExpContentPresentFalse()
    {
        $selector = 'span.test_class';
        $regexp   = '/Nonexistant/';

        $this->assertSelectRegExp($selector, $regexp, FALSE, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertFileEquals
     */
    public function testAssertFileEquals()
    {
        $this->assertFileEquals(
          $this->filesDirectory . 'foo.xml',
          $this->filesDirectory . 'foo.xml'
        );

        try {
            $this->assertFileEquals(
              $this->filesDirectory . 'foo.xml',
              $this->filesDirectory . 'bar.xml'
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertFileNotEquals
     */
    public function testAssertFileNotEquals()
    {
        $this->assertFileNotEquals(
          $this->filesDirectory . 'foo.xml',
          $this->filesDirectory . 'bar.xml'
        );

        try {
            $this->assertFileNotEquals(
              $this->filesDirectory . 'foo.xml',
              $this->filesDirectory . 'foo.xml'
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertStringEqualsFile
     */
    public function testAssertStringEqualsFile()
    {
        $this->assertStringEqualsFile(
          $this->filesDirectory . 'foo.xml',
          file_get_contents($this->filesDirectory . 'foo.xml')
        );

        try {
            $this->assertStringEqualsFile(
              $this->filesDirectory . 'foo.xml',
              file_get_contents($this->filesDirectory . 'bar.xml')
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertStringNotEqualsFile
     */
    public function testAssertStringNotEqualsFile()
    {
        $this->assertStringNotEqualsFile(
          $this->filesDirectory . 'foo.xml',
          file_get_contents($this->filesDirectory . 'bar.xml')
        );

        try {
            $this->assertStringNotEqualsFile(
              $this->filesDirectory . 'foo.xml',
              file_get_contents($this->filesDirectory . 'foo.xml')
            );
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertStringStartsWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringStartsWithThrowsException()
    {
        $this->assertStringStartsWith(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertStringStartsWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringStartsWithThrowsException2()
    {
        $this->assertStringStartsWith('', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertStringStartsNotWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringStartsNotWithThrowsException()
    {
        $this->assertStringStartsNotWith(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertStringStartsNotWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringStartsNotWithThrowsException2()
    {
        $this->assertStringStartsNotWith('', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertStringEndsWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringEndsWithThrowsException()
    {
        $this->assertStringEndsWith(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertStringEndsWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringEndsWithThrowsException2()
    {
        $this->assertStringEndsWith('', NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertStringEndsNotWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringEndsNotWithThrowsException()
    {
        $this->assertStringEndsNotWith(NULL, NULL);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertStringEndsNotWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringEndsNotWithThrowsException2()
    {
        $this->assertStringEndsNotWith('', NULL);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertStringStartsWith
     */
    public function testAssertStringStartsWith()
    {
        $this->assertStringStartsWith('prefix', 'prefixfoo');

        try {
            $this->assertStringStartsWith('prefix', 'foo');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertStringStartsNotWith
     */
    public function testAssertStringStartsNotWith()
    {
        $this->assertStringStartsNotWith('prefix', 'foo');

        try {
            $this->assertStringStartsNotWith('prefix', 'prefixfoo');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertStringEndsWith
     */
    public function testAssertStringEndsWith()
    {
        $this->assertStringEndsWith('suffix', 'foosuffix');

        try {
            $this->assertStringEndsWith('suffix', 'foo');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertStringEndsNotWith
     */
    public function testAssertStringEndsNotWith()
    {
        $this->assertStringEndsNotWith('suffix', 'foo');

        try {
            $this->assertStringEndsNotWith('suffix', 'foosuffix');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertStringMatchesFormat
     */
    public function testAssertStringMatchesFormat()
    {
        $this->assertStringMatchesFormat('*%s*', '***');

        try {
            $this->assertStringMatchesFormat('*%s*', '**');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertStringNotMatchesFormat
     */
    public function testAssertStringNotMatchesFormat()
    {
        $this->assertStringNotMatchesFormat('*%s*', '**');

        try {
            $this->assertStringMatchesFormat('*%s*', '**');
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertEmpty
     */
    public function testAssertEmpty()
    {
        $this->assertEmpty(array());

        try {
            $this->assertEmpty(array('foo'));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotEmpty
     */
    public function testAssertNotEmpty()
    {
        $this->assertNotEmpty(array('foo'));

        try {
            $this->assertNotEmpty(array());
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeEmpty
     */
    public function testAssertAttributeEmpty()
    {
        $o    = new StdClass;
        $o->a = array();

        $this->assertAttributeEmpty('a', $o);

        try {
            $o->a = array('b');
            $this->assertAttributeEmpty('a', $o);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertAttributeNotEmpty
     */
    public function testAssertAttributeNotEmpty()
    {
        $o    = new StdClass;
        $o->a = array('b');

        $this->assertAttributeNotEmpty('a', $o);

        try {
            $o->a = array();
            $this->assertAttributeNotEmpty('a', $o);
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::markTestIncomplete
     */
    public function testMarkTestIncomplete()
    {
        try {
            $this->markTestIncomplete('incomplete');
        }

        catch (PHPUnit_Framework_IncompleteTestError $e) {
            $this->assertEquals('incomplete', $e->getMessage());

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::markTestSkipped
     */
    public function testMarkTestSkipped()
    {
        try {
            $this->markTestSkipped('skipped');
        }

        catch (PHPUnit_Framework_SkippedTestError $e) {
            $this->assertEquals('skipped', $e->getMessage());

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertCount
     */
    public function testAssertCount()
    {
        $this->assertCount(2, array(1,2));

        try {
            $this->assertCount(2, array(1,2,3));
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertCount
     */
    public function testAssertCountThrowsExceptionIfExpectedCountIsNoInteger()
    {

        try {
            $this->assertCount('a', array());
        }

        catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #1 of PHPUnit_Framework_Assert::assertCount() must be a integer', $e->getMessage());

            return;
        }

        $this->fail();
    }


    /**
     * @covers PHPUnit_Framework_Assert::assertCount
     */
    public function testAssertCountThrowsExceptionIfElementIsNotCountable()
    {

        try {
            $this->assertCount(2, '');
        }

        catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #2 of PHPUnit_Framework_Assert::assertCount() must be a countable', $e->getMessage());

            return;
        }

        $this->fail();
    }
}
