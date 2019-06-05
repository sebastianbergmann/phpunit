<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotIterable;

use PHPUnit\Framework\Assert;

/** @param iterable|int $value */
function consume($value) : int
{
    Assert::assertIsNotIterable($value);

    return $value;
}
