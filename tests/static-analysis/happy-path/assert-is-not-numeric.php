<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotNumeric;

use PHPUnit\Framework\Assert;

/** @param numeric|array $value */
function consume($value) : array
{
    Assert::assertIsNotNumeric($value);

    return $value;
}
