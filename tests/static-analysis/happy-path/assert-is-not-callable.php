<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsCallable;

use PHPUnit\Framework\Assert;

/** @param callable|int $value */
function consume($value) : int
{
    Assert::assertIsNotCallable($value);

    return $value;
}
