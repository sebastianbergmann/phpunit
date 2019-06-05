<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotInstanceOf;

use PHPUnit\Framework\Assert;

class A {}
class B {}

/** @param A|B $value */
function consume(object $value) : B
{
    Assert::assertNotInstanceOf(A::class, $value);

    return $value;
}
