<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Fixture;

use Countable;
use Doctrine\Common\Collections\Collection;

final class SkipCollection
{
    public function create(Collection $collection): Countable
    {
        return $collection;
    }
}
