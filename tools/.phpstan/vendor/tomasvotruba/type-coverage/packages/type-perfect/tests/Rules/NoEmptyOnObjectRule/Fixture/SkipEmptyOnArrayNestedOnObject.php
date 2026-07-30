<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoEmptyOnObjectRule\Fixture;

use PhpParser\Node\Expr\MethodCall;

final class SkipEmptyOnArrayNestedOnObject
{
    public function run(MethodCall $methodCall)
    {
        if (!empty($methodCall->args[9])) {
            return $methodCall->args[9];
        }
    }
}
