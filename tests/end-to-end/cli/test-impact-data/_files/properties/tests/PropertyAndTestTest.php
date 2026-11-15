<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData\Properties;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class PropertyAndTestTest extends TestCase
{
    /**
     * @param int<0, 2> $value
     */
    #[Property]
    public function testDoubles(int $value): void
    {
        $this->assertSame($value + $value, Doubler::double($value));
    }

    public function testNegates(): void
    {
        $this->assertSame(-1, Negator::negate(1));
    }
}
