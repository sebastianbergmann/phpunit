<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\StateMachine;

use function array_count_values;
use function array_filter;
use function array_sum;
use function array_values;
use function count;
use function max;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\CounterexampleExporter;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\InvalidStateMachineException;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\StateMachine\CounterMachine;
use PHPUnit\TestFixture\PropertyBased\StateMachine\PreconditionThatDoesNotReturnABool;
use PHPUnit\TestFixture\PropertyBased\StateMachine\StuckMachine;
use PHPUnit\TestFixture\PropertyBased\StateMachine\SwitchMachine;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use ReflectionMethod;
use RuntimeException;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;

#[CoversClass(StateMachineRunner::class)]
#[Small]
final class StateMachineRunnerTest extends TestCase
{
    public function testTakesTheStepsThatAreDrawn(): void
    {
        $machine = new SwitchMachine;

        // switchOn(), switchOn() (skipped, the switch is on), token(),
        // useToken() with the latest token, and stop
        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 0, 1, 0, 1, 2, 1, 3, 0, 0])), $machine);

        $runner->run(50);

        $this->assertSame(
            [
                'invariant()',
                'switchOn()',
                'invariant()',
                'token()',
                'invariant()',
                'useToken(token1)',
                'invariant()',
            ],
            $machine->calls,
        );

        $this->assertSame(3, $runner->numberOfSteps());
        $this->assertSame(
            ['switchOn' => 1, 'switchOff' => 0, 'token' => 1, 'useToken' => 1, 'describe' => 0],
            $runner->numberOfStepsByRule(),
        );
        $this->assertSame([], $runner->steps());
    }

    public function testValuesFromPoolsAreCountedFromTheValueThatWasProducedLast(): void
    {
        $machine = new SwitchMachine;

        $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 2, 1, 2, 1, 2, 1, 3, 0, 1, 3, 2, 0])), $machine)->run(50);

        $this->assertSame(['token()', 'token()', 'token()', 'useToken(token3)', 'useToken(token1)'], $this->stepsOf($machine));
    }

    public function testValuesThatAreConsumedAreRemovedFromTheirPool(): void
    {
        $machine = new CounterMachine;

        // start(0), snapshot(), restore(), restore() (skipped, the pool is
        // empty), and stop
        $source = ChoiceSource::replay(new ChoiceSequence([0, 1, 0, 1, 4, 0, 1, 4, 0]));

        $this->runner($source, $machine)->run(50);

        $this->assertSame(
            [
                'start(0)',
                'isNotNegative()',
                'checkedAtEnd()',
                'snapshot()',
                'isNotNegative()',
                'restore(0)',
                'isNotNegative()',
                'checkedAtEnd()',
            ],
            $machine->calls,
        );
    }

    public function testNoMoreStepsAreTakenThanTheMaximumNumberOfSteps(): void
    {
        $machine = new SwitchMachine;
        $source  = ChoiceSource::replay(new ChoiceSequence([1, 2, 1, 2, 1, 2]));
        $runner  = $this->runner($source, $machine);

        $runner->run(2);

        $this->assertSame(2, $runner->numberOfSteps());
        $this->assertSame([1, 2, 1, 2], $source->sequence()->choices());
    }

    public function testNoMoreStepsAreTakenWhenNoRuleCanBeApplied(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([1, 0]));
        $runner = $this->runner($source, new StuckMachine);

        $runner->run(50);

        $this->assertSame(0, $runner->numberOfSteps());
        $this->assertSame([], $source->sequence()->choices());
    }

    public function testInvariantsAtTheEndAreNotCheckedTwiceWhenNoStepIsTaken(): void
    {
        $machine = new CounterMachine;

        $this->runner(ChoiceSource::replay(new ChoiceSequence([])), $machine)->run(50);

        $this->assertSame(['start(0)', 'isNotNegative()', 'checkedAtEnd()'], $machine->calls);
    }

    public function testStepsAndTheirInitializationsAreMarkedAsSpans(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([1, 2, 1, 3, 0, 0]));

        $this->runner($source, new SwitchMachine)->run(50);

        $spans = [];

        foreach ($source->sequence()->spans() as $span) {
            $spans[] = [$span->start(), $span->end(), $span->label()];
        }

        $stateMachine = AbstractGenerator::labelOf('stateMachine', SwitchMachine::class);
        $step         = AbstractGenerator::labelOf('step', SwitchMachine::class);

        $this->assertSame(
            [
                [0, 6, $stateMachine],
                [0, 2, $step],
                [2, 5, $step],
                [5, 6, $step],
            ],
            $spans,
        );
    }

    public function testStepsAreReportedWithAnExporter(): void
    {
        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 0, 1, 2, 1, 3, 0, 1, 4, 0])), new SwitchMachine, true);

        $runner->run(50);

        $steps = $runner->steps();

        $this->assertCount(4, $steps);
        $this->assertSame('$machine->switchOn();', $steps[0]);
        $this->assertSame('$tokens1 = $machine->token();', $steps[1]);
        $this->assertSame('$machine->useToken(token: $tokens1); // \'TOKEN1\'', $steps[2]);
        $this->assertStringMatchesFormat(
            <<<'EOT'
$machine->describe();
// stdClass Object #%d (
//     'on' => true,
// )
EOT,
            $steps[3],
        );
    }

    public function testGeneratedArgumentsAreReported(): void
    {
        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([0, 1, 1, 0, 1, 0, 1, 3, 0, 0])), new CounterMachine, true);

        $runner->run(50);

        $this->assertSame(
            [
                '$machine->start(value: 0);',
                '$machine->increment(amount: 1); // 1',
                '$snapshots1 = $machine->snapshot();',
                '$machine->restore(snapshot: $snapshots1);',
            ],
            $runner->steps(),
        );
    }

    public function testStepThatFailsIsReported(): void
    {
        $machine                = new SwitchMachine;
        $machine->useTokenFails = true;

        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 2, 1, 3, 0])), $machine, true);

        try {
            $runner->run(50);
        } catch (RuntimeException $e) {
            $this->assertSame('The token cannot be used', $e->getMessage());
            $this->assertSame(
                [
                    '$tokens1 = $machine->token();',
                    '$machine->useToken(token: $tokens1);',
                ],
                $runner->steps(),
            );

            return;
        }

        $this->fail('The step did not fail');
    }

    public function testInvariantThatFailsIsReported(): void
    {
        $machine                       = new SwitchMachine;
        $machine->invariantFailsWhenOn = true;

        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 2, 1, 0])), $machine, true);

        try {
            $runner->run(50);
        } catch (RuntimeException $e) {
            $this->assertSame('The switch is on', $e->getMessage());
            $this->assertSame(
                [
                    '$tokens1 = $machine->token();',
                    '$machine->switchOn();',
                    '$machine->invariant(); // invariant failed',
                ],
                $runner->steps(),
            );

            return;
        }

        $this->fail('The invariant did not fail');
    }

    public function testPreconditionThatDoesNotReturnABoolIsRejected(): void
    {
        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([])), new PreconditionThatDoesNotReturnABool);

        $this->expectException(InvalidStateMachineException::class);
        $this->expectExceptionMessageIs('State machine ' . PreconditionThatDoesNotReturnABool::class . ' cannot be run: the precondition canStep() of rule step() returned int instead of a bool');

        $runner->run(50);
    }

    public function testStepsThatAreDrawnAtRandomOnlyApplyRulesThatCanBeApplied(): void
    {
        $numberOfSteps       = [];
        $numberOfStepsByRule = [];

        for ($seed = 1; $seed <= 50; $seed++) {
            $machine = new CounterMachine;
            $source  = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar($seed)));
            $runner  = $this->runner($source, $machine);

            // the invariant fails when decrement() is applied while the
            // counter is 0
            $runner->run(20);

            $this->assertLessThanOrEqual(20, $runner->numberOfSteps());
            $this->assertSame($runner->numberOfSteps(), array_sum($runner->numberOfStepsByRule()));
            $this->assertCount(0, array_filter($runner->numberOfStepsByRule(), static fn (int $number): bool => $number < 0));

            // the same choices take the same steps
            $replayed = new CounterMachine;

            $this->runner(ChoiceSource::replay($source->sequence()), $replayed)->run(20);

            $this->assertSame($machine->calls, $replayed->calls);

            $numberOfSteps[] = $runner->numberOfSteps();

            foreach ($runner->numberOfStepsByRule() as $rule => $number) {
                if ($number > 0) {
                    $numberOfStepsByRule[$rule] = true;
                }
            }
        }

        $this->assertGreaterThan(10, max($numberOfSteps));
        $this->assertGreaterThan(10, count(array_count_values($numberOfSteps)));
        $this->assertCount(4, $numberOfStepsByRule);
    }

    private function runner(ChoiceSource $source, object $machine, bool $reported = false): StateMachineRunner
    {
        $exporter = null;

        if ($reported) {
            $exporter = new CounterexampleExporter(
                NameScope::forMethod(new ReflectionMethod($this, __FUNCTION__)),
                $source->constructions(),
            );
        }

        return new StateMachineRunner(
            $source,
            $exporter,
            new StateMachineDefinitionBuilder()->build($machine::class, new GeneratorDeriver(GeneratorRegistry::empty())),
            $machine,
        );
    }

    /**
     * @return list<non-empty-string>
     */
    private function stepsOf(SwitchMachine $machine): array
    {
        return array_values(array_filter($machine->calls, static fn (string $call): bool => $call !== 'invariant()'));
    }
}
