<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

use DateTime;
use stdClass;

final class SkipUsedInternallyForSecondType
{
    public function run(stdClass|DateTime $obj)
    {
    }

    public function execute()
    {
        $this->run(new DateTime('now'));
    }
}
