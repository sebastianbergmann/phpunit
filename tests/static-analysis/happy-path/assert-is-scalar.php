<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsScalar;

use PHPUnit\Framework\Assert;

/**
 * @param mixed $value
 *
 * @psalm-return scalar
 */
function consume($value) : string
{
    Assert::assertIsScalar($value);

    return $value;
}
