<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsBool;

use PHPUnit\Framework\Assert;

/** @param mixed $value */
function consume($value) : bool
{
    Assert::assertIsBool($value);

    return $value;
}
