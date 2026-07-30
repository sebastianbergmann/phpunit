<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

final class ExplicitlyNullableParams
{
    public function test(
        ?bool $bool = true,
    ): bool
    {
        return $bool;
    }

    static public function run(): void
    {
        $skipDateTimeMix = new ExplicitlyNullableParams();
        $skipDateTimeMix->test(false);
    }
}
