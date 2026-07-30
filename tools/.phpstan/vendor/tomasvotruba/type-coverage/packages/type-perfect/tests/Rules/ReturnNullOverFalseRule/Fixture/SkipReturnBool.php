<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\ReturnNullOverFalseRule\Fixture;

final class SkipReturnBool
{
    public function run(): bool
    {
        if (mt_rand(1, 0)) {
            return true;
        }

        return false;
    }
}
