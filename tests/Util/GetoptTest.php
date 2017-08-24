<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Exception;

class GetoptTest extends TestCase
{
    public function testItIncludeTheLongOptionsAfterTheArgument()
    {
        $args = [
            'command',
            'myArgument',
            '--colors',
        ];
        $actual = Getopt::getopt($args, '', ['colors==']);

        $expected = [
            [
                [
                    '--colors',
                    null,
                ],
            ],
            [
                'myArgument',
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testItIncludeTheShortOptionsAfterTheArgument()
    {
        $args = [
            'command',
            'myArgument',
            '-v',
        ];
        $actual = Getopt::getopt($args, 'v');

        $expected = [
            [
                [
                    'v',
                    null,
                ],
            ],
            [
                'myArgument',
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testShortOptionUnrecognizedException()
    {
        $args = [
            'command',
            'myArgument',
            '-v',
        ];

        $this->expectException(Exception::class);
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

        $this->expectException(Exception::class);
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

        $this->expectException(Exception::class);
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('unrecognized option --foo');

        Getopt::getopt($args, '', ['colors']);
    }

    public function testLongOptionRequiresAnArgumentException()
    {
        $args = [
            'command',
            '--foo',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('option --foo requires an argument');

        Getopt::getopt($args, '', ['foo=']);
    }

    public function testLongOptionDoesNotAllowAnArgumentException()
    {
        $args = [
            'command',
            '--foo=bar',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("option --foo doesn't allow an argument");

        Getopt::getopt($args, '', ['foo']);
    }

    public function testItHandlesLongParametesWithValues()
    {
        $command = 'command parameter-0 --exec parameter-1 --conf config.xml --optn parameter-2 --optn=content-of-o parameter-n';
        $args    = \explode(' ', $command);
        unset($args[0]);
        $expected = [
            [
                ['--exec', null],
                ['--conf', 'config.xml'],
                ['--optn', null],
                ['--optn', 'content-of-o'],
            ],
            [
                'parameter-0',
                'parameter-1',
                'parameter-2',
                'parameter-n',
            ],
        ];
        $actual = Getopt::getopt($args, '', ['exec', 'conf=', 'optn==']);
        $this->assertEquals($expected, $actual);
    }

    public function testItHandlesShortParametesWithValues()
    {
        $command = 'command parameter-0 -x parameter-1 -c config.xml -o parameter-2 -ocontent-of-o parameter-n';
        $args    = \explode(' ', $command);
        unset($args[0]);
        $expected = [
            [
                ['x', null],
                ['c', 'config.xml'],
                ['o', null],
                ['o', 'content-of-o'],
            ],
            [
                'parameter-0',
                'parameter-1',
                'parameter-2',
                'parameter-n',
            ],
        ];
        $actual = Getopt::getopt($args, 'xc:o::');
        $this->assertEquals($expected, $actual);
    }
}
