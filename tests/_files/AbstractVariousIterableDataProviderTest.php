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

abstract class AbstractVariousIterableDataProviderTest
{
    public static function asArrayProviderInParent()
    {
        return [
            ['J'],
            ['K'],
            ['L'],
        ];
    }

    public static function asIteratorProviderInParent()
    {
        yield ['M'];

        yield ['N'];

        yield ['O'];
    }

    public static function asTraversableProviderInParent()
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

    /**
     * @dataProvider asArrayProvider
     * @dataProvider asIteratorProvider
     * @dataProvider asTraversableProvider
     */
    public function testAbstract(): void
    {
    }

    /**
     * @dataProvider asArrayProviderInParent
     * @dataProvider asIteratorProviderInParent
     * @dataProvider asTraversableProviderInParent
     */
    public function testInParent(): void
    {
    }
}
