<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Extensions_PhptTestCaseTest extends \PHPUnit_Framework_TestCase
{
    public function testParseIniSection()
    {
        $phptTestCase = new PhpTestCaseProxy(__FILE__);
        $settings     = $phptTestCase->parseIniSection("foo=1\nbar = 2\rbaz = 3\r\nempty=\nignore");

        $expected = array(
            'foo=1',
            'bar = 2',
            'baz = 3',
            'empty=',
            'ignore',
        );

        $this->assertEquals($expected, $settings);
    }
}

class PhpTestCaseProxy extends PHPUnit_Extensions_PhptTestCase
{
    public function parseIniSection($content)
    {
        return parent::parseIniSection($content);
    }
}
