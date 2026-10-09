<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class RememberedCounterexampleTest extends TestCase
{
    /**
     * Random examples practically never falsify this property, but its
     * counterexample is remembered in the test run history.
     */
    #[Property(examples: 10)]
    public function testValueIsNotMagic(int $value): void
    {
        $this->assertNotSame(12345, $value);
    }

    #[Property(examples: 10)]
    public function testHolds(int $value): void
    {
        $this->assertIsInt($value);
    }

    /**
     * @param int<0, 1000> $a
     * @param int<0, 1000> $b
     */
    #[Property]
    public function testSumIsAtMostTen(int $a, int $b): void
    {
        $this->assertLessThanOrEqual(10, $a + $b);
    }
}
