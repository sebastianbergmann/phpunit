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

use const PHP_INT_MAX;
use const T_COMMENT;
use const T_OPEN_TAG;
use const T_WHITESPACE;
use function array_count_values;
use function array_filter;
use function array_sum;
use function array_values;
use function count;
use function implode;
use function in_array;
use function is_array;
use function max;
use function token_get_all;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\CounterexampleExporter;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\InvalidStateMachineException;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\StateMachine\ChildStateMachine;
use PHPUnit\TestFixture\PropertyBased\StateMachine\CounterMachine;
use PHPUnit\TestFixture\PropertyBased\StateMachine\LockedMachine;
use PHPUnit\TestFixture\PropertyBased\StateMachine\ObservedMachine;
use PHPUnit\TestFixture\PropertyBased\StateMachine\PreconditionThatDoesNotReturnABool;
use PHPUnit\TestFixture\PropertyBased\StateMachine\StateMachineWithPoolsWhoseNamesEndInDigits;
use PHPUnit\TestFixture\PropertyBased\StateMachine\StateMachineWithRuleThatReturnsAGivenValue;
use PHPUnit\TestFixture\PropertyBased\StateMachine\StuckMachine;
use PHPUnit\TestFixture\PropertyBased\StateMachine\SwitchMachine;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use ReflectionMethod;
use RuntimeException;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;
use SebastianBergmann\ChoiceSequence\SpanKind;
use stdClass;

#[CoversClass(StateMachineRunner::class)]
#[Small]
final class StateMachineRunnerTest extends TestCase
{
    /**
     * @return non-empty-array<non-empty-string, array{non-empty-string, mixed}>
     */
    public static function valueWhoseExportContainsAClosingTagProvider(): array
    {
        return [
            'string' => [
                '$machine->value(); // \'?\> <?php echo "pwned";\'',
                '?> <?php echo "pwned";',
            ],
            'string that ends with a closing tag' => [
                '$machine->value(); // \'??\>\'',
                '??>',
            ],
            'string with line breaks' => [
                '$machine->value(); // "?\>\r\n?\>\r?\>"',
                "?>\r\n?>\r?>",
            ],
            'string with a backslash before the greater-than sign' => [
                '$machine->value(); // \'?\\\\>\'',
                '?\\>',
            ],
            'array' => [
                '$machine->value(); // [\'?\>\' => [\'?\>\', \'*/\']]',
                ['?>' => ['?>', '*/']],
            ],
        ];
    }

