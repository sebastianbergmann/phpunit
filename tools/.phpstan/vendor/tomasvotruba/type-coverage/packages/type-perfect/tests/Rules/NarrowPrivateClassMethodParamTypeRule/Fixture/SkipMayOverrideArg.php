<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;

class SkipMayOverrideArg
{
    public function run(Node $node)
    {
        if ($node instanceof MethodCall) {

            // on this part, $node may be overriden
            $node = $this->mayOverrideNode();

            $this->isCheck($node);

        }
    }

    private function isCheck(Node $node)
    {
    }
}
