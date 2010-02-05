<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.6
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'PHPUnit/Util/Timer.php';

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.6
 */
class Util_TestTest extends PHPUnit_Framework_TestCase
{
    public function testGetExpectedException()
    {
        $this->assertEquals(
          array('class' => 'FooBarBaz', 'code' => 0, 'message' => ''),
          PHPUnit_Util_Test::getExpectedException('@expectedException FooBarBaz')
        );

        $this->assertEquals(
          array('class' => 'Foo_Bar_Baz', 'code' => 0, 'message' => ''),
          PHPUnit_Util_Test::getExpectedException('@expectedException Foo_Bar_Baz')
        );

        $this->assertEquals(
          array('class' => 'Foo\Bar\Baz', 'code' => 0, 'message' => ''),
          PHPUnit_Util_Test::getExpectedException('@expectedException Foo\Bar\Baz')
        );

        $this->assertEquals(
          array('class' => 'ほげ', 'code' => 0, 'message' => ''),
          PHPUnit_Util_Test::getExpectedException('@expectedException ほげ')
        );
    }

    public function testGetProvidedDataRegEx()
    {
        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('method', $matches[1]);

        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('class::method', $matches[1]);

        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\class::method', $matches[1]);

        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider namespace\namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\namespace\class::method', $matches[1]);

        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider メソッド', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('メソッド', $matches[1]);
    }

    public function testParseAnnotation()
    {
        $this->assertEquals(
          array('Foo', 'ほげ'),
          PHPUnit_Util_Test::getDependencies(get_class($this), 'methodForTestParseAnnotation')
        );
    }

    /**
     * @depends Foo
     * @depends ほげ
     */
    public function methodForTestParseAnnotation() {}
}
