<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoMixedPropertyFetcherRule\Fixture;

final class DynamicName
{
    public function run($unknownType)
    {
        $unknownType->{$name};
    }
}
