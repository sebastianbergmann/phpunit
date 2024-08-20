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
use PHPUnit\TestFixture\ObjectEquals\ValueObject;

#[CoversMethod(Assert::class, 'assertObjectEquals')]
#[TestDox('assertObjectEquals()')]
#[Small]
final class assertObjectEqualsTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: object, 1: object, 2: non-empty-string}>
     */
    public static function successProvider(): array
    {
        return [
            [new ValueObject(1), new ValueObject(1), 'equals'],
        ];
    }

    /**
     * @return non-empty-list<array{0: object, 1: object, 2: non-empty-string}>
     */
    public static function failureProvider(): array
    {
        return [
            [new ValueObject(1), new ValueObject(2), 'equals'],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(object $expected, object $actual, string $method): void
    {
        $this->assertObjectEquals($expected, $actual, $method);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(object $expected, object $actual, string $method): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertObjectEquals($expected, $actual, $method);
    }
}
