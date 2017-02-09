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
 * @since      Class available since Release 4.2.0
 */
class Util_RegexTest extends PHPUnit_Framework_TestCase
{
    public function validRegexpProvider()
    {
        return [
          ['#valid regexp#', 'valid regexp', 1],
          [';val.*xp;', 'valid regexp', 1],
          ['/val.*xp/i', 'VALID REGEXP', 1],
          ['/a val.*p/','valid regexp', 0],
        ];
    }

    public function invalidRegexpProvider()
    {
        return [
          ['valid regexp', 'valid regexp'],
          [';val.*xp', 'valid regexp'],
          ['val.*xp/i', 'VALID REGEXP'],
        ];
    }

    public function expressionUnifierChecksDataProvider()
    {
        return [
          ['a', '/a/'],
          ['/a', '/a/'],
          ['a/', '/a/'],
          ['a/a', '/a/a/'],
          ['a/i', '/a/i'],
          ['a/g', '/a/g'],
          ['a/m', '/a/m'],
          ['a/mig', '/a/mig'],
          ['(a|b|c)', '/(a|b|c)/'],
        ];
    }

    /**
     * @dataProvider validRegexpProvider
     * @covers       PHPUnit_Util_Regex::pregMatchSafe
     */
    public function testValidRegex($pattern, $subject, $return)
    {
        $this->assertEquals($return, PHPUnit_Util_Regex::pregMatchSafe($pattern, $subject));
    }

    /**
     * @dataProvider invalidRegexpProvider
     * @covers       PHPUnit_Util_Regex::pregMatchSafe
     */
    public function testInvalidRegex($pattern, $subject)
    {
        $this->assertFalse(PHPUnit_Util_Regex::pregMatchSafe($pattern, $subject));
    }

    /**
     * @param string $examinedExpression
     * @param string $expectedExpression
     *
     * @dataProvider expressionUnifierChecksDataProvider
     * @covers PHPUnit_Util_Regex::unifyExpression
     */
    public function testItShouldUnifyProvidedStringToRegexCompatible($examinedExpression, $expectedExpression)
    {
        $this->assertSame($expectedExpression, PHPUnit_Util_Regex::unifyExpression($examinedExpression));
    }
}
