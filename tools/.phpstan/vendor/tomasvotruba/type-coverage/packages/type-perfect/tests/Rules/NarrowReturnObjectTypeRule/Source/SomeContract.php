<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Source;

use PHPStan\Rules\Rule;

interface SomeContract
{
    public function getRule(): Rule;
}
