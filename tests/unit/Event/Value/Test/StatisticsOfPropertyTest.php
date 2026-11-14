<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Classification::class)]
#[CoversClass(ObservedValues::class)]
#[CoversClass(TargetedScore::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
final class StatisticsOfPropertyTest extends TestCase
{
    public function testClassificationHasItsLabelAndTheNumberOfExamplesThatWereClassifiedWithIt(): void
    {
        $classification = new Classification('negative', 42);

        $this->assertSame('negative', $classification->label());
        $this->assertSame(42, $classification->numberOfExamples());
    }

    public function testObservedValuesHaveTheirLabelTheirNumberAndTheirStatistics(): void
    {
        $observedValues = new ObservedValues('size', 100, 0, 4, 5.12, 11, 32);

        $this->assertSame('size', $observedValues->label());
        $this->assertSame(100, $observedValues->numberOfValues());
        $this->assertSame(0, $observedValues->minimum());
        $this->assertSame(4, $observedValues->median());
        $this->assertSame(5.12, $observedValues->mean());
        $this->assertSame(11, $observedValues->percentile90());
        $this->assertSame(32, $observedValues->maximum());
    }

    public function testTargetedScoreHasItsLabelAndTheHighestScore(): void
    {
        $targetedScore = new TargetedScore('closeness', -0.125);

        $this->assertSame('closeness', $targetedScore->label());
        $this->assertSame(-0.125, $targetedScore->highestScore());
    }
}
