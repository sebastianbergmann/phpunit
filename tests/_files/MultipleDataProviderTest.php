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

use ArrayIterator;
use ArrayObject;
use Generator;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MultipleDataProviderTest extends TestCase
{
    public static function providerA(): array
    {
        return [
            ['ok', null, null],
            ['ok', null, null],
            ['ok', null, null],
        ];
    }

    public static function providerB(): array
    {
        return [
            [null, 'ok', null],
            [null, 'ok', null],
            [null, 'ok', null],
        ];
    }

    public static function providerC(): array
    {
        return [
            [null, null, 'ok'],
            [null, null, 'ok'],
            [null, null, 'ok'],
        ];
    }

    public static function providerD(): Generator
    {
        yield ['ok', null, null];

        yield ['ok', null, null];

        yield ['ok', null, null];
    }

    public static function providerE(): Generator
    {
        yield [null, 'ok', null];

        yield [null, 'ok', null];

        yield [null, 'ok', null];
    }

    public static function providerF(): ArrayIterator|Iterator
    {
        $object = new ArrayObject(
            [
                [null, null, 'ok'],
                [null, null, 'ok'],
                [null, null, 'ok'],
            ],
        );

        return $object->getIterator();
    }

    #[DataProvider('providerA')]
    #[DataProvider('providerB')]
    #[DataProvider('providerC')]
    public function testOne(): void
    {
    }

    #[DataProvider('providerD')]
    #[DataProvider('providerE')]
    #[DataProvider('providerF')]
    public function testTwo(): void
    {
    }
}
