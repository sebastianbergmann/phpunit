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

final class FalsifiedPropertyTest extends TestCase
{
    /**
     * Tripler::triple() is only called for the minimal counterexample, which
     * is found while the property is shrunk.
     *
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testIsFalsifiedByAnExampleThatIsShrunk(int $value): void
    {
        if ($value === 100) {
            Tripler::triple($value);
        }

        $this->assertLessThan(200, Doubler::double($value));
    }

    public function testNegates(): void
    {
        $this->assertSame(-1, Negator::negate(1));
    }
}
