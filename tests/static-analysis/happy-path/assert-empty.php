<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath;

use PHPUnit\Framework\Assert;

/** @return false */
function consume(string $value)
{
    Assert::assertEmpty($value);

    return $value === 'a non-empty string';
}
