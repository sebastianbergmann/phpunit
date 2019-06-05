<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotEmpty;

use PHPUnit\Framework\Assert;

/** @return false */
function consume(string $value)
{
    Assert::assertNotEmpty($value);

    return $value === '';
}
