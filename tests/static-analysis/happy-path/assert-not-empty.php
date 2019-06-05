<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotEmpty;

use PHPUnit\Framework\Assert;

function consume(?int $value) : int
{
    Assert::assertNotEmpty($value);

    return $value;
}
