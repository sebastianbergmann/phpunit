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
        $args = array(
            'command',
            'myArgument',
            '-v',
        );

        try {
            PHPUnit_Util_Getopt::getopt($args, '');
            $exception = null;
        } catch (Exception $x) {
            $exception = $x;
        }
        $this->assertNotNull($exception);
        $this->assertInstanceOf('PHPUnit_Framework_Exception', $exception);
        $this->assertSame('unrecognized option -- v', $exception->getMessage());
    }

    public function testShortOptionRequiresAnArgumentException()
    {
        $args = array(
            'command',
            'myArgument',
            '-f',
        );

        try {
            PHPUnit_Util_Getopt::getopt($args, 'f:');
            $exception = null;
        } catch (Exception $x) {
            $exception = $x;
        }
        $this->assertNotNull($exception);
        $this->assertInstanceOf('PHPUnit_Framework_Exception', $exception);
        $this->assertSame('option requires an argument -- f', $exception->getMessage());
    }

    public function testShortOptionHandleAnOptionalValue()
    {
        $args = array(
            'command',
            'myArgument',
            '-f',
        );
        $actual   = PHPUnit_Util_Getopt::getopt($args, 'f::');
        $expected = array(
            array(
                array(
                    'f',
                    null,
                ),
            ),
            array(
                'myArgument',
            ),
        );
        $this->assertEquals($expected, $actual);
    }

    public function testLongOptionIsAmbiguousException()
    {
        $args = array(
            'command',
            '--col',
        );

        try {
            PHPUnit_Util_Getopt::getopt($args, '', array('columns', 'colors'));
            $exception = null;
        } catch (Exception $x) {
            $exception = $x;
        }
        $this->assertNotNull($exception);
        $this->assertInstanceOf('PHPUnit_Framework_Exception', $exception);
        $this->assertSame('option --col is ambiguous', $exception->getMessage());
    }

    public function testLongOptionUnrecognizedException()
    {
        // the exception 'unrecognized option --option' is not thrown
        // if the there are not defined extended options
        $args = array(
            'command',
            '--foo',
        );

        try {
            PHPUnit_Util_Getopt::getopt($args, '', array('colors'));
            $exception = null;
        } catch (Exception $x) {
            $exception = $x;
        }
        $this->assertNotNull($exception);
        $this->assertInstanceOf('PHPUnit_Framework_Exception', $exception);
        $this->assertSame('unrecognized option --foo', $exception->getMessage());
    }

    public function testLongOptionRequiresAnArgumentException()
    {
        $args = array(
            'command',
            '--foo',
        );

        try {
            PHPUnit_Util_Getopt::getopt($args, '', array('foo='));
            $exception = null;
        } catch (Exception $x) {
            $exception = $x;
        }
        $this->assertNotNull($exception);
        $this->assertInstanceOf('PHPUnit_Framework_Exception', $exception);
        $this->assertSame('option --foo requires an argument', $exception->getMessage());
    }

    public function testLongOptionDoesNotAllowAnArgumentException()
    {
        $args = array(
            'command',
            '--foo=bar',
        );

        try {
            PHPUnit_Util_Getopt::getopt($args, '', array('foo'));
            $exception = null;
        } catch (Exception $x) {
            $exception = $x;
        }
        $this->assertNotNull($exception);
        $this->assertInstanceOf('PHPUnit_Framework_Exception', $exception);
        $this->assertSame("option --foo doesn't allow an argument", $exception->getMessage());
    }

    public function testItHandlesLongParametesWithValues()
    {
        $command = implode(' ', array(
            'command',
            'parameter-0',
            '--exec',
            'parameter-1',
            '--conf',
            'config.xml',
            '--optn',
            'parameter-2',
            '--optn=content-of-o',
            'parameter-n',
        ));
        $args    = explode(' ', $command);
        unset($args[0]);
        $expected = array(
            array(
                array('--exec', null),
                array('--conf', 'config.xml'),
                array('--optn', null),
                array('--optn', 'content-of-o'),
            ),
            array(
                'parameter-0',
                'parameter-1',
                'parameter-2',
                'parameter-n',
            ),
        );
        $actual = PHPUnit_Util_Getopt::getopt($args, '', array('exec', 'conf=', 'optn=='));
        $this->assertEquals($expected, $actual);
    }

    public function testItHandlesShortParametesWithValues()
    {
        $command = 'command parameter-0 -x parameter-1 -c config.xml -o parameter-2 -ocontent-of-o parameter-n';
        $args    = explode(' ', $command);
        unset($args[0]);
        $expected = array(
            array(
                array('x', null),
                array('c', 'config.xml'),
                array('o', null),
                array('o', 'content-of-o'),
            ),
            array(
                'parameter-0',
                'parameter-1',
                'parameter-2',
                'parameter-n',
            ),
        );
        $actual = PHPUnit_Util_Getopt::getopt($args, 'xc:o::');
        $this->assertEquals($expected, $actual);
    }
}
