<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Control \x1B[8mCharacters")]
final class ControlCharactersTest extends TestCase
{
    public static function provider(): array
    {
        return [
            "\x1B[31mdata set name\x1B[0m" => [true],
        ];
    }

    public function testUnexpectedOutput(): void
    {
        print "\x1B[2J\x1B[Hunexpected output\n";

        $this->assertTrue(true);
    }

    public function testFailureMessage(): void
    {
        $this->fail("Failed\x1B[2K\rEverything is fine");
    }

    #[DataProvider('provider')]
    public function testDataSetName(bool $value): void
    {
        $this->assertFalse($value);
    }

    #[TestDox("Label\x07 with \x1B[8mcontrol characters")]
    public function testTestDoxLabel(): void
    {
        $this->markTestSkipped("Skipped\x1B[2K\rNothing to see here");
    }
}
