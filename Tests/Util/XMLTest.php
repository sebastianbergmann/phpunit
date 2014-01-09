<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Mike Naberezny <mike@maintainable.com>
 * @author     Derek DeVries <derek@maintainable.com>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.0
 */

/**
 *
 *
 * @package    PHPUnit
 * @author     Mike Naberezny <mike@maintainable.com>
 * @author     Derek DeVries <derek@maintainable.com>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.0
 */
class Util_XMLTest extends PHPUnit_Framework_TestCase
{
    public function testAssertValidKeysValidKeys()
    {
        $options   = array('testA' => 1, 'testB' => 2, 'testC' => 3);
        $valid     = array('testA', 'testB', 'testC');
        $expected  = array('testA' => 1, 'testB' => 2, 'testC' => 3);
        $validated = PHPUnit_Util_XML::assertValidKeys($options, $valid);

        $this->assertEquals($expected, $validated);
    }

    public function testAssertValidKeysValidKeysEmpty()
    {
        $options   = array('testA' => 1, 'testB' => 2);
        $valid     = array('testA', 'testB', 'testC');
        $expected  = array('testA' => 1, 'testB' => 2, 'testC' => NULL);
        $validated = PHPUnit_Util_XML::assertValidKeys($options, $valid);

        $this->assertEquals($expected, $validated);
    }

    public function testAssertValidKeysDefaultValuesA()
    {
        $options   = array('testA' => 1, 'testB' => 2);
        $valid     = array('testA' => 23, 'testB' => 24, 'testC' => 25);
        $expected  = array('testA' => 1, 'testB' => 2, 'testC' => 25);
        $validated = PHPUnit_Util_XML::assertValidKeys($options, $valid);

        $this->assertEquals($expected, $validated);
    }

    public function testAssertValidKeysDefaultValuesB()
    {
        $options   = array();
        $valid     = array('testA' => 23, 'testB' => 24, 'testC' => 25);
        $expected  = array('testA' => 23, 'testB' => 24, 'testC' => 25);
        $validated = PHPUnit_Util_XML::assertValidKeys($options, $valid);

        $this->assertEquals($expected, $validated);
    }

    public function testAssertValidKeysInvalidKey()
    {
        $options = array('testA' => 1, 'testB' => 2, 'testD' => 3);
        $valid   = array('testA', 'testB', 'testC');

        try {
            $validated = PHPUnit_Util_XML::assertValidKeys($options, $valid);
            $this->fail();
        }

        catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Unknown key(s): testD', $e->getMessage());
        }
    }

    public function testAssertValidKeysInvalidKeys()
    {
        $options = array('testA' => 1, 'testD' => 2, 'testE' => 3);
        $valid   = array('testA', 'testB', 'testC');

        try {
            $validated = PHPUnit_Util_XML::assertValidKeys($options, $valid);
            $this->fail();
        }

        catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Unknown key(s): testD, testE', $e->getMessage());
        }
    }

    public function testConvertAssertSelect()
    {
        $selector  = 'div#folder.open a[href="http://www.xerox.com"][title="xerox"].selected.big > span';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag'   => 'div',
                           'id'    => 'folder',
                           'class' => 'open',
                           'descendant' => array('tag'        => 'a',
                                                 'class'      => 'selected big',
                                                 'attributes' => array('href'  => 'http://www.xerox.com',
                                                                       'title' => 'xerox'),
                                                 'child'      => array('tag' => 'span')));
         $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectElt()
    {
        $selector  = 'div';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag' => 'div');

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertClass()
    {
        $selector  = '.foo';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('class' => 'foo');

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertId()
    {
        $selector  = '#foo';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('id' => 'foo');

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertAttribute()
    {
        $selector  = '[foo="bar"]';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('attributes' => array('foo' => 'bar'));

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertAttributeSpaces()
    {
        $selector  = '[foo="bar baz"] div[value="foo bar"]';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('attributes' => array('foo' => 'bar baz'),
                           'descendant' => array('tag'        => 'div',
                                                 'attributes' => array('value' => 'foo bar')));
        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertAttributeMultipleSpaces()
    {
        $selector = '[foo="bar baz"] div[value="foo bar baz"]';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag      = array('attributes' => array('foo' => 'bar baz'),
                          'descendant' => array('tag' => 'div',
                                                'attributes' => array('value' => 'foo bar baz')));
        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectEltClass()
    {
        $selector  = 'div.foo';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag' => 'div', 'class' => 'foo');

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectEltId()
    {
        $selector  = 'div#foo';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag' => 'div', 'id' => 'foo');

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectEltAttrEqual()
    {
        $selector  = 'div[foo="bar"]';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag' => 'div', 'attributes' => array('foo' => 'bar'));

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectEltMultiAttrEqual()
    {
        $selector  = 'div[foo="bar"][baz="fob"]';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag' => 'div', 'attributes' => array('foo' => 'bar', 'baz' => 'fob'));

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectEltAttrHasOne()
    {
        $selector  = 'div[foo~="bar"]';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag' => 'div', 'attributes' => array('foo' => 'regexp:/.*\bbar\b.*/'));

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectEltAttrContains()
    {
        $selector  = 'div[foo*="bar"]';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag' => 'div', 'attributes' => array('foo' => 'regexp:/.*bar.*/'));

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectEltChild()
    {
        $selector  = 'div > a';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag' => 'div', 'child' => array('tag' => 'a'));

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectEltDescendant()
    {
        $selector  = 'div a';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector);
        $tag       = array('tag' => 'div', 'descendant' => array('tag' => 'a'));

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectContent()
    {
        $selector  = '#foo';
        $content   = 'div contents';
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector, $content);
        $tag       = array('id' => 'foo', 'content' => 'div contents');

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectTrue()
    {
        $selector  = '#foo';
        $content   = TRUE;
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector, $content);
        $tag       = array('id' => 'foo');

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertSelectFalse()
    {
        $selector  = '#foo';
        $content   = FALSE;
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector, $content);
        $tag       = array('id' => 'foo');

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertNumber()
    {
        $selector  = '.foo';
        $content   = 3;
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector, $content);
        $tag       = array('class' => 'foo');

        $this->assertEquals($tag, $converted);
    }

    public function testConvertAssertRange()
    {
        $selector  = '#foo';
        $content   = array('greater_than' => 5, 'less_than' => 10);
        $converted = PHPUnit_Util_XML::convertSelectToTag($selector, $content);
        $tag       = array('id' => 'foo');

        $this->assertEquals($tag, $converted);
    }

    public function testPrepareStringEscapesChars()
    {
        $this->assertEquals('&#x1b;', PHPUnit_Util_XML::prepareString("\033"));
    }
}
