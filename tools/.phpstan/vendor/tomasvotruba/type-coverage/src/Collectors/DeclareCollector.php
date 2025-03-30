<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage\Collectors;

use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\FileNode;

final class DeclareCollector implements Collector
{
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /**
     * @param FileNode $node
     */
    public function processNode(Node $node, Scope $scope): bool
    {
        foreach ($node->getNodes() as $node) {
            if (! $node instanceof Declare_) {
                continue;
            }

            foreach ($node->declares as $declare) {
                if (
                    $declare->key->name !== 'strict_types'
                ) {
                    continue;
                }

                if (
                    ! $declare->value instanceof LNumber
                    || $declare->value->value !== 1
                ) {
                    return false;
                }

                return true;
            }
        }

        return false;
    }
}
