<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\ExpectedThisType;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipThisPassedExactType;

final class CallByThis
{
    public function run(SkipThisPassedExactType $skipThisPassedExactType): void
    {
        $skipThisPassedExactType->run($this);
    }
}
