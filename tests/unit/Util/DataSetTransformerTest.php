<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Framework\InvalidDataSetException;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class DataSetTransformerTest extends TestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform(\ReflectionMethod $method, array $dataSet, array $expected): void
    {
        $this->assertSame($expected, DataSetTransformer::transform($method, $dataSet));
    }

    public function transformProvider(): array
    {
        $fn = new \ReflectionMethod(static::class, 'fn');

        return [
            [
                $fn,
                ['first', 'second', 'third'],
                ['first', 'second', 'third'],
            ],
            [
                $fn,
                ['a' => 'first'],
                ['first', null],
            ],
            [
                $fn,
                ['a' => 'first', 'b' => 'second'],
                ['first', 'second'],
            ],
            [
                $fn,
                ['b' => 'second', 'a' => 'first'],
                ['first', 'second'],
            ],
            [
                $fn,
                ['a' => 'first', 'b' => 'second', 'params' => ['third']],
                ['first', 'second', 'third'],
            ],
            [
                $fn,
                ['a' => 'first', 'b' => 'second', 'params' => ['third', 'fourth']],
                ['first', 'second', 'third', 'fourth'],
            ],
            [
                $fn,
                ['a' => 'first', 'params' => ['third', 'fourth']],
                ['first', null, 'third', 'fourth'],
            ],
        ];
    }

    /**
     * @dataProvider invalidDataSetProvider
     */
    public function testInvalidDataSet(\ReflectionMethod $method, array $dataSet, string $message): void
    {
        $this->expectException(InvalidDataSetException::class);
        $this->expectExceptionMessage($message);

        DataSetTransformer::transform($method, $dataSet);
    }

    public function invalidDataSetProvider(): array
    {
        $fn = new \ReflectionMethod(static::class, 'fn');

        return [
            [
                $fn,
                ['b' => 'param b'],
                'parameter $a is not given',
            ],
            [
                $fn,
                ['a' => 'a', 'd' => 'd', 'e' => 'e'],
                'method PHPUnit\Util\DataSetTransformerTest::fn does not have the following parameters: d, e',
            ],
            [
                $fn,
                ['a' => 'a', 'params' => new \stdClass()],
                'parameter $params in PHPUnit\Util\DataSetTransformerTest::fn is variadic, non-associative array required, stdClass given',
            ],
            [
                $fn,
                ['a' => 'a', 'params' => ['a' => 'b']],
                'parameter $params in PHPUnit\Util\DataSetTransformerTest::fn is variadic, non-associative array required, array given',
            ],
        ];
    }

    public function fn($a, $b = null, ...$params): void
    {
    }
}
