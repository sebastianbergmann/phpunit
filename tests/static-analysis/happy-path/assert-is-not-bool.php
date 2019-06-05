<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotBool;

use PHPUnit\Framework\Assert;

/** @param bool|int $value */
function consume($value) : int
{
    Assert::assertIsNotBool($value);

    return $value;
}
