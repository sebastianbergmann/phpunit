<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace TextUI\XmlConfiguration\Migration;

use ArrayIterator;
use Countable;
use DOMNode;
use DOMNodeList;
use IteratorAggregate;

class SnapshotNodeList implements Countable, IteratorAggregate
{
    /** @var DOMNode[] */
    private $nodes = [];

    public static function fromNodeList(DOMNodeList  $list): self
    {
        $snapshot = new self();

        foreach ($list as $node) {
            $snapshot->nodes[] = $node;
        }

        return $snapshot;
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->nodes);
    }
}
