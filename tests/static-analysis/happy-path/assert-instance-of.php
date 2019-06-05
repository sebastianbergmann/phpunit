<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertInstanceOf;

use PHPUnit\Framework\Assert;

function consume(object $value) : \stdClass
{
    Assert::assertInstanceOf(\stdClass::class, $value);

    return $value;
}
