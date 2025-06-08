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

abstract class AbstractVariousIterableDataProviderTest
{
    public static function asArrayProviderInParent(): array
    {
        return [
            ['J'],
            ['K'],
            ['L'],
        ];
    }

    public static function asIteratorProviderInParent(): Generator
    {
        yield ['M'];

        yield ['N'];

        yield ['O'];
    }

    public static function asTraversableProviderInParent(): WrapperIteratorAggregate
    {
        return new WrapperIteratorAggregate([
            ['P'],
            ['Q'],
            ['R'],
        ]);
    }

    abstract public static function asArrayProvider();

    abstract public static function asIteratorProvider();

    abstract public static function asTraversableProvider();

    #[DataProvider('asArrayProvider')]
    #[DataProvider('asIteratorProvider')]
    #[DataProvider('asTraversableProvider')]
    public function testAbstract(string $x): void
    {
    }

    #[DataProvider('asArrayProviderInParent')]
    #[DataProvider('asIteratorProviderInParent')]
    #[DataProvider('asTraversableProviderInParent')]
    public function testInParent(string $x): void
    {
    }
}
