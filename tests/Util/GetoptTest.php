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
 * @author     Yannick Voyer (http://github.com/yvoyer)
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
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
}
