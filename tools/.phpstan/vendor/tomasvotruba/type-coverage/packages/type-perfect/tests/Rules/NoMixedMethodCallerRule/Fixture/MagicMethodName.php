<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoMixedMethodCallerRule\Fixture;

final class MagicMethodName
{
    public function run($someType, $magic)
    {
        $someType->$magic();
    }
}
