<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotArray;

use PHPUnit\Framework\Assert;

/** @param array|int $value */
function consume($value) : int
{
    Assert::assertIsNotArray($value);

    return $value;
}
