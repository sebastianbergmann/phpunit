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

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Test\PropertyFalsified;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(PropertyFalsifiedToStringMapper::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
#[TestDox('PropertyFalsifiedToStringMapper')]
final class PropertyFalsifiedToStringMapperTest extends AbstractEventTestCase
{
    public function testDescribesHowAPropertyWasFalsifiedWithItsMinimalCounterexample(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 2 examples, counterexample shrunk in 17 steps:

    $money = new Money(amount: 1, currency: Currency::EUR);
    $parts = 2;

EOT,
            PropertyFalsifiedToStringMapper::map(
                $this->event(2, 40, 17, false, false, ['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2']),
            ),
        );
    }

    public function testUsesTheSingularForOneExampleAndOneStep(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 1 example, counterexample shrunk in 1 step:

    $value = 0;

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(1, 3, 1, false, false, ['value' => '0'])),
        );
    }

    public function testDoesNotMentionShrinkingWhenTheCounterexampleWasNotShrunk(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 3 examples with this counterexample:

    $value = -7;

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(3, 4, 0, false, false, ['value' => '-7'])),
        );
    }

    public function testIndentsEveryLineOfAValueThatIsExportedOnSeveralLines(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 1 example, counterexample shrunk in 2 steps:

    $values = [
        0 => 1,
    ];

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(1, 5, 2, false, false, ['values' => "[\n    0 => 1,\n]"])),
        );
    }

    public function testDescribesTheValuesThatTheMinimalCounterexampleDrewAfterItsArguments(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 8 examples, counterexample shrunk in 2 steps:

    $size = 1;

    Draw 1 (list): [5]
    Draw 2: 0

EOT,
            PropertyFalsifiedToStringMapper::map(
                $this->event(8, 30, 2, false, false, ['size' => '1'], null, [['label' => 'list', 'value' => '[5]'], ['label' => null, 'value' => '0']]),
            ),
        );
    }

    public function testDescribesTheValuesThatTheMinimalCounterexampleOfAPropertyWithoutParametersDrew(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 6 examples, counterexample shrunk in 1 step:

    Draw 1: [
        0 => 1,
    ]

EOT,
            PropertyFalsifiedToStringMapper::map(
                $this->event(6, 10, 1, false, false, [], null, [['label' => null, 'value' => "[\n    0 => 1,\n]"]]),
            ),
        );
    }

    public function testDescribesTheStepsOfTheStateMachineThatTheMinimalCounterexampleRan(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 3 examples, counterexample shrunk in 25 steps:

    $accounts1 = $machine->open();
    $machine->deposit(account: $accounts1, amount: 1); // [
    //     0 => 1,
    // ]
    $machine->balanceIsNotNegative(); // invariant failed

EOT,
            PropertyFalsifiedToStringMapper::map(
                $this->event(3, 40, 25, false, false, [], null, [], [], 0, 0, ['$accounts1 = $machine->open();', "\$machine->deposit(account: \$accounts1, amount: 1); // [\n//     0 => 1,\n// ]", '$machine->balanceIsNotNegative(); // invariant failed']),
            ),
        );
    }

    public function testDescribesTheStepsOfTheStateMachineThatTheMinimalCounterexampleRanAfterItsArgumentsAndTheValuesThatItDrew(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 3 examples, counterexample shrunk in 25 steps:

    $size = 1;

    Draw 1: 0

    $machine->push(value: 0);

Property also falsified by RuntimeException at /path/to/FooTest.php:12 with this counterexample:

    $machine->pop();

EOT,
            PropertyFalsifiedToStringMapper::map(
                $this->event(3, 40, 25, false, false, ['size' => '1'], null, [['label' => null, 'value' => '0']], [['description' => 'RuntimeException at /path/to/FooTest.php:12', 'choiceSequence' => [1, 1, 0], 'counterexample' => [], 'drawnValues' => [], 'steps' => ['$machine->pop();']]], 0, 0, ['$machine->push(value: 0);']),
            ),
        );
    }

    public function testDescribesTheOtherWaysInWhichThePropertyWasFalsifiedAfterHowShrinkingWasStopped(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 2 examples, counterexample shrunk in 1 step:

    $size = 5;

Shrinking was stopped after 3 attempts, a simpler counterexample may exist.

Property also falsified by RuntimeException at /path/to/FooTest.php:12 with this counterexample:

    $size = 3;

    Draw 1: 0

EOT,
            PropertyFalsifiedToStringMapper::map(
                $this->event(2, 3, 1, true, false, ['size' => '5'], null, [], [['description' => 'RuntimeException at /path/to/FooTest.php:12', 'choiceSequence' => [3, 0], 'counterexample' => ['size' => '3'], 'drawnValues' => [['label' => null, 'value' => '0']], 'steps' => []]]),
            ),
        );
    }

    public function testStatesHowOftenTheExampleThatFalsifiedThePropertyFalsifiedItAgainWhenThatWasNotEveryTime(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 2 examples, counterexample shrunk in 5 steps:

    $value = 100;

The example that falsified the property falsified it again in only 2 of 4 replays, so its outcome does not depend on its arguments alone.

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(2, 9, 5, false, false, ['value' => '100'], null, [], [], 4, 2)),
        );
    }

    public function testStatesThatTheExampleThatFalsifiedThePropertyDidNotFalsifyItAgain(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 3 examples with this counterexample:

    $value = -7;

The example that falsified the property did not falsify it again in 1 replay.

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(3, 0, 0, false, false, ['value' => '-7'], null, [], [], 1, 0)),
        );
    }

    public function testDoesNotMentionTheReplaysOfTheExampleThatFalsifiedThePropertyWhenItFalsifiedItAgainEveryTime(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 3 examples, counterexample shrunk in 2 steps:

    $value = 7;

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(3, 4, 2, false, false, ['value' => '7'], null, [], [], 4, 4)),
        );
    }

    public function testStatesThatShrinkingWasStoppedWhenTheBudgetWasExhausted(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 2 examples, counterexample shrunk in 23 steps:

    $value = 5;

Shrinking was stopped after 1000 attempts, a simpler counterexample may exist.

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(2, 1000, 23, true, false, ['value' => '5'])),
        );
    }

    public function testStatesThatShrinkingWasStoppedWhenTheTestRunWasAborted(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 2 examples, counterexample shrunk in 3 steps:

    $value = 5;

Shrinking was stopped after 12 attempts because the test run was aborted, a simpler counterexample may exist.

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(2, 12, 3, false, true, ['value' => '5'])),
        );
    }

    public function testDescribesHowAPropertyWithoutParametersWasFalsified(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            "Property falsified after 1 example.\n",
            PropertyFalsifiedToStringMapper::map($this->event(1, 0, 0, false, false, [])),
        );
    }

    public function testDescribesHowAPropertyWasFalsifiedByANamedExplicitExample(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified by explicit example "one cent":

    $money = new Money(amount: 1, currency: Currency::EUR);
    $parts = 2;

EOT,
            PropertyFalsifiedToStringMapper::map(
                $this->event(0, 0, 0, false, false, ['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2'], 'one cent'),
            ),
        );
    }

    public function testDescribesHowAPropertyWasFalsifiedByAnExplicitExampleWithoutName(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified by explicit example #0:

    $value = 5;

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(0, 0, 0, false, false, ['value' => '5'], 0)),
        );
    }

    public function testDescribesHowAPropertyWithoutParametersWasFalsifiedByAnExplicitExample(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            "Property falsified by explicit example #0.\n",
            PropertyFalsifiedToStringMapper::map($this->event(0, 0, 0, false, false, [], 0)),
        );
    }

    /**
     * @param non-negative-int                                                                                                                                                                                       $numberOfExamplesChecked
     * @param non-negative-int                                                                                                                                                                                       $numberOfShrinkingAttempts
     * @param non-negative-int                                                                                                                                                                                       $numberOfShrinkingSteps
     * @param array<non-empty-string, string>                                                                                                                                                                        $counterexample
     * @param null|int|non-empty-string                                                                                                                                                                              $explicitExample
     * @param list<array{label: ?non-empty-string, value: string}>                                                                                                                                                   $drawnValues
     * @param list<array{description: non-empty-string, choiceSequence: list<non-negative-int>, counterexample: array<non-empty-string, string>, drawnValues: list<array{label: ?non-empty-string, value: string}>}> $otherFalsifications
     * @param non-negative-int                                                                                                                                                                                       $numberOfReplays
     * @param non-negative-int                                                                                                                                                                                       $numberOfReproductions
     */
    private function event(int $numberOfExamplesChecked, int $numberOfShrinkingAttempts, int $numberOfShrinkingSteps, bool $shrinkingBudgetExhausted, bool $shrinkingInterrupted, array $counterexample, null|int|string $explicitExample = null, array $drawnValues = [], array $otherFalsifications = [], int $numberOfReplays = 0, int $numberOfReproductions = 0, array $steps = []): PropertyFalsified
    {
        return new PropertyFalsified(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            $numberOfExamplesChecked,
            $numberOfShrinkingAttempts,
            $numberOfShrinkingSteps,
            $shrinkingBudgetExhausted,
            $shrinkingInterrupted,
            [],
            $counterexample,
            $explicitExample,
            $drawnValues,
            $steps,
            $otherFalsifications,
            $numberOfReplays,
            $numberOfReproductions,
        );
    }
}
