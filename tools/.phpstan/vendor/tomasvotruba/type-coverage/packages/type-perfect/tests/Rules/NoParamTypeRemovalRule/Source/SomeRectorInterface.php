<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Source;

interface SomeRectorInterface
{
    public function refactor(SomeNode $node);
}
