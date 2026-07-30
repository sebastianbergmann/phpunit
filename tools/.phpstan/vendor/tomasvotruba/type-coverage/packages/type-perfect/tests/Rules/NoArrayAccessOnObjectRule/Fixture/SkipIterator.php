<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\Fixture;

final class SkipIterator
{
    public function run()
    {
        $iterator = new class extends \Iterator {};

        return $iterator[0];
    }
}
