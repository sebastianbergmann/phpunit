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

#[CoversClass(ExactDistribution::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ExactDistributionTest extends TestCase
{
    public function testSamplesTheGivenChoice(): void
    {
        $this->assertSame(7, new ExactDistribution(7)->sample(new Randomizer(new Xoshiro256StarStar(1)), 10));
    }

    public function testClampsTheGivenChoiceToTheMaximum(): void
    {
        $this->assertSame(3, new ExactDistribution(7)->sample(new Randomizer(new Xoshiro256StarStar(1)), 3));
    }
}
