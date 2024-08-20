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

use function PHPUnit\TestFixture\Generator\f;
use ArrayIterator;
use Countable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertCount')]
#[CoversClass(GeneratorNotSupportedException::class)]
#[TestDox('assertCount()')]
#[Small]
final class assertCountTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: int, 1: Countable|iterable}>
     */
    public static function successProvider(): array
    {
        return [
            [2, [1, 2]],
            [2, new ArrayIterator([1, 2])],
        ];
    }

    /**
     * @return non-empty-list<array{0: int, 1: Countable|iterable}>
     */
    public static function failureProvider(): array
    {
        return [
            [2, [1, 2, 3]],
            [2, new ArrayIterator([1, 2, 3])],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(int $expectedCount, Countable|iterable $haystack): void
    {
        $this->assertCount($expectedCount, $haystack);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(int $expectedCount, Countable|iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertCount($expectedCount, $haystack);
    }

    public function testDoesNotSupportGenerators(): void
    {
        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $haystack parameter is not supported');

        $this->assertCount(0, f());
    }
}
