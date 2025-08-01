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
use stdClass;

#[CoversMethod(Assert::class, 'assertSameDictionaryKeysValuesTest')]
#[TestDox('assertSameDictionaryKeysValuesTest()')]
#[Small]
final class assertSameDictionaryKeysValuesTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function successProvider(): array
    {
        return [
            [
                [
                    'string'     => 'string',
                    true         => true,
                    1            => 1,
                    2            => 2.5,
                    'object'     => new stdClass,
                    'array'      => [1, 2, 3],
                    'dictionary' => [
                        'string' => 'string',
                        true     => true,
                        1        => 1,
                        2        => 2.5,
                        'object' => new stdClass,
                        'array'  => [1, 2, 3],
                    ],
                ],
                [
                    'dictionary' => [
                        'object' => new stdClass,
                        'array'  => [1, 2, 3],
                        'string' => 'string',
                        true     => true,
                        1        => 1,
                        2        => 2.5,
                    ],
                    'string' => 'string',
                    true     => true,
                    1        => 1,
                    2        => 2.5,
                    'object' => new stdClass,
                    'array'  => [1, 2, 3],
                ],
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function failureProvider(): array
    {
        return [
            [
                [
                    'string'     => 'string',
                    true         => true,
                    1            => 1,
                    2            => 2.5,
                    'object'     => new stdClass,
                    'array'      => [1, 2, 3],
                    'dictionary' => [
                        'string' => 'string',
                        true     => true,
                        1        => 1,
                        2        => 2.5,
                        'object' => new stdClass,
                        'array'  => [1, 2, 3],
                    ],
                ],
                [
                    'string' => 'string',
                    true     => true,
                    1        => 1,
                ],
            ],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $expected, mixed $actual): void
    {
        $this->assertSameDictionaryKeysValues($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $expected, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertSameDictionaryKeysValues($expected, $actual);
    }
}
