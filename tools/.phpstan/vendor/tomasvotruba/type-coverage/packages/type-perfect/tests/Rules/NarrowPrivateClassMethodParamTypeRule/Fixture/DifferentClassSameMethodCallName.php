<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Source\AnotherClassWithRun;
use Rector\TypePerfect\Tests\Rules\PreferredClassRule\Fixture\SomeStaticCall;

class DifferentClassSameMethodCallName
{
    /**
     * @param SomeStaticCall|MethodCall $node
     */
    public function process(AnotherClassWithRun $anotherClassWithRun)
    {
        $anotherClassWithRun->run($anotherClassWithRun);
    }

    /**
     * @param SomeStaticCall|MethodCall $node
     */
    private function run(Node $node)
    {
        if ($node->name instanceof MethodCall) {
            $this->run($node->name);
        }
    }
}
