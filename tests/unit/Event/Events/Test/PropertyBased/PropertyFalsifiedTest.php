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

#[CoversClass(PropertyFalsified::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class PropertyFalsifiedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();

        $event = new PropertyFalsified(
            $telemetryInfo,
            $test,
            1234,
            2,
            40,
            17,
            false,
            false,
            [0, 2, 1],
            ['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2'],
            null,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame(1234, $event->seed());
        $this->assertSame(2, $event->numberOfExamplesChecked());
        $this->assertSame(40, $event->numberOfShrinkingAttempts());
        $this->assertSame(17, $event->numberOfShrinkingSteps());
        $this->assertFalse($event->shrinkingBudgetExhausted());
        $this->assertFalse($event->shrinkingInterrupted());
        $this->assertSame([0, 2, 1], $event->choiceSequence());
        $this->assertSame(['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2'], $event->counterexample());
        $this->assertNull($event->explicitExample());
    }

    public function testHasTheNameOfTheExplicitExampleThatFalsifiedTheProperty(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 0, 0, 0, false, false, [], ['value' => '5'], 'five');

        $this->assertSame('five', $event->explicitExample());
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
            [0, 2, 1],
            ['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2'],
            null,
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
            [5],
            ['value' => '5'],
            null,
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
            [5],
            ['value' => '5'],
            null,
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
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 0, 0, 0, false, false, [], ['value' => '5'], 'five');

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
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 0, 0, 0, false, false, [], ['value' => '5'], 1);

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
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 1, 0, 0, false, false, [], [], null);

        $this->assertSame('Test Property Falsified (FooTest::testBar, seed: 1234, examples: 1, shrinking steps: 0, shrinking attempts: 0)', $event->asString());
    }
}
