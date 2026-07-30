<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Fixture;

use stdClass;

interface AnInterfaceOther
{
    public function run(stdClass $stdClass);
}
