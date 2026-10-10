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
     * @param non-negative-int                $numberOfExamplesChecked
     * @param non-negative-int                $numberOfShrinkingAttempts
     * @param non-negative-int                $numberOfShrinkingSteps
     * @param array<non-empty-string, string> $counterexample
     * @param null|int|non-empty-string       $explicitExample
     */
    private function event(int $numberOfExamplesChecked, int $numberOfShrinkingAttempts, int $numberOfShrinkingSteps, bool $shrinkingBudgetExhausted, bool $shrinkingInterrupted, array $counterexample, null|int|string $explicitExample = null): PropertyFalsified
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
        );
    }
}
