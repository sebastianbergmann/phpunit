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
    public function asArrayProviderInParent()
    {
        return [
            ['J'],
            ['K'],
            ['L'],
        ];
    }

    public function asIteratorProviderInParent()
    {
        yield ['M'];

        yield ['N'];

        yield ['O'];
    }

    public function asTraversableProviderInParent()
    {
        return new WrapperIteratorAggregate([
            ['P'],
            ['Q'],
            ['R'],
        ]);
    }

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

    abstract protected function asArrayProvider();

    abstract protected function asIteratorProvider();

    abstract protected function asTraversableProvider();
}
