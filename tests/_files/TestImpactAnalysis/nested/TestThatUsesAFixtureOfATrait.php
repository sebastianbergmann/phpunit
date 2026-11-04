<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactAnalysis\Nested;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\TestImpactAnalysis\Money;
use PHPUnit\TestFixture\TestImpactAnalysis\TraitThatUsesAFixture;

#[CoversClass(Money::class)]
final class TestThatUsesAFixtureOfATrait extends TestCase
{
    use TraitThatUsesAFixture;
}
