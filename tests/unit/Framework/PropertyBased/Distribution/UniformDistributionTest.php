<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Distribution;

use function array_keys;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

#[CoversClass(UniformDistribution::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class UniformDistributionTest extends TestCase
{
    public function testSamplesEveryChoiceInTheRange(): void
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));
        $samples    = [];

        for ($i = 0; $i < 200; $i++) {
            $samples[(new UniformDistribution)->sample($randomizer, 3)] = true;
        }

        $this->assertArraysHaveIdenticalValuesIgnoringOrder([0, 1, 2, 3], array_keys($samples));
    }
}
