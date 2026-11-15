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

final class PropertyTest extends TestCase
{
    /**
     * @param int<0, 2> $value
     */
    #[Property]
    public function testEveryExamplePerformsAssertions(int $value): void
    {
        if ($value === 0) {
            $this->assertSame(0, Doubler::double($value));

            return;
        }

        if ($value === 1) {
            $this->assertSame(3, Tripler::triple($value));

            return;
        }

        $this->assertSame(-2, Negator::negate($value));
    }

    /**
     * @param int<0, 2> $value
     */
    #[Property]
    public function testSomeExamplesDoNotPerformAssertions(int $value): void
    {
        if ($value === 0) {
            Doubler::double($value);

            return;
        }

        if ($value === 1) {
            Tripler::triple($value);

            return;
        }

        $this->assertSame(-2, Negator::negate($value));
    }
}
