<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsIterable;

use PHPUnit\Framework\Assert;

/** @param mixed $value */
function consume($value) : iterable
{
    Assert::assertIsIterable($value);

    return $value;
}
