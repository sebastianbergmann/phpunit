<?php declare(strict_types=1);

namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsResource;

use PHPUnit\Framework\Assert;

/**
 * @param mixed $value
 *
 * @return resource
 */
function consume($value)
{
    Assert::assertIsResource($value);

    return $value;
}
