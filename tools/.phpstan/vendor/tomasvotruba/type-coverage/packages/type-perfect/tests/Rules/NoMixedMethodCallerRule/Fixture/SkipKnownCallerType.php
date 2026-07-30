<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoMixedMethodCallerRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NoMixedMethodCallerRule\Source\KnownType;

final class SkipKnownCallerType
{
    public function run(KnownType $knownType)
    {
        $knownType->call();
    }
}
