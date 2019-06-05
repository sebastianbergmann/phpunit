<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotScalar;

use PHPUnit\Framework\Assert;

/** @param scalar|object $value */
function consume($value) : object
{
    Assert::assertIsNotScalar($value);

    return $value;
}