    public function testTakesTheStepsThatAreDrawn(): void
    {
        $machine = new SwitchMachine;

        // switchOn(), switchOn() (skipped, the switch is on), token(),
        // useToken() with the latest token, and stop
        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 0, 1, 0, 1, 2, 0, 1, 3, 0, 0])), $machine);

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

        $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 2, 0, 1, 2, 0, 1, 2, 0, 1, 3, 0, 1, 3, 2, 0])), $machine)->run(50);

        $this->assertSame(['token()', 'token()', 'token()', 'useToken(token3)', 'useToken(token1)'], $this->stepsOf($machine));
    }

    public function testValuesThatAreConsumedAreRemovedFromTheirPool(): void
    {
        $machine = new CounterMachine;

        // start(0), snapshot(), restore(), restore() (skipped, the pool is
        // empty), and stop
        $source = ChoiceSource::replay(new ChoiceSequence([0, 1, 0, 0, 1, 4, 0, 1, 4, 0]));

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
        $source  = ChoiceSource::replay(new ChoiceSequence([1, 2, 0, 1, 2, 0, 1, 2, 0]));
        $runner  = $this->runner($source, $machine);

        $runner->run(2);

        $this->assertSame(2, $runner->numberOfSteps());
        $this->assertSame([1, 2, 0, 1, 2, 0], $source->sequence()->choices());
    }

    public function testTheChoicesForTheArgumentsOfARuleThatIsSkippedAreDrawnSoThatTheStepsAfterItAreTakenAsTheyWereDrawn(): void
    {
        $machine = new LockedMachine;

        // add(5) (skipped, the machine is locked), take() (skipped, the pool
        // is empty), item(), inspect() of the item (skipped, the machine is
        // locked), unlock(), add(7), and stop
        $source = ChoiceSource::replay(new ChoiceSequence([1, 0, 5, 1, 1, 0, 1, 2, 0, 1, 3, 0, 1, 4, 1, 0, 7]));
        $runner = $this->runner($source, $machine);

        $runner->run(50);

        $this->assertSame(['item()', 'unlock()', 'add(7)'], $machine->calls);
        $this->assertSame(3, $runner->numberOfSteps());
        $this->assertSame([1, 0, 5, 1, 1, 0, 1, 2, 0, 1, 3, 0, 1, 4, 1, 0, 7, 0], $source->sequence()->choices());
    }

    public function testTheChoiceForTheValueThatARuleThatIsSkippedProducesIsDrawn(): void
    {
        $machine = new LockedMachine;

        // itemOfUnlockedMachine() (skipped, the machine is locked), unlock(),
        // and stop
        $source = ChoiceSource::replay(new ChoiceSequence([1, 5, 0, 1, 4]));
        $runner = $this->runner($source, $machine);

        $runner->run(50);

        $this->assertSame(['unlock()'], $machine->calls);
        $this->assertSame([1, 5, 0, 1, 4, 0], $source->sequence()->choices());
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
        // token(), useToken() with the token, and stop
        $source = ChoiceSource::replay(new ChoiceSequence([1, 2, 0, 1, 3, 0, 0]));

        $this->runner($source, new SwitchMachine)->run(50);

        $spans = [];

        foreach ($source->sequence()->spans() as $span) {
            $spans[] = [$span->start(), $span->end(), $span->label(), $span->kind()];
        }

        $stateMachine = AbstractGenerator::labelOf('stateMachine', SwitchMachine::class);
        $step         = AbstractGenerator::labelOf('step', SwitchMachine::class);
        $pool         = AbstractGenerator::labelOf('pool', SwitchMachine::class, 'tokens');

        // the step that produces a value for a pool ends with a choice for
        // the value, and the position of the value that a step takes from a
        // pool is a reference to it
        $this->assertSame(
            [
                [0, 7, $stateMachine, null],
                [0, 3, $step, null],
                [2, 3, $pool, SpanKind::Referable],
                [3, 6, $step, null],
                [5, 6, $pool, SpanKind::Reference],
                [6, 7, $step, null],
            ],
            $spans,
        );
    }

    public function testThePositionOfAValueThatIsConsumedIsMarkedAsAReferenceThatConsumesIt(): void
    {
        // start(0), snapshot(), restore(), and stop
        $source = ChoiceSource::replay(new ChoiceSequence([0, 1, 0, 0, 1, 3, 0, 0]));

        $this->runner($source, new CounterMachine)->run(50);

        $kinds = [];

        foreach ($source->sequence()->spans() as $span) {
            if ($span->kind() !== null) {
                $kinds[] = [$span->start(), $span->end(), $span->kind()];
            }
        }

        $this->assertSame(
            [
                [3, 4, SpanKind::Referable],
                [6, 7, SpanKind::ConsumingReference],
            ],
            $kinds,
        );
    }

    public function testStepsAreReportedWithAnExporter(): void
    {
        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 0, 1, 2, 0, 1, 3, 0, 1, 4, 0])), new SwitchMachine, true);

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

    #[DataProvider('valueWhoseExportContainsAClosingTagProvider')]
    #[RequiresPhpExtension('tokenizer')]
    public function testClosingTagInTheValueThatAStepReturnedDoesNotEndTheCommentThatTheValueIsWrittenAs(string $expected, mixed $value): void
    {
        $machine        = new StateMachineWithRuleThatReturnsAGivenValue;
        $machine->value = $value;

        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 0, 0])), $machine, true);

        $runner->run(50);

        $this->assertSame([$expected], $runner->steps());
        $this->assertSame('$machine->value();', $this->codeWithoutComments($runner->steps()));
    }

    #[RequiresPhpExtension('tokenizer')]
    public function testClosingTagInALineOfTheValueThatAStepReturnedDoesNotEndTheCommentThatTheLineIsWrittenAs(): void
    {
        $machine                     = new StateMachineWithRuleThatReturnsAGivenValue;
        $machine->value              = new stdClass;
        $machine->value->closingTags = "?>\n?> <?php echo 'pwned';";

        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 0, 0])), $machine, true);

        $runner->run(50);

        $steps = $runner->steps();

        $this->assertCount(1, $steps);
        $this->assertStringMatchesFormat(
            <<<'EOT'
$machine->value();
// stdClass Object #%d (
//     'closingTags' => '?\>\n
// ?\> <?php echo 'pwned';',
// )
EOT,
            $steps[0],
        );
        $this->assertSame('$machine->value();', $this->codeWithoutComments($steps));
    }

    public function testGeneratedArgumentsAreReported(): void
    {
        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([0, 1, 1, 0, 1, 0, 0, 1, 3, 0, 0])), new CounterMachine, true);

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

    public function testValuesOfPoolsWhoseNamesOnlyDifferInDigitsAtTheirEndAreAssignedToDifferentVariables(): void
    {
        // produceX1(), produceX_1(), eleven times produceX(), and consume()
        // with the values that were produced last
        $choices = [1, 1, 0, 1, 2, 0];

        for ($i = 1; $i <= 11; $i++) {
            $choices[] = 1;
            $choices[] = 0;
            $choices[] = 0;
        }

        $runner = $this->runner(
            ChoiceSource::replay(new ChoiceSequence([...$choices, 1, 3, 0, 0, 0, 0])),
            new StateMachineWithPoolsWhoseNamesEndInDigits,
            true,
        );

        $runner->run(50);

        $steps = $runner->steps();

        $this->assertCount(14, $steps);
        $this->assertSame('$x11 = $machine->produceX1();', $steps[0]);
        $this->assertSame('$x_11 = $machine->produceX_1();', $steps[1]);
        $this->assertSame('$x1 = $machine->produceX();', $steps[2]);
        $this->assertSame('$x10 = $machine->produceX();', $steps[11]);
        $this->assertSame('$x__11 = $machine->produceX();', $steps[12]);
        $this->assertSame('$machine->consume(x: $x__11, x1: $x11, x_1: $x_11);', $steps[13]);
    }

    public function testStepThatFailsIsReported(): void
    {
        $machine                = new SwitchMachine;
        $machine->useTokenFails = true;

        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 2, 0, 1, 3, 0])), $machine, true);

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

        $runner = $this->runner(ChoiceSource::replay(new ChoiceSequence([1, 2, 0, 1, 0])), $machine, true);

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

    public function testStepsThatWereTakenWithoutAnExporterCanBeExportedAfterwards(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([0, 1, 1, 0, 1, 0, 0, 1, 3, 0, 0]));
        $runner = $this->runner($source, new CounterMachine);

        $runner->run(50);

        $this->assertSame([], $runner->steps());
        $this->assertSame(
            [
                '$machine->start(value: 0);',
                '$machine->increment(amount: 1);',
                '$snapshots1 = $machine->snapshot();',
                '$machine->restore(snapshot: $snapshots1);',
            ],
            $runner->stepsExportedWith($this->exporter($source)),
        );
    }

    public function testStepsThatWereTakenWithAnExporterAreNotExportedAgain(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([0, 1, 1, 0, 1, 0, 0, 1, 3, 0, 0]));
        $runner = $this->runner($source, new CounterMachine, true);

        $runner->run(50);

        $this->assertSame($runner->steps(), $runner->stepsExportedWith($this->exporter($source)));
    }

    public function testStepThatIsBeingTakenWithoutAnExporterIsExportedAsTheLastStep(): void
    {
        $machine = new ObservedMachine;
        $source  = ChoiceSource::replay(new ChoiceSequence([1, 0, 1, 1, 0, 2, 0]));
        $runner  = $this->runner($source, $machine);
        $steps   = [];

        $machine->whileStepIsTaken = function () use ($runner, $source, &$steps): void
        {
            $steps[] = $runner->stepsExportedWith($this->exporter($source));
        };

        $runner->run(50);

        $this->assertSame(
            [
                ['$machine->step(amount: 2);'],
                ['$machine->step(amount: 2);', '$machine->step(amount: 3);'],
            ],
            $steps,
        );
    }

    public function testStepThatIsBeingTakenWithAnExporterIsExportedAsTheLastStep(): void
    {
        $machine = new ObservedMachine;
        $source  = ChoiceSource::replay(new ChoiceSequence([1, 0, 1, 1, 0, 2, 0]));
        $runner  = $this->runner($source, $machine, true);
        $steps   = [];

        $machine->whileStepIsTaken = function () use ($runner, $source, &$steps): void
        {
            $steps[] = $runner->stepsExportedWith($this->exporter($source));
        };

        $runner->run(50);

        $this->assertSame(
            [
                ['$machine->step(amount: 2);'],
                ['$machine->step(amount: 2);', '$machine->step(amount: 3);'],
            ],
            $steps,
        );
        $this->assertSame($runner->steps(), $runner->stepsExportedWith($this->exporter($source)));
    }

    public function testInvariantThatIsBeingCheckedIsExportedAsTheLastStep(): void
    {
        $machine = new ObservedMachine;
        $source  = ChoiceSource::replay(new ChoiceSequence([1, 0, 1, 0]));
        $runner  = $this->runner($source, $machine);
        $steps   = [];

        $machine->whileInvariantIsChecked = function () use ($runner, $source, &$steps): void
        {
            $steps[] = $runner->stepsExportedWith($this->exporter($source));
        };

        $runner->run(50);

        $this->assertSame(
            [
                ['$machine->invariant();'],
                ['$machine->step(amount: 2);', '$machine->invariant();'],
            ],
            $steps,
        );
        $this->assertSame(['$machine->step(amount: 2);'], $runner->stepsExportedWith($this->exporter($source)));
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

    public function testStepsAreDrawnAtRandomWhenTheMaximumNumberOfStepsIsTheLargestInteger(): void
    {
        $source = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(1)));
        $runner = $this->runner($source, new ChildStateMachine);

        $runner->run(PHP_INT_MAX);

        // each step of this state machine draws two choices
        $this->assertGreaterThan(0, $runner->numberOfSteps());
        $this->assertLessThanOrEqual(ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES / 2, $runner->numberOfSteps());
    }

    private function runner(ChoiceSource $source, object $machine, bool $reported = false): StateMachineRunner
    {
        $exporter = null;

        if ($reported) {
            $exporter = $this->exporter($source);
        }

        return new StateMachineRunner(
            $source,
            $exporter,
            new StateMachineDefinitionBuilder()->build($machine::class, new GeneratorDeriver(GeneratorRegistry::empty())),
            $machine,
        );
    }

    private function exporter(ChoiceSource $source): CounterexampleExporter
    {
        return new CounterexampleExporter(
            NameScope::forMethod(new ReflectionMethod($this, __FUNCTION__)),
            $source->constructions(),
        );
    }

    /**
     * The steps as PHP parses them, without their comments and without
     * whitespace: a comment that ends the PHP code leaves a closing tag and
     * what follows it, and a comment that ends before its line does leaves
     * the rest of the line.
     *
     * @param list<non-empty-string> $steps
     */
    private function codeWithoutComments(array $steps): string
    {
        $code = '';

        foreach (token_get_all('<?php ' . implode("\n", $steps)) as $token) {
            if (!is_array($token)) {
                $code .= $token;

                continue;
            }

            if (!in_array($token[0], [T_OPEN_TAG, T_COMMENT, T_WHITESPACE], true)) {
                $code .= $token[1];
            }
        }

        return $code;
    }

    /**
     * @return list<non-empty-string>
     */
    private function stepsOf(SwitchMachine $machine): array
    {
        return array_values(array_filter($machine->calls, static fn (string $call): bool => $call !== 'invariant()'));
    }
}
