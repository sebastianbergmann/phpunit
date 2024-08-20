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

#[CoversMethod(Assert::class, 'assertEqualsWithDelta')]
#[TestDox('assertEqualsWithDelta()')]
#[Small]
final class assertEqualsWithDeltaTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: mixed, 2: float}>
     */
    public static function successProvider(): array
    {
        return [
            [2.3, 2.5, 0.5],
            [[2.3], [2.5], 0.5],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed, 2: float}>
     */
    public static function failureProvider(): array
    {
        return [
            [2.3, 3.5, 0.5],
            [[2.3], [3.5], 0.5],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $expected, mixed $actual, float $delta): void
    {
        $this->assertEqualsWithDelta($expected, $actual, $delta);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $expected, mixed $actual, float $delta): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertEqualsWithDelta($expected, $actual, $delta);
    }
}
