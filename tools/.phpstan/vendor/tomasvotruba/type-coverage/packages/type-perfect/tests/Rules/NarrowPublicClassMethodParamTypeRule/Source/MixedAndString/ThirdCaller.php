<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\MixedAndString;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipMixedAndString;

final class SecondCaller
{
    public function run(SkipMixedAndString $skipMixedAndString)
    {
        $skipMixedAndString->resolve(1000);
    }
}
