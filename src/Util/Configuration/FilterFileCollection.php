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

final class FilterFileCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var FilterFile[]
     */
    private $items = [];

    /**
     * @param FilterFile[] $items
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
     * @return FilterFile[]
     */
    public function asArray(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function getIterator(): FilterFileCollectionIterator
    {
        return new FilterFileCollectionIterator($this);
    }

    private function add(FilterFile $item): void
    {
        $this->items[] = $item;
    }
}
