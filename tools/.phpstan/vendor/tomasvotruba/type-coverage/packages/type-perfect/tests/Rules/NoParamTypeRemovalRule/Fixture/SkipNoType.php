<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Source\NoTypeInterface;

final class SkipNoType implements NoTypeInterface
{
    public function noType($node)
    {
    }
}
