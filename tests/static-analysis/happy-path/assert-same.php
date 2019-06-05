<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertSame;

use PHPUnit\Framework\Assert;

function consume(\stdClass $a, object $b) : \stdClass
{
    Assert::assertSame($a, $b);

    return $b;
}
