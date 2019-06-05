<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNull;

use PHPUnit\Framework\Assert;

/**
 * @param mixed $value
 *
 * @return null
 */
function consume($value)
{
    Assert::assertNull($value);

    return $value;
}
