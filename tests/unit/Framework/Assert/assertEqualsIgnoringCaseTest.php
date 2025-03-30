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

#[CoversMethod(Assert::class, 'assertEqualsIgnoringCase')]
#[TestDox('assertEqualsIgnoringCase()')]
#[Small]
final class assertEqualsIgnoringCaseTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function successProvider(): array
    {
        return [
            ['a', 'A'],
            [['a'], ['A']],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function failureProvider(): array
    {
        return [
            ['a', 'B'],
            [['a'], ['B']],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $expected, mixed $actual): void
    {
        $this->assertEqualsIgnoringCase($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $expected, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertEqualsIgnoringCase($expected, $actual);
    }
}
