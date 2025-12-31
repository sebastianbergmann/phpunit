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

#[CoversMethod(Assert::class, 'assertLessThanOrEqual')]
#[TestDox('assertLessThanOrEqual()')]
#[Small]
final class assertLessThanOrEqualTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: int, 1: int}>
     */
    public static function successProvider(): array
    {
        return [
            [2, 1],
            [2, 2],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $maximum, mixed $actual): void
    {
        $this->assertLessThanOrEqual($maximum, $actual);
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertLessThanOrEqual(1, 2);
    }
}
