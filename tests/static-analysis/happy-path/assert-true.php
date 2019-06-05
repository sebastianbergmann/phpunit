<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertTrue;

use PHPUnit\Framework\Assert;

/**
 * @param mixed $value
 *
 * @return true
 */
function consume($value) : bool
{
    Assert::assertTrue($value);

    return $value;
}
