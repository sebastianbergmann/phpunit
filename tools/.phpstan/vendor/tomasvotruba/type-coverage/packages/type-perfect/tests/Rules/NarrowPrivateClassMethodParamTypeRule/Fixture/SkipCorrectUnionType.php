<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Return_;

final class SkipCorrectUnionType
{
    public function run(\PhpParser\Node $node)
    {
        if (! $node instanceof Assign && ! $node instanceof Return_) {
            return [];
        }

        if (! $this->isIncludeOnceOrRequireOnce($node)) {
            return [];
        }

        return [];
    }

    /**
     * @param Assign|Return_ $node
     */
    private function isIncludeOnceOrRequireOnce(\PhpParser\Node $node)
    {
        return true;
    }
}
