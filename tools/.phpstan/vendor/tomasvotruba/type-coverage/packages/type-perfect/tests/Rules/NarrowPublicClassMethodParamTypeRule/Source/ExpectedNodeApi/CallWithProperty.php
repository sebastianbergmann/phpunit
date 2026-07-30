<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\ExpectedNodeApi;

use PhpParser\Node\Stmt\Property;
use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipApiMarked;

final class CallWithProperty
{
    public function run(SkipApiMarked $skipApiMarked, Property $property): void
    {
        $skipApiMarked->callNode($property);
    }

}
