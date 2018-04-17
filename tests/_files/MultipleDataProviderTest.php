<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class MultipleDataProviderTest extends TestCase
{
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

    public static function providerD()
    {
        yield ['ok', null, null];

        yield ['ok', null, null];

        yield ['ok', null, null];
    }

    public static function providerE()
    {
        yield [null, 'ok', null];

        yield [null, 'ok', null];

        yield [null, 'ok', null];
    }

    public static function providerF()
    {
        $object = new ArrayObject(
            [
                [null, null, 'ok'],
                [null, null, 'ok'],
                [null, null, 'ok']
            ]
        );

        return $object->getIterator();
    }

    /**
     * @dataProvider providerA
     * @dataProvider providerB
     * @dataProvider providerC
     */
    public function testOne(): void
    {
    }

    /**
     * @dataProvider providerD
     * @dataProvider providerE
     * @dataProvider providerF
     */
    public function testTwo(): void
    {
    }
}
