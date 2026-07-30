<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Printer;

use PhpParser\Node;
use PHPStan\Node\Printer\Printer;

final readonly class NodeComparator
{
    public function __construct(
        private Printer $printer
    ) {
    }

    public function areNodesEqual(Node $firstNode, Node $secondNode): bool
    {
        // remove comments from nodes
        $firstNode->setAttribute('comments', null);
        $secondNode->setAttribute('comments', null);

        return $this->printer->prettyPrint([$firstNode]) === $this->printer->prettyPrint([$secondNode]);
    }
}
