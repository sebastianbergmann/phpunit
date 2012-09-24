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
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.3.0
 */

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */
class Util_TestDox_NamePrettifierTest extends PHPUnit_Framework_TestCase
{
    protected $namePrettifier;

    protected function setUp()
    {
        $this->namePrettifier = new PHPUnit_Util_TestDox_NamePrettifier;
    }

    public function testTitleHasSensibleDefaults()
    {
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('FooTest'));
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('TestFoo'));
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('TestFooTest'));
    }

    public function testCaterForUserDefinedSuffix()
    {
        $this->namePrettifier->setSuffix('TestCase');
        $this->namePrettifier->setPrefix(NULL);

        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('FooTestCase'));
        $this->assertEquals('TestFoo', $this->namePrettifier->prettifyTestClass('TestFoo'));
        $this->assertEquals('FooTest', $this->namePrettifier->prettifyTestClass('FooTest'));
    }

    public function testCaterForUserDefinedPrefix()
    {
        $this->namePrettifier->setSuffix(NULL);
        $this->namePrettifier->setPrefix('XXX');

        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('XXXFoo'));
        $this->assertEquals('TestXXX', $this->namePrettifier->prettifyTestClass('TestXXX'));
        $this->assertEquals('XXX', $this->namePrettifier->prettifyTestClass('XXXXXX'));
    }

    public function testTestNameIsConvertedToASentence()
    {
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethod('testThisIsATest'));
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethod('testThisIsATest2'));
        $this->assertEquals('this is a test', $this->namePrettifier->prettifyTestMethod('this_is_a_test'));
        $this->assertEquals('Foo for bar is 0', $this->namePrettifier->prettifyTestMethod('testFooForBarIs0'));
        $this->assertEquals('Foo for baz is 1', $this->namePrettifier->prettifyTestMethod('testFooForBazIs1'));
    }

    /**
     * @ticket 224
     */
    public function testTestNameIsNotGroupedWhenNotInSequence()
    {
        $this->assertEquals('Sets redirect header on 301', $this->namePrettifier->prettifyTestMethod('testSetsRedirectHeaderOn301'));
        $this->assertEquals('Sets redirect header on 302', $this->namePrettifier->prettifyTestMethod('testSetsRedirectHeaderOn302'));
    }
}
