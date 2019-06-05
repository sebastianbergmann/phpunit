<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotInt;

use PHPUnit\Framework\Assert;

/** @param float|int $value */
function consume($value) : float
{
    Assert::assertIsNotInt($value);

    return $value;
}
