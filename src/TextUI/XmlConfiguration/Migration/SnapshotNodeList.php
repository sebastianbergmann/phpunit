<?php declare(strict_types = 1);
namespace TextUI\XmlConfiguration\Migration;

use ArrayIterator;
use Countable;
use DOMNode;
use DOMNodeList;
use IteratorAggregate;

class SnapshotNodeList implements IteratorAggregate, Countable {

    /** @var DOMNode[] */
    private $nodes = [];

    public static function fromNodeList(DOMNodeList  $list): SnapshotNodeList {
        $snapshot = new self();
        foreach($list as $node) {
            $snapshot->nodes[] = $node;
        }

        return $snapshot;
    }

    public function count(): int {
        return count($this->nodes);
    }

    public function getIterator() {
        return new ArrayIterator($this->nodes);
    }

}
