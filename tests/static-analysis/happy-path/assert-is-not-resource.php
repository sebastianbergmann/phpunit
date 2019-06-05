<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotResource;

use PHPUnit\Framework\Assert;

/** @param resource|int $value */
function consume($value) : int
{
    Assert::assertIsNotResource($value);

    return $value;
}
