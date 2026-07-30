<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\ExpectedClassString;

use PHPStan\Reflection\ClassReflection;
use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\SkipClassStringPassed;

final class FirstTypedCaller
{
    public function run(ClassReflection $classReflection)
    {
        $skipClassStringPassed = new SkipClassStringPassed();
        $skipClassStringPassed->resolve($classReflection->getName());
    }
}
