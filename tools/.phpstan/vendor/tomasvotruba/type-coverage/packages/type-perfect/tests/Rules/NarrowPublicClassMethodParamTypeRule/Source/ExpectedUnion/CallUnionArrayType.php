<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\ExpectedUnion;

use PhpParser\Node;
use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipEqualUnionType;

final class CallUnionTypeArrayType
{
    public function run(SkipEqualUnionType $skipEqualUnionType, Node $node): void
    {
        /** @var Node[]|Node $node */
        $node = rand(0, 1)
            ? $node
            : [$node];

        $skipEqualUnionType->runArrayTyped($node);
    }

    public function run2(SkipEqualUnionType $skipEqualUnionType, Node $node): void
    {
        /** @var Node[]|Node $node */
        $node = rand(0, 1)
            ? $node
            : [$node];

        $skipEqualUnionType->runArrayTyped2($node);
    }
}
