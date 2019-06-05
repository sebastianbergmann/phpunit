<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotFalse;

use PHPUnit\Framework\Assert;

/** @param int|false $value */
function consume($value) : int
{
    Assert::assertNotFalse($value);

    return $value;
}
