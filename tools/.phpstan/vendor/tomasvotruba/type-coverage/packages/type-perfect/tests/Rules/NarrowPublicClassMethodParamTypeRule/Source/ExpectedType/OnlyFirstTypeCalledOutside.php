<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\ExpectedType;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipUsedInternallyForSecondType;
use stdClass;

final class OnlyFirstTypeCalledOutside
{
    public function run(SkipUsedInternallyForSecondType $skipUsedInternallyForSecondType)
    {
        $skipUsedInternallyForSecondType->run(new stdClass());
    }
}
