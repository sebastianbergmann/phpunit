<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsFloat;

use PHPUnit\Framework\Assert;

/** @param mixed $value */
function consume($value) : float
{
    Assert::assertIsFloat($value);

    return $value;
}
