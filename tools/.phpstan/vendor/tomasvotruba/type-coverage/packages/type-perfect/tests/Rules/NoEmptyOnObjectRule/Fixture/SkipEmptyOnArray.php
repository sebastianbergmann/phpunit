<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoEmptyOnObjectRule\Fixture;

final class SkipEmptyOnArray
{
    public function run(array $values)
    {
        if (!empty($values[9])) {
            return $values[9];
        }
    }
}
