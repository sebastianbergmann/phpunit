<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsCallable;

use PHPUnit\Framework\Assert;

/** @param mixed $value */
function consume($value) : callable
{
    Assert::assertIsCallable($value);

    return $value;
}
