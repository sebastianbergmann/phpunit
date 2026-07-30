<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\ExpectedClassType;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipExpectedClassType;
use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\PassMeAsType;

final class SecondClassTypedCaller
{
    public function goForIt(SkipExpectedClassType $skipExpectedClassType)
    {
        $knownType = new PassMeAsType();
        $skipExpectedClassType->callMeWithClassType($knownType);
    }
}
