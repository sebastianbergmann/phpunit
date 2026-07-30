<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\TypePerfect\Tests\Rules\PreferredClassRule\Fixture\SomeStaticCall;

class SkipRecursive
{
    /**
     * @param SomeStaticCall|MethodCall $node
     */
    public function processNode(Node $node): void
    {
        $this->run($node);
    }

    /**
     * @param SomeStaticCall|MethodCall $node
     */
    private function run(Node $node): void
    {
        if ($node->name instanceof MethodCall) {
            $this->run($node->name);
        }
    }
}
