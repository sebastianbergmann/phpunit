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
use EmptyIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertEmpty')]
#[CoversClass(GeneratorNotSupportedException::class)]
#[TestDox('assertEmpty()')]
#[Small]
final class assertEmptyTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed}>
     */
    public static function successProvider(): array
    {
        return [
            [[]],
            [''],
            [null],
            [false],
            ['0'],
            [0],
            [new EmptyIterator],
            [
                new class implements Countable
                {
                    public function count(): int
                    {
                        return 0;
                    }
                },
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed}>
     */
    public static function failureProvider(): array
    {
        return [
            [[0]],
            [true],
            ['1'],
            [
                new class implements Countable
                {
                    public function count(): int
                    {
                        return 1;
                    }
                },
            ],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $actual): void
    {
        $this->assertEmpty($actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertEmpty($actual);
    }

    public function testDoesNotSupportGenerators(): void
    {
        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $actual parameter is not supported');

        $this->assertEmpty(f());
    }
}
