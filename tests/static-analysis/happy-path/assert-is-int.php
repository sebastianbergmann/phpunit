<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsInt;

use PHPUnit\Framework\Assert;

/** @param mixed $value */
function consume($value) : int
{
    Assert::assertIsInt($value);

    return $value;
}
