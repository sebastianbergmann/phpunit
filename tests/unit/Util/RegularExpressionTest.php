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
    public function validRegexpProvider(): array
    {
        return [
            ['#valid regexp#', 'valid regexp', 1],
            [';val.*xp;', 'valid regexp', 1],
            ['/val.*xp/i', 'VALID REGEXP', 1],
            ['/a val.*p/', 'valid regexp', 0],
        ];
    }

    public function invalidRegexpProvider(): array
    {
        return [
            ['valid regexp', 'valid regexp'],
            [';val.*xp', 'valid regexp'],
            ['val.*xp/i', 'VALID REGEXP'],
        ];
    }

    /**
     * @testdox Valid regex $pattern on $subject returns $return
     * @dataProvider validRegexpProvider
     *
     * @throws \Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testValidRegex($pattern, $subject, $return): void
    {
        $this->assertEquals($return, RegularExpression::safeMatch($pattern, $subject));
    }

    /**
     * @testdox Invalid regex $pattern on $subject
     * @dataProvider invalidRegexpProvider
     *
     * @throws \Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testInvalidRegex($pattern, $subject): void
    {
        $this->assertFalse(RegularExpression::safeMatch($pattern, $subject));
    }
}
