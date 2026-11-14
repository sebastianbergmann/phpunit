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

final class IntegerTypesTest extends TestCase
{
    #[Property]
    public function testInt(int $value): void
    {
        $this->assertIsInt($value);
    }

    /**
     * @param int<-5, 5> $value
     */
    #[Property]
    public function testIntegerRange(int $value): void
    {
        $this->assertGreaterThanOrEqual(-5, $value);
        $this->assertLessThanOrEqual(5, $value);
    }

    /**
     * @param int<min, -10> $value
     */
    #[Property]
    public function testIntegerRangeWithoutMinimum(int $value): void
    {
        $this->assertLessThanOrEqual(-10, $value);
    }

    /**
     * @param int<10, max> $value
     */
    #[Property]
    public function testIntegerRangeWithoutMaximum(int $value): void
    {
        $this->assertGreaterThanOrEqual(10, $value);
    }

    /**
     * @param positive-int $value
     */
    #[Property]
    public function testPositiveInt(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }

    /**
     * @param negative-int $value
     */
    #[Property]
    public function testNegativeInt(int $value): void
    {
        $this->assertLessThan(0, $value);
    }

    /**
     * @param non-negative-int $value
     */
    #[Property]
    public function testNonNegativeInt(int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }

    /**
     * @param non-positive-int $value
     */
    #[Property]
    public function testNonPositiveInt(int $value): void
    {
        $this->assertLessThanOrEqual(0, $value);
    }

    /**
     * @param non-zero-int $value
     */
    #[Property]
    public function testNonZeroInt(int $value): void
    {
        $this->assertNotSame(0, $value);
    }
}
