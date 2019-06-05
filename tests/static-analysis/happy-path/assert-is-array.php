<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsArray;

use PHPUnit\Framework\Assert;

/** @param mixed $value */
function consume($value) : array
{
    Assert::assertIsArray($value);

    return $value;
}
