<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotFloat;

use PHPUnit\Framework\Assert;

/** @param float|int $value */
function consume($value) : int
{
    Assert::assertIsNotFloat($value);

    return $value;
}
