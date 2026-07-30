<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Fixture;

use PHPStan\Rules\Cast\EchoRule;
use PHPStan\Rules\Rule;
use Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Source\SomeContract;

final class SkipSomeContract implements SomeContract
{
    public function getRule(): Rule
    {
        return new EchoRule();
    }
}
