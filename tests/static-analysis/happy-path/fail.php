<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertEmpty\Fail;

use PHPUnit\Framework\Assert;

/** @param int|string $value */
function consume($value) : int
{
    if (\is_string($value)) {
        Assert::fail();
    }

    return $value;
}
