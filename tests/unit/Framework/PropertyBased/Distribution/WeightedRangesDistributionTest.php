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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

#[CoversClass(WeightedRangesDistribution::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class WeightedRangesDistributionTest extends TestCase
{
    public function testSamplesFromTheRangesAccordingToTheirWeights(): void
    {
        $distribution = new WeightedRangesDistribution(
            [
                ['weight' => 1.0, 'minimum' => 10, 'maximum' => 19],
                ['weight' => 0.0, 'minimum' => 20, 'maximum' => 29],
            ],
        );

        $randomizer = new Randomizer(new Xoshiro256StarStar(1));

        for ($i = 0; $i < 100; $i++) {
            $choice = $distribution->sample($randomizer, 100);

            $this->assertGreaterThanOrEqual(10, $choice);
            $this->assertLessThanOrEqual(19, $choice);
        }
    }

    public function testIgnoresRangesThatStartAboveTheMaximumAndCutsOffRangesThatExtendBeyondIt(): void
    {
        $distribution = new WeightedRangesDistribution(
            [
                ['weight' => 1.0, 'minimum' => 50, 'maximum' => 59],
                ['weight' => 1.0, 'minimum' => 3, 'maximum' => 40],
            ],
        );

        $randomizer = new Randomizer(new Xoshiro256StarStar(1));

        for ($i = 0; $i < 100; $i++) {
            $choice = $distribution->sample($randomizer, 5);

            $this->assertGreaterThanOrEqual(3, $choice);
            $this->assertLessThanOrEqual(5, $choice);
        }
    }

    public function testSamplesUniformlyWhenAllRangesStartAboveTheMaximum(): void
    {
        $distribution = new WeightedRangesDistribution(
            [
                ['weight' => 1.0, 'minimum' => 50, 'maximum' => 59],
            ],
        );

        $randomizer = new Randomizer(new Xoshiro256StarStar(1));

        for ($i = 0; $i < 100; $i++) {
            $this->assertLessThanOrEqual(5, $distribution->sample($randomizer, 5));
        }
    }
}
