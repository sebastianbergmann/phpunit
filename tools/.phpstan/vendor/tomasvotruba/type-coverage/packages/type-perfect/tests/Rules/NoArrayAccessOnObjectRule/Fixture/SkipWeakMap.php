<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\Fixture;

use WeakMap;

final class SkipWeakMap
{
    public function run(WeakMap $weakMap)
    {
        return $weakMap[0];
    }
}
