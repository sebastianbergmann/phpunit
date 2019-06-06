<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotEmpty;

use PHPUnit\Framework\Assert;

function consume(?int $value): int
{
    Assert::assertNotEmpty($value);

    return $value;
}
