<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertJsonStringEqualsJsonString')]
#[TestDox('assertJsonStringEqualsJsonString()')]
#[Small]
final class assertJsonStringEqualsJsonStringTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function successProvider(): array
    {
        return [
            ['{"Mascot" : "elePHPant"}', '{"Mascot" : "elePHPant"}'],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function failureProvider(): array
    {
        return [
            ['{"Mascot" : "elePHPant"}', '{"Mascot" : "Tux"}'],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expectedJson, string $actualJson): void
    {
        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expectedJson, string $actualJson): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }
}
