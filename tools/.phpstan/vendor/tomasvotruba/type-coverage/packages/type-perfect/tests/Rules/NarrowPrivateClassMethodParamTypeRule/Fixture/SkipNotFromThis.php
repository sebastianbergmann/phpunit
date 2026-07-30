<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;

final class SkipNotFromThis
{
    public function run()
    {
        $obj = new \DateTime('now');
        $obj->format('Y-m-d');
    }
}
