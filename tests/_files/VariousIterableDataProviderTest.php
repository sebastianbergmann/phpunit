<?php

class VariousIterableDataProviderTest
{
    /**
     * @dataProvider asArrayProvider
     * @dataProvider asIteratorProvider
     * @dataProvider asTraversableProvider
     */
    public function test()
    {
    }

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
}
