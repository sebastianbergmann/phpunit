<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Source\AbstractControl;
use Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Source\SpecificControl;

final class SomeAbstractReturnType
{
    public function create(): AbstractControl
    {
        return new SpecificControl();
    }
}
