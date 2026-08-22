<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Fixture;

use Countable;
use SplObjectStorage;

final class SkipSplObjectStorage
{
    public function create(SplObjectStorage $storage): Countable
    {
        return $storage;
    }
}
