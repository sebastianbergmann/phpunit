<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\ExpectedUnion;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipEqualUnionType;

final class CallUnionTypeFlipped
{
    /**
     * @param StaticCall[]|MethodCall[] $data
     */
    public function run(SkipEqualUnionType $skipEqualUnionType, array $data): void
    {
        foreach ($data as $value) {
            $skipEqualUnionType->run($value);
        }
    }
}
