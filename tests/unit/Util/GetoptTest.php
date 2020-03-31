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

/**
 * @small
 * @covers \PHPUnit\Util\Getopt
 */
final class GetoptTest extends TestCase
{
    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseLongOption
     */
    public function testItIncludeTheLongOptionsAfterTheArgument(): void
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

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseShortOption
     */
    public function testItIncludeTheShortOptionsAfterTheArgument(): void
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

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     */
    public function testShortOptionUnrecognizedException(): void
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

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseShortOption
     */
    public function testShortOptionRequiresAnArgumentException(): void
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

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseShortOption
     */
    public function testShortOptionHandleAnOptionalValue(): void
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

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseLongOption
     */
    public function testLongOptionIsAmbiguousException(): void
    {
        $args = [
            'command',
            '--col',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('option --col is ambiguous');

        Getopt::getopt($args, '', ['columns', 'colors']);
    }

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseLongOption
     */
    public function testLongOptionUnrecognizedException(): void
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

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseLongOption
     */
    public function testLongOptionRequiresAnArgumentException(): void
    {
        $args = [
            'command',
            '--foo',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('option --foo requires an argument');

        Getopt::getopt($args, '', ['foo=']);
    }

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseLongOption
     */
    public function testLongOptionDoesNotAllowAnArgumentException(): void
    {
        $args = [
            'command',
            '--foo=bar',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("option --foo doesn't allow an argument");

        Getopt::getopt($args, '', ['foo']);
    }

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseLongOption
     */
    public function testItHandlesLongParametesWithValues(): void
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

    /**
     * @covers \PHPUnit\Util\Getopt::getopt
     * @covers \PHPUnit\Util\Getopt::parseShortOption
     */
    public function testItHandlesShortParametesWithValues(): void
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
