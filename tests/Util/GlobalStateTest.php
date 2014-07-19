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
 * @author     Blake Williams <code@shabbyrobe.org>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 */

/**
 *
 *
 * @package    PHPUnit
 * @author     Blake Williams <code@shabbyrobe.org>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 */
class Util_GlobalStateTest extends PHPUnit_Framework_TestCase
{
    public function validExportVariableProvider()
    {
        return array(
            array("Hello"),
            array(1),
            array(1234.56),
            array(0),
            array(true),
            array(false),
            array(null),
            array(array("foo")),
            array(array(array("foo"))),
            array((object)array("foo"=>"bar")),
            array((object)array("foo"=>"back\\slash")),
            array((object)array("foo"=>"back\\\\slash")),
        );
    }

    /**
     * @dataProvider validExportVariableProvider
     * @covers       PHPUnit_Util_GlobalState::exportVariable
     */
    public function testValidExportVariable($value)
    {
        $method = new ReflectionMethod('PHPUnit_Util_GlobalState', 'exportVariable');
        $method->setAccessible(true);
        $exported = $method->invoke(null, $value);
        if ($exported === false)
            throw new UnexpectedValueException();
            
        eval('$result = '.$exported.';');
        if (is_object($value))
            $this->assertEquals($value, $result);
        else
            $this->assertSame($value, $result);
    }
}
