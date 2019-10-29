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

final class FilterDirectoryCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var FilterDirectory[]
     */
    private $items = [];

    /**
     * @param FilterDirectory[] $items
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
     * @return FilterDirectory[]
     */
    public function asArray(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function getIterator(): FilterDirectoryCollectionIterator
    {
        return new FilterDirectoryCollectionIterator($this);
    }

    private function add(FilterDirectory $item): void
    {
        $this->items[] = $item;
    }
}
