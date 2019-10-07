<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class VariousIterableDataProviderTest
{
    public static function asArrayProvider()
    {
        return [
            ['A'],
            ['B'],
            ['C'],
        ];
    }

    public static function asIteratorProvider()
    {
        yield ['D'];

        yield ['E'];

        yield ['F'];
    }

    public static function asTraversableProvider()
    {
        return new WrapperIteratorAggregate([
            ['G'],
            ['H'],
            ['I'],
        ]);
    }

    /**
     * @dataProvider asArrayProvider
     * @dataProvider asIteratorProvider
     * @dataProvider asTraversableProvider
     */
    public function test(): void
    {
    }
}
