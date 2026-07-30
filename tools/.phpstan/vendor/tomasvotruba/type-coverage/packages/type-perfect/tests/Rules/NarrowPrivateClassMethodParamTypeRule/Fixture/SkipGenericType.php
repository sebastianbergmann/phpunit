<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;

/**
 * @template T of MethodCall
 */
class SkipGenericType
{
    /**
     * @param T $node
     * @return void
     */
    public function run(Node $node)
    {
        $this->getsAGeneric($node);
    }

    /**
     * @param T $node
     * @return void
     */
    private function getsAGeneric(Node $node)
    {
    }
}
