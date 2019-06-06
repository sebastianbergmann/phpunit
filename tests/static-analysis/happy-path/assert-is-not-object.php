<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertIsNotObject;

use PHPUnit\Framework\Assert;

/** @param int|object $value */
function consume($value): int
{
    Assert::assertIsNotObject($value);

    return $value;
}
