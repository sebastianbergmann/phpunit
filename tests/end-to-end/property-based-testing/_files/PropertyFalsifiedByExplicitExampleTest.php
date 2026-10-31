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

use function abs;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class PropertyFalsifiedByExplicitExampleTest extends TestCase
{
    /**
     * Generated examples never falsify this property, but one of its
     * explicit examples does.
     *
     * @param int<0, 1000> $value
     */
    #[Property]
    #[TestWith([1], 'one')]
    #[TestWith([-1], 'minus one')]
    public function testAbsoluteValueIsTheValue(int $value): void
    {
        $this->assertSame($value, abs($value));
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    #[TestWith([0])]
    #[TestWith([-1])]
    public function testValueIsNotNegative(int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }
}
