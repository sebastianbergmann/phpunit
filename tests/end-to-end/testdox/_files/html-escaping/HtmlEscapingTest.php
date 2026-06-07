<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestDox\HtmlEscaping;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('<script>alert(1)</script>')]
final class HtmlEscapingTest extends TestCase
{
    public static function provider(): array
    {
        return [
            '<img src=x onerror=alert(2)>' => [true],
        ];
    }

    #[TestDox('<b>"x" & \'y\'</b>')]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('provider')]
    public function testTwo(bool $value): void
    {
        $this->assertTrue($value);
    }
}
