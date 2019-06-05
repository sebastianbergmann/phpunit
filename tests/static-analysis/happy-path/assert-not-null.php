<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotNull;

use PHPUnit\Framework\Assert;

function consume(?int $value) : int
{
    Assert::assertNotNull($value);

    return $value;
}
