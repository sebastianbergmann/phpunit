<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;

final class SkipEqualUnionType
{
    public function run(MethodCall|StaticCall $obj)
    {
    }

    public function runTernary(StaticCall|MethodCall $obj)
    {
        return $obj instanceof StaticCall
            ? $obj->class
            : $obj->var;
    }

    public function runTernaryFlipped(StaticCall|MethodCall $obj)
    {
        return $obj instanceof MethodCall
            ? $obj->var
            : $obj->class;
    }

    /**
     * @param Node[]|Node $node
     */
    public function runArrayTyped(array | Node $node)
    {
    }

    /**
     * @param array<Node>|Node $node
     */
    public function runArrayTyped2(array | Node $node)
    {
    }
}
