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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegularExpression::class)]
#[Small]
final class RegularExpressionTest extends TestCase
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

    #[DataProvider('validRegexpProvider')]
    #[TestDox('Valid regex $pattern on $subject returns $return')]
    public function testValidRegex(string $pattern, string $subject, int $return): void
    {
        $this->assertEquals($return, RegularExpression::safeMatch($pattern, $subject));
    }

    #[DataProvider('invalidRegexpProvider')]
    #[TestDox('Invalid regex $pattern on $subject')]
    public function testInvalidRegex(string $pattern, string $subject): void
    {
        $this->assertFalse(RegularExpression::safeMatch($pattern, $subject));
    }
}
