<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

final class SkipClosure
{
    public function map(\Closure $mapper): array
    {
        return [];
    }

    public function run()
    {
        $this->map(function () {});
    }
}
