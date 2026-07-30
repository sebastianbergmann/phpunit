<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

final class SkipDefault
{
    public function dontSetParamValue(
        string $Id = ''
    ): bool
    {
        return true;
    }

    static public function run(): void
    {
        $skipDefault = new SkipDefault();
        $skipDefault->dontSetParamValue();
    }
}
