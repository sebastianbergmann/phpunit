<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

final class SkipNonPublicClassMethod
{
    public function personInTree()
    {
        $this->callMe(1000);
    }

    private function callMe($number)
    {
    }
}
