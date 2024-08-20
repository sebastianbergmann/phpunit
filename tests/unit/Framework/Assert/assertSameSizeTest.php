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
use Countable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertSameSize')]
#[CoversClass(GeneratorNotSupportedException::class)]
#[TestDox('assertSameSize()')]
#[Small]
final class assertSameSizeTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: Countable|iterable, 1: Countable|iterable}>
     */
    public static function successProvider(): array
    {
        return [
            [[1, 2], [3, 4]],
        ];
    }

    /**
     * @return non-empty-list<array{0: Countable|iterable, 1: Countable|iterable}>
     */
    public static function failureProvider(): array
    {
        return [
            [[1, 2], [3]],
        ];
    }

    /**
     * @return non-empty-list<array{0: Countable|iterable, 1: Countable|iterable}>
     */
    public static function errorProvider(): array
    {
        return [
            [f(), []],
            [[], f()],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(Countable|iterable $expected, Countable|iterable $actual): void
    {
        $this->assertSameSize($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(Countable|iterable $expected, Countable|iterable $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertSameSize($expected, $actual);
    }

    #[DataProvider('errorProvider')]
    public function testDoesNotSupportGenerators(Countable|iterable $expected, Countable|iterable $actual): void
    {
        $this->expectException(GeneratorNotSupportedException::class);

        $this->assertSameSize($expected, $actual);
    }
}
