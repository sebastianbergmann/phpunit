<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath;

use PHPUnit\Framework\Assert;

/** @return null */
function consume(?object $value)
{
    Assert::assertEmpty($value);

    return $value;
}
