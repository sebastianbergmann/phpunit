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

final class ExtensionCollection implements \IteratorAggregate
{
    /**
     * @var Extension[]
     */
    private $items = [];

    /**
     * @param Extension[] $items
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
     * @return Extension[]
     */
    public function asArray(): array
    {
        return $this->items;
    }

    public function getIterator(): ExtensionCollectionIterator
    {
        return new ExtensionCollectionIterator($this);
    }

    private function add(Extension $item): void
    {
        $this->items[] = $item;
    }
}
