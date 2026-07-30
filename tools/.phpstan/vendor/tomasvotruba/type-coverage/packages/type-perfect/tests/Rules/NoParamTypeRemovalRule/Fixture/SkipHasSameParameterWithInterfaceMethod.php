<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Fixture;

use stdClass;

class SkipHasSameParameterWithInterfaceMethod implements AnInterface, AnInterfaceOther
{
    public function execute(string $string, int $int)
    {
    }

    public function run(stdClass $stdClass)
    {
    }
}
