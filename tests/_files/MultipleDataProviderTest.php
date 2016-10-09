<?php
class MultipleDataProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerA
     * @dataProvider providerB
     * @dataProvider providerC
     */
    public function testOne()
    {
    }

    public static function providerA()
    {
        return [
            ['ok', null, null],
            ['ok', null, null],
            ['ok', null, null]
        ];
    }

    public static function providerB()
    {
        return [
            [null, 'ok', null],
            [null, 'ok', null],
            [null, 'ok', null]
        ];
    }

    public static function providerC()
    {
        return [
            [null, null, 'ok'],
            [null, null, 'ok'],
            [null, null, 'ok']
        ];
    }
}
