<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\PublicDoubleShot;

final class SecondCaller
{
    public function goForIt(PublicDoubleShot $publicDoubleShot)
    {
        $publicDoubleShot->callMeTwice(100);
    }
}
