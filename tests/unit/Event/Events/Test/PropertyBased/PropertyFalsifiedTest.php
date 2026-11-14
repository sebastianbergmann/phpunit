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
use PHPUnit\Event\Code\Counterexample;
use PHPUnit\Event\Code\DrawnValue;
use PHPUnit\Event\Code\OtherFalsification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(PropertyFalsified::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class PropertyFalsifiedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo  = $this->telemetryInfo();
        $test           = $this->testValueObject();
        $counterexample = new Counterexample([0, 2, 1], ['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2'], null, [], []);

        $event = new PropertyFalsified(
            $telemetryInfo,
            $test,
            1234,
            2,
            40,
            17,
            false,
            false,
            $counterexample,
            [],
            0,
            0,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame(1234, $event->seed());
        $this->assertSame(2, $event->numberOfExamplesChecked());
        $this->assertSame(40, $event->numberOfShrinkingAttempts());
        $this->assertSame(17, $event->numberOfShrinkingSteps());
        $this->assertFalse($event->shrinkingBudgetExhausted());
        $this->assertFalse($event->shrinkingInterrupted());
        $this->assertSame($counterexample, $event->counterexample());
        $this->assertSame([], $event->otherFalsifications());
    }

    public function testHasHowOftenTheExampleThatFalsifiedThePropertyWasReplayedAndFalsifiedItAgain(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 2, 3, 1, false, false, new Counterexample([5], ['size' => '5'], null, [], []), [], 4, 2);

        $this->assertSame(4, $event->numberOfReplays());
        $this->assertSame(2, $event->numberOfReproductions());
    }

    public function testStringRepresentationStatesHowOftenTheExampleThatFalsifiedThePropertyFalsifiedItAgainWhenThatWasNotEveryTime(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 2, 3, 1, false, false, new Counterexample([5], ['size' => '5'], null, [], []), [], 4, 2);

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, seed: 1234, examples: 2, shrinking steps: 1, shrinking attempts: 3, reproduced in 2 of 4 replays)
$size = 5;
EOT,
            $event->asString(),
        );
    }

    public function testHasTheOtherWaysInWhichThePropertyWasFalsified(): void
    {
        $otherFalsification = new OtherFalsification('RuntimeException at /path/to/FooTest.php:12', new Counterexample([3, 0], ['size' => '3'], null, [new DrawnValue(null, '0')], []));
        $event              = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 2, 3, 1, false, false, new Counterexample([5], ['size' => '5'], null, [], []), [$otherFalsification], 0, 0);

        $this->assertSame([$otherFalsification], $event->otherFalsifications());
    }

    public function testStringRepresentationHasTheOtherWaysInWhichThePropertyWasFalsified(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 2, 3, 1, false, false, new Counterexample([5], ['size' => '5'], null, [], []), [new OtherFalsification('RuntimeException at /path/to/FooTest.php:12', new Counterexample([3, 0], ['size' => '3'], null, [new DrawnValue(null, '0')], []))], 0, 0);

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, seed: 1234, examples: 2, shrinking steps: 1, shrinking attempts: 3)
$size = 5;
Also falsified by RuntimeException at /path/to/FooTest.php:12:
$size = 3;
Draw 1: 0
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationHasTheValuesThatTheMinimalCounterexampleDrew(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 2, 3, 1, false, false, new Counterexample([1, 5, 0], ['size' => '1'], null, [new DrawnValue('list', '[5]'), new DrawnValue(null, '0')], []), [], 0, 0);

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, seed: 1234, examples: 2, shrinking steps: 1, shrinking attempts: 3)
$size = 1;
Draw 1 (list): [5]
Draw 2: 0
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationHasWhatTheCounterexampleFormatterReturnedForTheArgumentsOfTheMinimalCounterexample(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 2, 3, 1, false, false, new Counterexample([1, 5, 0], ['size' => '1'], null, [new DrawnValue('list', '[5]')], [], 'a list with one element'), [], 0, 0);

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, seed: 1234, examples: 2, shrinking steps: 1, shrinking attempts: 3)
$size = 1;
a list with one element
Draw 1 (list): [5]
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationHasTheStepsOfTheStateMachineThatTheMinimalCounterexamplesRan(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 2, 3, 1, false, false, new Counterexample([1, 0, 0], [], null, [], ['$accounts1 = $machine->open();', '$machine->close(account: $accounts1);']), [new OtherFalsification('RuntimeException at /path/to/FooTest.php:12', new Counterexample([1, 1, 0], [], null, [], ['$machine->audit();']))], 0, 0);

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, seed: 1234, examples: 2, shrinking steps: 1, shrinking attempts: 3)
$accounts1 = $machine->open();
$machine->close(account: $accounts1);
Also falsified by RuntimeException at /path/to/FooTest.php:12:
$machine->audit();
EOT,
            $event->asString(),
        );
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new PropertyFalsified(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            2,
            40,
            17,
            false,
            false,
            new Counterexample([0, 2, 1], ['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2'], null, [], []),
            [],
            0,
            0,
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, seed: 1234, examples: 2, shrinking steps: 17, shrinking attempts: 40)
$money = new Money(amount: 1, currency: Currency::EUR);
$parts = 2;
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationStatesWhenTheShrinkingBudgetWasExhausted(): void
    {
        $event = new PropertyFalsified(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            7,
            1000,
            23,
            true,
            false,
            new Counterexample([5], ['value' => '5'], null, [], []),
            [],
            0,
            0,
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, seed: 1234, examples: 7, shrinking steps: 23, shrinking attempts: 1000, budget exhausted)
$value = 5;
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationStatesWhenShrinkingWasInterrupted(): void
    {
        $event = new PropertyFalsified(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            7,
            12,
            3,
            false,
            true,
            new Counterexample([5], ['value' => '5'], null, [], []),
            [],
            0,
            0,
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, seed: 1234, examples: 7, shrinking steps: 3, shrinking attempts: 12, shrinking interrupted)
$value = 5;
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationOfAPropertyThatWasFalsifiedByANamedExplicitExampleHasItsName(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 0, 0, 0, false, false, new Counterexample([], ['value' => '5'], 'five', [], []), [], 0, 0);

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, explicit example "five")
$value = 5;
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationOfAPropertyThatWasFalsifiedByAnExplicitExampleWithoutNameHasItsNumber(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 0, 0, 0, false, false, new Counterexample([], ['value' => '5'], 1, [], []), [], 0, 0);

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, explicit example #1)
$value = 5;
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationOfAPropertyWithoutParametersHasNoCounterexample(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 1, 0, 0, false, false, new Counterexample([], [], null, [], []), [], 0, 0);

        $this->assertSame('Test Property Falsified (FooTest::testBar, seed: 1234, examples: 1, shrinking steps: 0, shrinking attempts: 0)', $event->asString());
    }
}
