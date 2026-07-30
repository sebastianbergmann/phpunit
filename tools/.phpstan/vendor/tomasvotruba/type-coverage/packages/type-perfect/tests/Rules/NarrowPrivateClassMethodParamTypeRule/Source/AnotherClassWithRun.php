<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Source;

use PhpParser\Node;

final class AnotherClassWithRun
{
    public function run(Node $node)
    {
        return $node;
    }
}
