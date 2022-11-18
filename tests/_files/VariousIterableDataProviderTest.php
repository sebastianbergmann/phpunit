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

class VariousIterableDataProviderTest extends AbstractVariousIterableDataProviderTest
{
    public static function asArrayStaticProvider()
    {
        return [
            ['A'],
            ['B'],
            ['C'],
        ];
    }

    public static function asIteratorStaticProvider()
    {
        yield ['D'];

        yield ['E'];

        yield ['F'];
    }

    public static function asTraversableStaticProvider()
    {
        return new WrapperIteratorAggregate([
            ['G'],
            ['H'],
            ['I'],
        ]);
    }

    public static function asArrayProvider()
    {
        return [
            ['S'],
            ['T'],
            ['U'],
        ];
    }

    public static function asIteratorProvider()
    {
        yield ['V'];

        yield ['W'];

        yield ['X'];
    }

    public static function asTraversableProvider()
    {
        return new WrapperIteratorAggregate([
            ['Y'],
            ['Z'],
            ['P'],
        ]);
    }

    /**
     * @dataProvider asArrayStaticProvider
     * @dataProvider asIteratorStaticProvider
     * @dataProvider asTraversableStaticProvider
     */
    public function testStatic(): void
    {
    }

    /**
     * @dataProvider asArrayProvider
     * @dataProvider asIteratorProvider
     * @dataProvider asTraversableProvider
     */
    public function testNonStatic(): void
    {
    }

    /**
     * @dataProvider asArrayProviderInParent
     * @dataProvider asIteratorProviderInParent
     * @dataProvider asTraversableProviderInParent
     */
    public function testFromParent(): void
    {
    }
}
