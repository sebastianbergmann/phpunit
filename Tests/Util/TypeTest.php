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
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.6.0
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'PHPUnit/Util/Type.php';

/**
 *
 *
 * @package    PHPUnit
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.6.0
 */
class Util_TypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Removes spaces in front newlines
     *
     * @param  string $string
     * @return string
     */
    public static function trimnl($string)
    {
        return preg_replace('/[ ]*\n/', "\n", $string);
    }

    public function exportProvider()
    {
        $obj2 = new stdClass;
        $obj2->foo = 'bar';

        $obj = new stdClass;
        //@codingStandardsIgnoreStart 
        $obj->null = NULL;
        //@codingStandardsIgnoreEnd 
        $obj->boolean = TRUE;
        $obj->integer = 1;
        $obj->double = 1.2;
        $obj->string = '1';
        $obj->text = "this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext";
        $obj->object = $obj2;
        $obj->objectagain = $obj2;
        $obj->array = array('foo' => 'bar');
        $obj->self = $obj;

        $array = array(
            0 => 0,
            'null' => NULL,
            'boolean' => TRUE,
            'integer' => 1,
            'double' => 1.2,
            'string' => '1',
            'text' => "this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext",
            'object' => $obj2,
            'objectagain' => $obj2,
            'array' => array('foo' => 'bar'),
        );

        $array['self'] = &$array;

        return array(
            array(NULL, 'null'),
            array(TRUE, 'true'),
            array(1, '1'),
            array(1.0, '1.0'),
            array(1.2, '1.2'),
            array('1', "'1'"),
            // \n\r and \r is converted to \n
            array("this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext",
<<<EOF
'this
is
a
very
very
very
very
very
very
long
text'
EOF
            ),
            array(new stdClass, 'stdClass Object ()'),
            array($obj,
<<<EOF
stdClass Object (
    'null' => null
    'boolean' => true
    'integer' => 1
    'double' => 1.2
    'string' => '1'
    'text' => 'this
is
a
very
very
very
very
very
very
long
text'
    'object' => stdClass Object (
        'foo' => 'bar'
    )
    'objectagain' => stdClass Object (*RECURSION*)
    'array' => Array (
        'foo' => 'bar'
    )
    'self' => stdClass Object (*RECURSION*)
)
EOF
            ),
            array(array(), 'Array ()'),
            array($array,
<<<EOF
Array (
    0 => 0
    'null' => null
    'boolean' => true
    'integer' => 1
    'double' => 1.2
    'string' => '1'
    'text' => 'this
is
a
very
very
very
very
very
very
long
text'
    'object' => stdClass Object (
        'foo' => 'bar'
    )
    'objectagain' => stdClass Object (*RECURSION*)
    'array' => Array (
        'foo' => 'bar'
    )
    'self' => Array (*RECURSION*)
)
EOF
            ),
            array(
                chr(0) . chr(1) . chr(2) . chr(3) . chr(4) . chr(5),
                'Binary String: 0x000102030405'
            ),
            array(
                implode('', array_map('chr', range(0x0e, 0x1f))),
                'Binary String: 0x0e0f101112131415161718191a1b1c1d1e1f'
            ),
            array(
                chr(0x00) . chr(0x09),
                'Binary String: 0x0009'
            ),
            array(
                '',
                "''"
            ),
        );
    }

    /**
     * @dataProvider exportProvider
     */
    public function testExport($value, $expected)
    {
        $this->assertSame($expected, self::trimnl(PHPUnit_Util_Type::export($value)));
    }

    public function shortenedExportProvider()
    {
        $obj = new stdClass;
        $obj->foo = 'bar';

        $array = array(
            'foo' => 'bar',
        );

        return array(
            array(NULL, 'null'),
            array(TRUE, 'true'),
            array(1, '1'),
            array(1.0, '1.0'),
            array(1.2, '1.2'),
            array('1', "'1'"),
            // \n\r and \r is converted to \n
            array("this\nis\na\nvery\nvery\nvery\nvery\nvery\nvery\rlong\n\rtext", "'this\\nis\\na\\nvery\\nvery\\nvery\\nvery...g\\ntext'"),
            array(new stdClass, 'stdClass Object ()'),
            array($obj, 'stdClass Object (...)'),
            array(array(), 'Array ()'),
            array($array, 'Array (...)'),
        );
    }

    /**
     * @dataProvider shortenedExportProvider
     */
    public function testShortenedExport($value, $expected)
    {
        $this->assertSame($expected, self::trimnl(PHPUnit_Util_Type::shortenedExport($value)));
    }

    public function provideNonBinaryMultibyteStrings()
    {
        return array(
            array(implode('', array_map('chr', range(0x09, 0x0d))), 5),
            array(implode('', array_map('chr', range(0x20, 0x7f))), 96),
            array(implode('', array_map('chr', range(0x80, 0xff))), 128),
        );
    }


    /**
     * @dataProvider provideNonBinaryMultibyteStrings
     */
    public function testNonBinaryStringExport($value, $expectedLength)
    {
        $this->assertRegExp("~'.{{$expectedLength}}'\$~s", PHPUnit_Util_Type::export($value));
    }
}
