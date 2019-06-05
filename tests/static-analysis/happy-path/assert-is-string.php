<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsString;

use PHPUnit\Framework\Assert;

/** @param mixed $value */
function consume($value) : string
{
    Assert::assertIsString($value);

    return $value;
}
