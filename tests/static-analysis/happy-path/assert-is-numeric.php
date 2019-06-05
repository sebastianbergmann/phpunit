<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNumeric;

use PHPUnit\Framework\Assert;

/**
 * @param mixed $value
 *
 * @return numeric
 */
function consume($value)
{
    Assert::assertIsNumeric($value);

    return $value;
}
