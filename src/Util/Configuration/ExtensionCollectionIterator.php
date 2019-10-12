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

final class ExtensionCollectionIterator implements \Countable, \Iterator
{
    /**
     * @var Extension[]
     */
    private $items;

    /**
     * @var int
     */
    private $position;

    public function __construct(ExtensionCollection $collection)
    {
        $this->items = $collection->asArray();
    }

    public function count(): int
    {
        return \iterator_count($this);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < \count($this->items);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): Extension
    {
        return $this->items[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
