<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoMixedPropertyFetcherRule\Fixture;

final class UnknownPropertyFetcher
{
    public function run($unknownType)
    {
        $unknownType->name;
    }
}
