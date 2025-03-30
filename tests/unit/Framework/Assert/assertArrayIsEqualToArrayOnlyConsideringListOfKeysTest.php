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

#[CoversMethod(Assert::class, 'assertArrayIsEqualToArrayOnlyConsideringListOfKeys')]
#[TestDox('assertArrayIsEqualToArrayOnlyConsideringListOfKeys()')]
#[Small]
final class assertArrayIsEqualToArrayOnlyConsideringListOfKeysTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: array<mixed>, 1: array<mixed>, 2: array<mixed>}>
     */
    public static function successProvider(): array
    {
        return [
            [
                ['a' => 'b', 'b' => 'c', 0 => 1, 1 => 2],
                ['a' => 'b', 'b' => 'b', 0 => 1, 1 => 3],
                ['a', 0],
            ],
            [
                [0 => 1, '1' => 2, 2.0 => 3, '3.0' => 4],
                [0 => 1, '1' => 2, 2.0 => 2, '3.0' => 4],
                [0, '1', '3.0'],
            ],
            [
                ['a' => 'b', 'b' => 'c', 0 => 1, 1 => 2],
                [0 => 1, 1 => 3, 'a' => 'b', 'b' => 'b'],
                ['a', 0],
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: array<mixed>, 1: array<mixed>, 2: array<mixed>}>
     */
    public static function failureProvider(): array
    {
        return [
            [
                ['a' => 'b', 'b' => 'c', 0 => 1, 1 => 2],
                ['a' => 'b', 'b' => 'b', 0 => 1, 1 => 3],
                ['b'],
            ],
            [
                [0 => 1, '1' => 2, 2.0 => 3, '3.0' => 4],
                [0 => 1, '1' => 2, 2.0 => 2, '3.0' => 4],
                ['1', 2.0, '3.0'],
            ],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(array $expected, array $actual, array $keysToBeConsidered): void
    {
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected, $actual, $keysToBeConsidered);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(array $expected, array $actual, array $keysToBeConsidered): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected, $actual, $keysToBeConsidered);
    }
}
