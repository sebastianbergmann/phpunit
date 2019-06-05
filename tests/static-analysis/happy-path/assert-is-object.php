<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsObject;

use PHPUnit\Framework\Assert;

/** @param mixed $value */
function consume($value) : object
{
    Assert::assertIsObject($value);

    return $value;
}
