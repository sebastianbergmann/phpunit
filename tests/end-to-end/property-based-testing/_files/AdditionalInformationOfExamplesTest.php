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

use function sprintf;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class AdditionalInformationOfExamplesTest extends TestCase
{
    /**
     * @param int<0, 1000> $a
     * @param int<0, 1000> $b
     */
    #[Property]
    public function testSumIsAtMostTen(int $a, int $b): void
    {
        $this->provideAdditionalInformation(sprintf('sum of %d and %d', $a, $b));

        $this->assertLessThanOrEqual(10, $a + $b);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property(examples: 3)]
    #[TestWith([1])]
    #[TestWith([-1])]
    public function testValueIsNotNegative(int $value): void
    {
        $this->provideAdditionalInformation(sprintf('value %d', $value));

        $this->assertGreaterThanOrEqual(0, $value);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property(examples: 3)]
    public function testHolds(int $value): void
    {
        $this->provideAdditionalInformation(sprintf('value %d', $value));

        $this->assertGreaterThanOrEqual(0, $value);
    }
}
