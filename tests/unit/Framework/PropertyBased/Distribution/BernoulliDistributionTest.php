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

#[CoversClass(BernoulliDistribution::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class BernoulliDistributionTest extends TestCase
{
    public function testSamplesOneWithTheGivenProbability(): void
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));

        $this->assertSame(1, new BernoulliDistribution(1.0)->sample($randomizer, 1));
        $this->assertSame(0, new BernoulliDistribution(0.0)->sample($randomizer, 1));
    }

    public function testSamplesBothChoices(): void
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));
        $samples    = [];

        for ($i = 0; $i < 100; $i++) {
            $samples[new BernoulliDistribution(0.5)->sample($randomizer, 1)] = true;
        }

        $this->assertArraysHaveIdenticalValuesIgnoringOrder([0, 1], array_keys($samples));
    }

    public function testSamplesZeroWhenTheRangeOnlyContainsZero(): void
    {
        $this->assertSame(0, new BernoulliDistribution(1.0)->sample(new Randomizer(new Xoshiro256StarStar(1)), 0));
    }
}
