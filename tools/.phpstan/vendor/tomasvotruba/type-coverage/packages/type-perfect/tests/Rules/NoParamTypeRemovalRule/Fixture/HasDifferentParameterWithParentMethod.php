<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Fixture;

class HasDifferentParameterWithParentMethod extends ParentClass
{
    public function execute($string, $int)
    {
    }
}
