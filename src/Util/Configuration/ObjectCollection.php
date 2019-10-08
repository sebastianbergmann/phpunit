<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Configuration;

final class ObjectCollection implements \IteratorAggregate
{
    /**
     * @var _Object[]
     */
    private $items = [];

    /**
     * @param _Object[] $items
     */
    public static function fromArray(array $items): self
    {
        $collection = new self;

        foreach ($items as $item) {
            $collection->add($item);
        }

        return $collection;
    }

    private function __construct()
    {
    }

    /**
     * @return _Object[]
     */
    public function asArray(): array
    {
        return $this->items;
    }

    public function getIterator(): ObjectCollectionIterator
    {
        return new ObjectCollectionIterator($this);
    }

    private function add(_Object $item): void
    {
        $this->items[] = $item;
    }
}
