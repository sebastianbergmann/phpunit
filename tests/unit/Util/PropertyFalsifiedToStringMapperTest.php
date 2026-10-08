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
                $this->event(2, 40, 17, false, ['money' => 'new Money(amount: 1, currency: Currency::EUR)', 'parts' => '2']),
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
            PropertyFalsifiedToStringMapper::map($this->event(1, 3, 1, false, ['value' => '0'])),
        );
    }

    public function testDoesNotMentionShrinkingWhenTheCounterexampleWasNotShrunk(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Property falsified after 3 examples with this counterexample:

    $value = -7;

EOT,
            PropertyFalsifiedToStringMapper::map($this->event(3, 4, 0, false, ['value' => '-7'])),
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
            PropertyFalsifiedToStringMapper::map($this->event(1, 5, 2, false, ['values' => "[\n    0 => 1,\n]"])),
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
            PropertyFalsifiedToStringMapper::map($this->event(2, 1000, 23, true, ['value' => '5'])),
        );
    }

    public function testDescribesHowAPropertyWithoutParametersWasFalsified(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            "Property falsified after 1 example.\n",
            PropertyFalsifiedToStringMapper::map($this->event(1, 0, 0, false, [])),
        );
    }

    /**
     * @param positive-int                    $numberOfExamplesChecked
     * @param non-negative-int                $numberOfShrinkingAttempts
     * @param non-negative-int                $numberOfShrinkingSteps
     * @param array<non-empty-string, string> $counterexample
     */
    private function event(int $numberOfExamplesChecked, int $numberOfShrinkingAttempts, int $numberOfShrinkingSteps, bool $shrinkingBudgetExhausted, array $counterexample): PropertyFalsified
    {
        return new PropertyFalsified(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            $numberOfExamplesChecked,
            $numberOfShrinkingAttempts,
            $numberOfShrinkingSteps,
            $shrinkingBudgetExhausted,
            [],
            $counterexample,
        );
    }
}
