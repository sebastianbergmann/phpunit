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

use ArrayObject;
use PHPUnit\Framework\TestCase;

class MultipleDataProviderTest extends TestCase
{
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

    private static function providerA()
    {
        return [
            ['ok', null, null],
            ['ok', null, null],
            ['ok', null, null],
        ];
    }

    private static function providerB()
    {
        return [
            [null, 'ok', null],
            [null, 'ok', null],
            [null, 'ok', null],
        ];
    }

    private static function providerC()
    {
        return [
            [null, null, 'ok'],
            [null, null, 'ok'],
            [null, null, 'ok'],
        ];
    }

    private static function providerD()
    {
        yield ['ok', null, null];

        yield ['ok', null, null];

        yield ['ok', null, null];
    }

    private static function providerE()
    {
        yield [null, 'ok', null];

        yield [null, 'ok', null];

        yield [null, 'ok', null];
    }

    private static function providerF()
    {
        $object = new ArrayObject(
            [
                [null, null, 'ok'],
                [null, null, 'ok'],
                [null, null, 'ok'],
            ]
        );

        return $object->getIterator();
    }
}
