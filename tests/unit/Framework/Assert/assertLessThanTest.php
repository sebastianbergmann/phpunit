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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertLessThan')]
#[TestDox('assertLessThan()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertLessThanTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: int, 1: int}>
     */
    public static function failureProvider(): array
    {
        return [
            [1, 2],
            [1, 1],
        ];
    }

    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertLessThan(2, 1);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $maximum, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertLessThan($maximum, $actual);
    }
}
