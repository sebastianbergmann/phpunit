<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 */
class Util_GetoptTest extends PHPUnit_Framework_TestCase
{
    public function testItIncludeTheLongOptionsAfterTheArgument()
    {
        $args = array(
            'command',
            'myArgument',
            '--colors',
        );
        $actual = PHPUnit_Util_Getopt::getopt($args, '', array('colors=='));

        $expected = array(
            array(
                array(
                    '--colors',
                    null,
                ),
            ),
            array(
                'myArgument',
            ),
        );

        $this->assertEquals($expected, $actual);
    }

    public function testItIncludeTheShortOptionsAfterTheArgument()
    {
        $args = array(
            'command',
            'myArgument',
            '-v',
        );
        $actual = PHPUnit_Util_Getopt::getopt($args, 'v');

        $expected = array(
            array(
                array(
                    'v',
                    null,
                ),
            ),
            array(
                'myArgument',
            ),
        );

        $this->assertEquals($expected, $actual);
    }

    public function testShortOptionUnrecognizedException()
    {
        $args = [
            'command',
            'myArgument',
            '-v',
        ];

        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage('unrecognized option -- v');

        Getopt::getopt($args, '');
    }

    public function testShortOptionRequiresAnArgumentException()
    {
        $args = [
            'command',
            'myArgument',
            '-f',
        ];

        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage('option requires an argument -- f');

        Getopt::getopt($args, 'f:');
    }

    public function testShortOptionHandleAnOptionalValue()
    {
        $args = [
            'command',
            'myArgument',
            '-f',
        ];
        $actual   = Getopt::getopt($args, 'f::');
        $expected = [
            [
                [
                    'f',
                    null,
                ],
            ],
            [
                'myArgument',
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testLongOptionIsAmbiguousException()
    {
        $args = [
            'command',
            '--col',
        ];

        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage('option --col is ambiguous');

        Getopt::getopt($args, '', ['columns', 'colors']);
    }

    public function testLongOptionUnrecognizedException()
    {
        // the exception 'unrecognized option --option' is not thrown
        // if the there are not defined extended options
        $args = [
            'command',
            '--foo',
        ];

        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage('unrecognized option --foo');

        Getopt::getopt($args, '', ['colors']);
    }

    public function testLongOptionRequiresAnArgumentException()
    {
        $args = [
            'command',
            '--foo',
        ];

        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage('option --foo requires an argument');

        Getopt::getopt($args, '', ['foo=']);
    }

    public function testLongOptionDoesNotAllowAnArgumentException()
    {
        $args = [
            'command',
            '--foo=bar',
        ];

        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage("option --foo doesn't allow an argument");

        Getopt::getopt($args, '', ['foo']);
    }
}
