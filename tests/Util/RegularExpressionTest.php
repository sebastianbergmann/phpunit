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

class RegularExpressionTest extends TestCase
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

    /**
     * @dataProvider validRegexpProvider
     */
    public function testValidRegex($pattern, $subject, $return)
    {
        $this->assertEquals($return, RegularExpression::safeMatch($pattern, $subject));
    }

    /**
     * @dataProvider invalidRegexpProvider
     */
    public function testInvalidRegex($pattern, $subject)
    {
        $this->assertFalse(RegularExpression::safeMatch($pattern, $subject));
    }
}
