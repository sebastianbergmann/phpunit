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

use const PHP_INT_MAX;
use function array_filter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

#[CoversClass(BoundaryDistribution::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class BoundaryDistributionTest extends TestCase
{
    public function testSamplesChoicesWithinTheRange(): void
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));

        for ($i = 0; $i < 1000; $i++) {
            $choice = (new BoundaryDistribution)->sample($randomizer, 1000);

            $this->assertGreaterThanOrEqual(0, $choice);
            $this->assertLessThanOrEqual(1000, $choice);
        }
    }

    public function testSamplesTheBoundariesSmallChoicesAndChoicesOfEveryMagnitude(): void
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));
        $samples    = [];

        for ($i = 0; $i < 2000; $i++) {
            $samples[] = (new BoundaryDistribution)->sample($randomizer, PHP_INT_MAX);
        }

        $this->assertContains(0, $samples);
        $this->assertContains(1, $samples);
        $this->assertContains(PHP_INT_MAX, $samples);
        $this->assertNotEmpty(array_filter($samples, static fn (int $choice): bool => $choice > 1000 && $choice < 1000000));
        $this->assertNotEmpty(array_filter($samples, static fn (int $choice): bool => $choice > 1000000000000 && $choice < PHP_INT_MAX));
    }

    public function testSamplesZeroWhenTheRangeOnlyContainsZero(): void
    {
        $this->assertSame(0, (new BoundaryDistribution)->sample(new Randomizer(new Xoshiro256StarStar(1)), 0));
    }
}
