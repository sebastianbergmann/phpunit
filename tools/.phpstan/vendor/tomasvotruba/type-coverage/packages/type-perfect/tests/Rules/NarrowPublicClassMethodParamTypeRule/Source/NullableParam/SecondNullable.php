<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\NullableParam;

use PhpParser\Node;
use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipNullableCompare;

final class SecondNullable
{
    public function run(SkipNullableCompare $skipNullableCompare, ?Node $node): void
    {
        $skipNullableCompare->callNode($node);
    }
}
