<?php

declare(strict_types=1);

namespace Rector\TypePerfect\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use Rector\TypePerfect\NodeVisitor\CallableNodeVisitor;

final readonly class ReturnNodeFinder
{
    public function findOnlyReturnsExpr(ClassMethod $classMethod): Expr|null
    {
        $returns = $this->findReturnsWithValues($classMethod);
        if (count($returns) !== 1) {
            return null;
        }

        $onlyReturn = $returns[0];
        return $onlyReturn->expr;
    }

    /**
     * @return Return_[]
     */
    public function findReturnsWithValues(ClassMethod $classMethod): array
    {
        $returns = [];

        $this->traverseNodesWithCallable((array) $classMethod->stmts, static function (
            Node $node
        ) use (&$returns) {
            // skip different scope
            if ($node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }

            if (! $node instanceof Return_) {
                return null;
            }

            if (! $node->expr instanceof Expr) {
                return null;
            }

            $returns[] = $node;
        });

        return $returns;
    }

    /**
     * @param callable(Node $node): (int|Node|null) $callable
     * @param Node[] $nodes
     */
    private function traverseNodesWithCallable(array $nodes, callable $callable): void
    {
        $nodeTraverser = new NodeTraverser();

        $callableNodeVisitor = new CallableNodeVisitor($callable);
        $nodeTraverser->addVisitor($callableNodeVisitor);
        $nodeTraverser->traverse($nodes);
    }
}
