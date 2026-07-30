<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\PickReasonEnum;

class SkipEnum
{
    public function pick(PickReasonEnum $pickReasonEnum): void
    {
    }

    public function run()
    {
        $this->pick(PickReasonEnum::BarcodeIssue);
    }
}