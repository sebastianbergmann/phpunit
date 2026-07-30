<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Source;

interface NoTypeInterface
{
    public function noType($node);
}
