<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use const PHP_EOL;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Test\PropertyChecked;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(PropertyCheckedToStringMapper::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
#[TestDox('PropertyCheckedToStringMapper')]
final class PropertyCheckedToStringMapperTest extends AbstractEventTestCase
{
    public function testDescribesHowManyExamplesWereCheckedAndRejected(): void
    {
        $this->assertSame(
            '100 examples checked, 3 rejected' . PHP_EOL,
            PropertyCheckedToStringMapper::map($this->event(100, 3, [], [], [])),
        );
    }

    public function testUsesTheSingularForOneExample(): void
    {
        $this->assertSame(
            '1 example checked, 0 rejected' . PHP_EOL,
            PropertyCheckedToStringMapper::map($this->event(1, 0, [], [], [])),
        );
    }

    public function testDescribesThePercentageOfTheExamplesThatWereClassifiedWithEachLabelFromTheLargestToTheSmallest(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
100 examples checked, 3 rejected

     52.0% even
     48.0% odd
      1.0% zero

EOT,
            PropertyCheckedToStringMapper::map(
                $this->event(
                    100,
                    3,
                    [
                        ['label' => 'zero', 'numberOfExamples' => 1],
                        ['label' => 'odd', 'numberOfExamples' => 48],
                        ['label' => 'even', 'numberOfExamples' => 52],
                    ],
                    [],
                    [],
                ),
            ),
        );
    }

    public function testDescribesTheStatisticsOfTheValuesThatWereObservedWithEachLabelAfterTheClassifications(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
100 examples checked, 3 rejected

    100.0% any

    size: count 100, min 0, median 4, mean 5.12, p90 11, max 32
    ratio: count 7, min -1.5, median 0.25, mean 0.333333, p90 2, max 2

EOT,
            PropertyCheckedToStringMapper::map(
                $this->event(
                    100,
                    3,
                    [['label' => 'any', 'numberOfExamples' => 100]],
                    [
                        ['label' => 'size', 'numberOfValues' => 100, 'minimum' => 0, 'median' => 4, 'mean' => 5.12, 'percentile90' => 11, 'maximum' => 32],
                        ['label' => 'ratio', 'numberOfValues' => 7, 'minimum' => -1.5, 'median' => 0.25, 'mean' => 0.33333333, 'percentile90' => 2.0, 'maximum' => 2.0],
                    ],
                    [],
                ),
            ),
        );
    }

    public function testDescribesTheHighestScoreThatWasTargetedWithEachLabelAfterTheObservations(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
100 examples checked, 3 rejected

    size: count 100, min 0, median 4, mean 5.12, p90 11, max 32

    sum: highest targeted score 1250
    closeness: highest targeted score -0.125

EOT,
            PropertyCheckedToStringMapper::map(
                $this->event(
                    100,
                    3,
                    [],
                    [['label' => 'size', 'numberOfValues' => 100, 'minimum' => 0, 'median' => 4, 'mean' => 5.12, 'percentile90' => 11, 'maximum' => 32]],
                    [
                        ['label' => 'sum', 'highestScore' => 1250],
                        ['label' => 'closeness', 'highestScore' => -0.125],
                    ],
                ),
            ),
        );
    }

    public function testDescribesTheStatisticsOfObservedValuesWithoutClassifications(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
1 example checked, 0 rejected

    size: count 1, min 3, median 3, mean 3, p90 3, max 3

EOT,
            PropertyCheckedToStringMapper::map(
                $this->event(1, 0, [], [['label' => 'size', 'numberOfValues' => 1, 'minimum' => 3, 'median' => 3, 'mean' => 3.0, 'percentile90' => 3, 'maximum' => 3]], []),
            ),
        );
    }

    /**
     * @param non-negative-int                                                                                                                                                    $numberOfExamplesChecked
     * @param non-negative-int                                                                                                                                                    $numberOfExamplesRejected
     * @param list<array{label: non-empty-string, numberOfExamples: positive-int}>                                                                                                $classifications
     * @param list<array{label: non-empty-string, numberOfValues: positive-int, minimum: float|int, median: float|int, mean: float, percentile90: float|int, maximum: float|int}> $observations
     * @param list<array{label: non-empty-string, highestScore: float|int}>                                                                                                       $targets
     */
    private function event(int $numberOfExamplesChecked, int $numberOfExamplesRejected, array $classifications, array $observations, array $targets): PropertyChecked
    {
        return new PropertyChecked(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            $numberOfExamplesChecked,
            $numberOfExamplesRejected,
            $classifications,
            $observations,
            $targets,
        );
    }
}
