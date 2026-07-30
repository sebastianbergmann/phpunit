<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\ReturnNullOverFalseRule\Fixture;

final class ReturnFalseOnly
{
    public function run()
    {
        if (mt_rand(1, 0)) {
            return 1000;
        }

        return false;
    }
}
