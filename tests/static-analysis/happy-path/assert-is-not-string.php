<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotString;

use PHPUnit\Framework\Assert;

/** @param string|int $value */
function consume($value) : int
{
    Assert::assertIsNotString($value);

    return $value;
}
