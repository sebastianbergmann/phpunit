<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Source\SomeRectorInterface;

final class RemoveParentType implements SomeRectorInterface
{
    public function refactor($node)
    {
    }
}
