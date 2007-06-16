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
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'PHPUnit/Util/TestDox/NamePrettifier.php';

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
 * @since      Class available since Release 2.1.0
 */
class Util_TestDox_NamePrettifierTest extends PHPUnit_Framework_TestCase
{
    private $namePrettifier;

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
        $this->assertEquals('This2 is a test', $this->namePrettifier->prettifyTestMethod('testThis2IsATest'));
        $this->assertEquals('database_column_spec is set correctly', $this->namePrettifier->prettifyTestMethod('testdatabase_column_specIsSetCorrectly'));
    }
}
?>
