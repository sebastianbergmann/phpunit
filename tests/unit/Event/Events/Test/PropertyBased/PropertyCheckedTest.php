<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(PropertyChecked::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class PropertyCheckedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();

        $event = new PropertyChecked($telemetryInfo, $test, 1234, 100, 2, 3, [['label' => 'negative', 'numberOfExamples' => 42]], [['label' => 'size', 'numberOfValues' => 100, 'minimum' => 0, 'median' => 4, 'mean' => 5.12, 'percentile90' => 11, 'maximum' => 32]], [['label' => 'sum', 'highestScore' => 1250]]);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame(1234, $event->seed());
        $this->assertSame(100, $event->numberOfExamplesChecked());
        $this->assertSame(2, $event->numberOfExplicitExamplesChecked());
        $this->assertSame(3, $event->numberOfExamplesRejected());
        $this->assertSame([['label' => 'negative', 'numberOfExamples' => 42]], $event->classifications());
        $this->assertSame([['label' => 'size', 'numberOfValues' => 100, 'minimum' => 0, 'median' => 4, 'mean' => 5.12, 'percentile90' => 11, 'maximum' => 32]], $event->observations());
        $this->assertSame([['label' => 'sum', 'highestScore' => 1250]], $event->targets());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new PropertyChecked($this->telemetryInfo(), $this->testValueObject(), 1234, 100, 0, 3, [], [], []);

        $this->assertSame('Test Property Checked (FooTest::testBar, seed: 1234, examples: 100, rejected: 3)', $event->asString());
    }

    public function testStringRepresentationHasTheNumberOfExplicitExamplesWhenThereWereAny(): void
    {
        $event = new PropertyChecked($this->telemetryInfo(), $this->testValueObject(), 1234, 100, 2, 3, [], [], []);

        $this->assertSame('Test Property Checked (FooTest::testBar, seed: 1234, examples: 100, explicit examples: 2, rejected: 3)', $event->asString());
    }

    public function testStringRepresentationHasTheNumberAndThePercentageOfTheExamplesThatWereClassifiedWithEachLabel(): void
    {
        $event = new PropertyChecked(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            8,
            0,
            0,
            [
                ['label' => 'negative', 'numberOfExamples' => 3],
                ['label' => '0', 'numberOfExamples' => 1],
            ],
            [],
            [],
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Checked (FooTest::testBar, seed: 1234, examples: 8, rejected: 0)
negative: 3 (37.5%)
0: 1 (12.5%)
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationHasTheStatisticsOfTheValuesThatWereObservedWithEachLabel(): void
    {
        $event = new PropertyChecked(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            8,
            0,
            0,
            [],
            [
                ['label' => 'size', 'numberOfValues' => 8, 'minimum' => 0, 'median' => 4, 'mean' => 4.375, 'percentile90' => 9, 'maximum' => 9],
                ['label' => 'ratio', 'numberOfValues' => 3, 'minimum' => -0.5, 'median' => 0.1, 'mean' => 0.2333333333, 'percentile90' => 1.1, 'maximum' => 1.1],
            ],
            [],
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Checked (FooTest::testBar, seed: 1234, examples: 8, rejected: 0)
size: count 8, min 0, median 4, mean 4.375, p90 9, max 9
ratio: count 3, min -0.5, median 0.1, mean 0.233333, p90 1.1, max 1.1
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationHasTheHighestScoreThatWasTargetedWithEachLabel(): void
    {
        $event = new PropertyChecked(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            8,
            0,
            0,
            [],
            [],
            [
                ['label' => 'sum', 'highestScore' => 1250],
                ['label' => 'closeness', 'highestScore' => -0.125],
            ],
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Checked (FooTest::testBar, seed: 1234, examples: 8, rejected: 0)
sum: highest targeted score 1250
closeness: highest targeted score -0.125
EOT,
            $event->asString(),
        );
    }
}
