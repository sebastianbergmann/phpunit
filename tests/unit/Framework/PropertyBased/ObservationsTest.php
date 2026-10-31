<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Observations::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ObservationsTest extends TestCase
{
    public function testHasNoStatisticsWhenNoValueWasObserved(): void
    {
        $observations = new Observations;

        $observations->record([]);

        $this->assertSame([], $observations->statistics());
    }

    public function testHasTheStatisticsOfTheValuesThatWereObservedWithEachLabelInTheOrderInWhichTheLabelsWereFirstUsed(): void
    {
        $observations = new Observations;

        $observations->record([['label' => 'size', 'value' => 7], ['label' => 'ratio', 'value' => 0.5]]);
        $observations->record([['label' => 'size', 'value' => 1]]);
        $observations->record([['label' => 'size', 'value' => 4], ['label' => 'ratio', 'value' => -1.5]]);

        $this->assertSame(
            [
                ['label' => 'size', 'numberOfValues' => 3, 'minimum' => 1, 'median' => 4, 'mean' => 4.0, 'percentile90' => 7, 'maximum' => 7],
                ['label' => 'ratio', 'numberOfValues' => 2, 'minimum' => -1.5, 'median' => -1.5, 'mean' => -0.5, 'percentile90' => 0.5, 'maximum' => 0.5],
            ],
            $observations->statistics(),
        );
    }

    public function testTheMedianAndThe90thPercentileAreTheSmallestValuesThatAtLeastHalfAnd90PercentOfTheValuesAreNotGreaterThan(): void
    {
        $observations = new Observations;

        for ($value = 10; $value >= 1; $value--) {
            $observations->record([['label' => 'value', 'value' => $value]]);
        }

        $observations->record([['label' => 'value', 'value' => 100]]);

        $statistics = $observations->statistics();

        $this->assertCount(1, $statistics);
        $this->assertSame(11, $statistics[0]['numberOfValues']);
        $this->assertSame(6, $statistics[0]['median']);
        $this->assertSame(10, $statistics[0]['percentile90']);
        $this->assertSame(100, $statistics[0]['maximum']);
        $this->assertSame(155 / 11, $statistics[0]['mean']);
    }

    public function testAllStatisticsOfASingleValueAreThatValue(): void
    {
        $observations = new Observations;

        $observations->record([['label' => 'value', 'value' => 3]]);

        $this->assertSame(
            [['label' => 'value', 'numberOfValues' => 1, 'minimum' => 3, 'median' => 3, 'mean' => 3.0, 'percentile90' => 3, 'maximum' => 3]],
            $observations->statistics(),
        );
    }
}
