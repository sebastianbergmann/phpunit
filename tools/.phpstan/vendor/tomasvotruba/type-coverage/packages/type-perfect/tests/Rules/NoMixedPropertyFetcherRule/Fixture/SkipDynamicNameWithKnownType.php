<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoMixedPropertyFetcherRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NoMixedPropertyFetcherRule\Source\KnownType;

final class SkipDynamicNameWithKnownType
{
    public function runOnType(KnownType $knownType)
    {
        $knownType->{$name};
    }
}
