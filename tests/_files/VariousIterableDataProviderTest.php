<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

final class VariousIterableDataProviderTest extends AbstractVariousIterableDataProviderTest
{
    public static function asArrayStaticProvider(): array
    {
        return [
            ['A'],
            ['B'],
            ['C'],
        ];
    }

    public static function asIteratorStaticProvider(): Generator
    {
        yield ['D'];

        yield ['E'];

        yield ['F'];
    }

    public static function asTraversableStaticProvider(): WrapperIteratorAggregate
    {
        return new WrapperIteratorAggregate([
            ['G'],
            ['H'],
            ['I'],
        ]);
    }

    public static function asArrayProvider(): array
    {
        return [
            ['S'],
            ['T'],
            ['U'],
        ];
    }

    public static function asIteratorProvider(): Generator
    {
        yield ['V'];

        yield ['W'];

        yield ['X'];
    }

    public static function asTraversableProvider(): WrapperIteratorAggregate
    {
        return new WrapperIteratorAggregate([
            ['Y'],
            ['Z'],
            ['P'],
        ]);
    }

    #[DataProvider('asArrayStaticProvider')]
    #[DataProvider('asIteratorStaticProvider')]
    #[DataProvider('asTraversableStaticProvider')]
    public function testStatic(): void
    {
    }

    #[DataProvider('asArrayProvider')]
    #[DataProvider('asIteratorProvider')]
    #[DataProvider('asTraversableProvider')]
    public function testNonStatic(): void
    {
    }

    #[DataProvider('asArrayProviderInParent')]
    #[DataProvider('asIteratorProviderInParent')]
    #[DataProvider('asTraversableProviderInParent')]
    public function testFromParent(): void
    {
    }
}
