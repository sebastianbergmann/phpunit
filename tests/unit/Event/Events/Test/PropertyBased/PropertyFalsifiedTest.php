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
            [0, 2, 1],
            ['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2'],
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame(1234, $event->seed());
        $this->assertSame(2, $event->numberOfExamplesChecked());
        $this->assertSame(40, $event->numberOfShrinkingAttempts());
        $this->assertSame(17, $event->numberOfShrinkingSteps());
        $this->assertFalse($event->shrinkingBudgetExhausted());
        $this->assertSame([0, 2, 1], $event->choiceSequence());
        $this->assertSame(['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2'], $event->counterexample());
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
            [0, 2, 1],
            ['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2'],
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
            [5],
            ['value' => '5'],
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Property Falsified (FooTest::testBar, seed: 1234, examples: 7, shrinking steps: 23, shrinking attempts: 1000, budget exhausted)
$value = 5;
EOT,
            $event->asString(),
        );
    }

    public function testStringRepresentationOfAPropertyWithoutParametersHasNoCounterexample(): void
    {
        $event = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 1, 0, 0, false, [], []);

        $this->assertSame('Test Property Falsified (FooTest::testBar, seed: 1234, examples: 1, shrinking steps: 0, shrinking attempts: 0)', $event->asString());
    }
}
