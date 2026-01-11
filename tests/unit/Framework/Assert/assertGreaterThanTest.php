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

#[CoversMethod(Assert::class, 'assertGreaterThan')]
#[TestDox('assertGreaterThan()')]
#[Small]
final class assertGreaterThanTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: int, 1: int}>
     */
    public static function failureProvider(): array
    {
        return [
            [2, 1],
            [2, 2],
        ];
    }

    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertGreaterThan(1, 2);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $minimum, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertGreaterThan($minimum, $actual);
    }
}
