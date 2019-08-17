<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsIterable;

use PHPUnit\Framework\Assert;

/** @param mixed $value */
function consume($value): iterable
{
    Assert::assertIsIterable($value);

    return $value;
}
