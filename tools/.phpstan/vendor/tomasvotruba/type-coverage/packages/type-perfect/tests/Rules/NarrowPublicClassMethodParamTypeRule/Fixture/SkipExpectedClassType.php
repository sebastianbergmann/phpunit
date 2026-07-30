<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\PassMeAsType;

final class SkipExpectedClassType
{
    public function callMeWithClassType(PassMeAsType $passMeAsType)
    {
    }
}
