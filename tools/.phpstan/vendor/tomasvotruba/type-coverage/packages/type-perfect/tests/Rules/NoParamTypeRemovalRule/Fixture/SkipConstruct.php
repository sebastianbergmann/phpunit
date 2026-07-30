<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule\Fixture;

class SkipConstruct extends ParentConstructorClass
{
    public function __construct($someArg, $someOther)
    {
        parent::__construct('foo', 4);
    }
}

class ParentConstructorClass
{
    public function __construct(string $string, int $int)
    {
    }
}
