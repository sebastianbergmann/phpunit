<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotTrue;

use PHPUnit\Framework\Assert;

/** @param int|true $value */
function consume($value) : int
{
    Assert::assertNotTrue($value);

    return $value;
}
