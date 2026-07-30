<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Fixture;

interface AnInterface
{
    public function execute(string $string, int $int);
}
