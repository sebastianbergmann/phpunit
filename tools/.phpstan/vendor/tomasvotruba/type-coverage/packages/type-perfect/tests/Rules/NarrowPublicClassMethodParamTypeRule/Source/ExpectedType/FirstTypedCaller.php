<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\ExpectedType;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipProperlyFilledParamType;

final class FirstTypedCaller
{
    public function goForIt(SkipProperlyFilledParamType $skipProperlyFilledParamType)
    {
        $skipProperlyFilledParamType->callMeTwice(100);
    }
}
