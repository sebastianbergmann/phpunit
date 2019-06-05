<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertFalse;

use PHPUnit\Framework\Assert;

/**
 * @param mixed $value
 *
 * @return false
 */
function consume($value)
{
    Assert::assertFalse($value);

    return $value;
}
