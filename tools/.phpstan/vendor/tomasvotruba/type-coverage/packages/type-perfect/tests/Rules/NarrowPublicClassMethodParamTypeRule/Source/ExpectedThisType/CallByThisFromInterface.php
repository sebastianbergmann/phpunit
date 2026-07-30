<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\ExpectedThisType;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\ThisPassedFromInterface;
use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\SomeInterface;

final class CallByThisFromInterface implements SomeInterface
{
    public function run(ThisPassedFromInterface $thisPassedFromInterface): void
    {
        $thisPassedFromInterface->run($this);
    }
}
