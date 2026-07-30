<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

use PhpParser\Node;

final class SkipNullableCompare
{
    public function callNode(?Node $node)
    {
    }
}
