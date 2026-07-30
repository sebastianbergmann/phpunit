<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoMixedMethodCallerRule\Fixture;

final class UnknownCallerType
{
    public function run($mixedType)
    {
        $mixedType->call();
    }
}
