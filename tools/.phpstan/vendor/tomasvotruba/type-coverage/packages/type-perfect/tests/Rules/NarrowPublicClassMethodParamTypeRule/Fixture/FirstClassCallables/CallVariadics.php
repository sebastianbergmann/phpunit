<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\FirstClassCallables;

final class CallVariadics
{
    public function callMe(SomeCalledMethod $someCalledMethod)
    {
        $closure = $someCalledMethod->callMe(...);

        $someCalledMethod->callMe(1000);
    }
}
