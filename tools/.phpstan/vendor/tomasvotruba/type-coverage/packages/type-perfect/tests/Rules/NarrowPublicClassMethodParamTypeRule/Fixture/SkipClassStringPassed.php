<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

final class SkipClassStringPassed
{
    public function resolve(string $className)
    {
    }
}
