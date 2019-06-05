<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotObject;

use PHPUnit\Framework\Assert;

/** @param object|int $value */
function consume($value) : int
{
    Assert::assertIsNotObject($value);

    return $value;
}
