<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

class HandleDefaultValue {
    public function takesUnionWithDifferentDefaultValue(
        int|string $value = "hallo"
    ): bool
    {
        return true;
    }

    public function takesUnionWithSameTypeDefaultValue(
        int|string $value = 123
    ): bool
    {
        return true;
    }

    static public function run(): void
    {
        $o = new HandleDefaultValue();
        $o->takesUnionWithDifferentDefaultValue(1);

        $o = new HandleDefaultValue();
        $o->takesUnionWithSameTypeDefaultValue(1);
    }
}
