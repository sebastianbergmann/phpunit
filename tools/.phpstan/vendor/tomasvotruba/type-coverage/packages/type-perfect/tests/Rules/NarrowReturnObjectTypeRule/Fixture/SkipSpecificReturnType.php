<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Source\SpecificControl;

final class SkipSpecificReturnType
{
    public function create(): SpecificControl
    {
        return new SpecificControl();
    }
}
