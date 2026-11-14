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

use function array_intersect;
use function array_key_exists;
use function array_keys;
use function array_values;
use function assert;
use function count;
use function get_debug_type;
use function implode;
use function is_bool;
use function max;
use function min;
use function range;
use function sprintf;
use function str_contains;
use function str_replace;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\CounterexampleExporter;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\InvalidStateMachineException;
use ReflectionMethod;
use SebastianBergmann\ChoiceSequence\ExactDistribution;
use SebastianBergmann\ChoiceSequence\Pool;
use SebastianBergmann\ChoiceSequence\UniformDistribution;
use Throwable;

/**
 * Runs a state machine with the choices of an example of a property, so that
 * its steps are generated, replayed, and shrunk together with the arguments of
 * the property.
 *
 * The initializations are taken first, in the order in which they are
 * declared, those of a parent class before those of the class that extends
 * it. Then each step is drawn like an element of a list: a choice whether
 * another step is taken (0 = stop), the index of its rule in the same order
 * of the rules, and the arguments of the rule, all in one span.
 *
 * A value from a pool is drawn as its position among the values of the pool
 * that were not consumed, counted from the value that was produced last, in
 * a span of the kind Reference, or ConsumingReference when the value is
 * consumed. A step that produces a value for a pool ends with a choice that is
 * always 0, in a span of the kind Referable. Both spans are labelled with the
 * pool, and both are drawn by Pool of sebastian/choice-sequence. They tell
 * the shrinker which value a position refers to: when it deletes a step that
 * produced a value for the pool, or that consumed one, it changes the
 * positions that the steps after it draw, so that they take the values that
 * they took before.
 *
 * While choices are drawn at random, only rules whose preconditions hold and
 * whose pools have values are drawn, and each example enables a random subset
 * of the rules (swarm testing), without drawing choices for this. When a
 * choice sequence is replayed, a rule that cannot be applied is skipped and
 * does not count as a step. The choices for its arguments are drawn
 * nevertheless, so that the choices after them are read as the steps that
 * they were drawn for: when the step that produced the only value of a pool
 * is deleted, the steps that took the value are skipped, and the steps after
 * them are taken as before. No more steps are taken when no rule can be
 * applied.
 *
 * The invariants are checked after the initializations and after every step,
 * except for those with #[StateMachineInvariant(atEnd: true)], which are
 * checked after the initializations and after the last step.
 *
 * With an exporter, the steps are recorded as PHP code that calls the methods
 * of the state machine, for the report of the minimal counterexample: a value
 * that a step produced is assigned to a variable that is named after its pool,
 * the value that another step returned is written as a comment unless it is
 * null, with ?> written as ?\> as it would end the comment, and an invariant
 * is written only when it failed.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class StateMachineRunner
{
    /**
     * The probability with which a rule is enabled for an example whose steps
     * are drawn at random.
     */
    private const float PROBABILITY_OF_ENABLED_RULE = 0.5;

    /**
     * The largest number of steps that is planned for an example whose steps
     * are drawn at random: a step draws at least two choices, whether it is
     * taken and its rule, so no more steps can be taken before the maximum
     * number of choices of an example has been drawn.
     */
    private const int LARGEST_PLANNED_NUMBER_OF_STEPS = ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES / 2;
    private readonly ChoiceSource $source;
    private readonly ?CounterexampleExporter $exporter;
    private readonly StateMachineDefinition $definition;
    private readonly object $machine;

    /**
     * The values that the steps produced and that were not consumed, by pool,
     * in the order in which they were produced, with the names of the
     * variables that they are assigned to in the report.
     *
     * @var array<non-empty-string, Pool<array{value: mixed, variable: non-empty-string}>>
     */
    private array $pools = [];

    /**
     * @var array<non-empty-string, positive-int>
     */
    private array $numberOfValuesProduced = [];

    /**
     * The names of the variables that values were assigned to in the report.
     *
     * @var array<non-empty-string, true>
     */
    private array $variables = [];

    /**
     * @var list<non-empty-string>
     */
    private array $steps = [];

    /**
     * The steps that were taken, and the one that is being taken, without an
     * exporter: they are only written as PHP code when the PHP process ends
     * while the example is run, see stepsExportedWith().
     *
     * @var list<array{methodName: non-empty-string, variable: ?non-empty-string, arguments: list<string>, values: array<non-negative-int, mixed>}>
     */
    private array $stepsThatAreNotExported = [];

    /**
     * The step that is being taken with an exporter, or the invariant that is
     * being checked, written as PHP code.
     *
     * @var ?non-empty-string
     */
    private ?string $stepBeingTaken = null;

    /**
     * The rules of the steps that were taken, in the order in which they were
     * taken.
     *
     * @var list<non-empty-string>
     */
    private array $rulesOfSteps = [];

    /**
     * @param ?CounterexampleExporter $exporter the exporter for the steps that are reported, null when they are not reported
     */
    public function __construct(ChoiceSource $source, ?CounterexampleExporter $exporter, StateMachineDefinition $definition, object $machine)
    {
        $this->source     = $source;
        $this->exporter   = $exporter;
        $this->definition = $definition;
        $this->machine    = $machine;
    }

    /**
     * @param positive-int $maximumNumberOfSteps
     *
     * @throws ExampleRejectedException
     * @throws InvalidStateMachineException when a precondition does not return a bool
     * @throws Throwable                    when a step or an invariant fails
     */
    public function run(int $maximumNumberOfSteps): void
    {
        $this->source->startSpan(AbstractGenerator::labelOf('stateMachine', $this->definition->className()));

        try {
            foreach ($this->definition->initializations() as $initialization) {
                $this->take($initialization);
            }

            $this->checkInvariants([...$this->definition->invariants(), ...$this->definition->invariantsAtEnd()]);

            $this->takeSteps($maximumNumberOfSteps);

            if ($this->rulesOfSteps !== []) {
                $this->checkInvariants($this->definition->invariantsAtEnd());
            }
        } finally {
            $this->source->endSpan();
        }
    }

    /**
     * The steps that were taken, written as PHP code, in the order in which
     * they were taken, with the invariant that failed, if one did. They are
     * only recorded with an exporter.
     *
     * @return list<non-empty-string>
     */
    public function steps(): array
    {
        return $this->steps;
    }

    /**
     * The steps that were taken, as steps() returns them, followed by the step
     * that is being taken or the invariant that is being checked, for the
     * report of the example that was run when the PHP process ended. The
     * steps that were taken without an exporter are written as PHP code with
     * the given one: their arguments are exported in the state in which the
     * steps left them, and the values that they returned are not written.
     *
     * @return list<non-empty-string>
     */
    public function stepsExportedWith(CounterexampleExporter $exporter): array
    {
        $steps = $this->steps;

        foreach ($this->stepsThatAreNotExported as $step) {
            $arguments = [];

            foreach ($step['arguments'] as $position => $argument) {
                if (array_key_exists($position, $step['values'])) {
                    $argument .= $exporter->export($step['values'][$position]);
                }

                $arguments[] = $argument;
            }

            $call = sprintf('$machine->%s(%s);', $step['methodName'], implode(', ', $arguments));

            if ($step['variable'] !== null) {
                $call = '$' . $step['variable'] . ' = ' . $call;
            }

            $steps[] = $call;
        }

        if ($this->stepBeingTaken !== null) {
            $steps[] = $this->stepBeingTaken;
        }

        return $steps;
    }

    /**
     * The number of steps that were taken, not counting the initializations
     * and the rules that were skipped.
     *
     * @return non-negative-int
     */
    public function numberOfSteps(): int
    {
        return count($this->rulesOfSteps);
    }

    /**
     * The number of steps that were taken with each rule, in the order in
     * which the rules are declared.
     *
     * @return array<non-empty-string, non-negative-int>
     */
    public function numberOfStepsByRule(): array
    {
        $numberOfStepsByRule = [];

        foreach ($this->definition->rules() as $rule) {
            $numberOfStepsByRule[$rule->methodName()] = count(array_keys($this->rulesOfSteps, $rule->methodName(), true));
        }

        return $numberOfStepsByRule;
    }

    /**
     * @param positive-int $maximumNumberOfSteps
     *
     * @throws ExampleRejectedException
     * @throws InvalidStateMachineException
     * @throws Throwable
     */
    private function takeSteps(int $maximumNumberOfSteps): void
    {
        $rules = $this->definition->rules();
        $label = AbstractGenerator::labelOf('step', $this->definition->className());

        // while choices are drawn at random, the number of steps is chosen
        // uniformly, so that short and long sequences of steps are equally
        // likely; it is chosen from the numbers of steps for which choices
        // can be drawn, as a step draws at least two of them, so that the
        // numbers to choose from are not more than can be held in memory
        $plannedNumberOfSteps = $this->source->suggest(
            range(1, min($maximumNumberOfSteps, self::LARGEST_PLANNED_NUMBER_OF_STEPS)),
            1.0,
        );
        $enabledRules = $this->enabledRules();

        while (count($this->rulesOfSteps) < $maximumNumberOfSteps) {
            $applicableRules = $this->applicableRules();

            if ($applicableRules === []) {
                return;
            }

            $anotherStep = 0;

            if ($plannedNumberOfSteps !== null && count($this->rulesOfSteps) < $plannedNumberOfSteps) {
                $anotherStep = 1;
            }

            $this->source->startSpan($label);

            try {
                if ($this->source->draw(1, new ExactDistribution($anotherStep)) === 0) {
                    return;
                }

                $index = $this->source->draw(
                    count($rules) - 1,
                    new ExactDistribution($this->suggestedRule($applicableRules, $enabledRules)),
                );

                if (!array_key_exists($index, $applicableRules)) {
                    assert(array_key_exists($index, $rules));

                    $this->skip($rules[$index]);

                    continue;
                }

                $this->take($applicableRules[$index]);
            } finally {
                $this->source->endSpan();
            }

            $this->rulesOfSteps[] = $applicableRules[$index]->methodName();

            $this->checkInvariants($this->definition->invariants());
        }
    }

    /**
     * The indexes of the rules that are enabled for an example whose steps
     * are drawn at random, and none when a choice sequence is replayed.
     *
     * @return list<non-negative-int>
     */
    private function enabledRules(): array
    {
        $enabledRules = [];

        foreach (array_keys($this->definition->rules()) as $index) {
            if ($this->source->suggest([true], self::PROBABILITY_OF_ENABLED_RULE) !== null) {
                $enabledRules[] = $index;
            }
        }

        return $enabledRules;
    }

    /**
     * The rules whose pools have values for their parameters and whose
     * preconditions hold, by their indexes.
     *
     * @throws InvalidStateMachineException
     *
     * @return array<non-negative-int, StepDefinition>
     */
    private function applicableRules(): array
    {
        $applicableRules = [];

        foreach ($this->definition->rules() as $index => $rule) {
            if ($this->poolsHaveValuesFor($rule) && $this->preconditionHolds($rule)) {
                $applicableRules[$index] = $rule;
            }
        }

        return $applicableRules;
    }

    /**
     * The index of an applicable rule that is enabled, or of any applicable
     * rule when none of them is enabled, which is drawn while choices are
     * drawn at random.
     *
     * @param non-empty-array<non-negative-int, StepDefinition> $applicableRules
     * @param list<non-negative-int>                            $enabledRules
     *
     * @return non-negative-int
     */
    private function suggestedRule(array $applicableRules, array $enabledRules): int
    {
        $candidates = array_values(array_intersect(array_keys($applicableRules), $enabledRules));

        if ($candidates === []) {
            $candidates = array_keys($applicableRules);
        }

        $suggestedRule = $this->source->suggest($candidates, 1.0);

        // the distribution is not used when a choice sequence is replayed
        if ($suggestedRule === null) {
            return 0;
        }

        return $suggestedRule;
    }

    /**
     * A parameter that consumes a value removes it from its pool before the
     * next parameter receives a value.
     */
    private function poolsHaveValuesFor(StepDefinition $rule): bool
    {
        $numberOfValues = [];

        foreach ($rule->parameters() as $parameter) {
            $pool = $parameter->pool();

            if ($pool === null) {
                continue;
            }

            if (!array_key_exists($pool, $numberOfValues)) {
                $numberOfValues[$pool] = 0;

                if (array_key_exists($pool, $this->pools)) {
                    $numberOfValues[$pool] = $this->pools[$pool]->count();
                }
            }

            if ($numberOfValues[$pool] === 0) {
                return false;
            }

            if ($parameter->consume()) {
                $numberOfValues[$pool]--;
            }
        }

        return true;
    }

    /**
     * @throws InvalidStateMachineException
     */
    private function preconditionHolds(StepDefinition $rule): bool
    {
        $precondition = $rule->precondition();

        if ($precondition === null) {
            return true;
        }

        $holds = $this->call($precondition, []);

        if (!is_bool($holds)) {
            throw InvalidStateMachineException::because(
                $this->definition->className(),
                sprintf(
                    'the precondition %s() of rule %s() returned %s instead of a bool',
                    $precondition,
                    $rule->methodName(),
                    get_debug_type($holds),
                ),
            );
        }

        return $holds;
    }

    /**
     * @throws ExampleRejectedException
     * @throws Throwable
     */
    private function take(StepDefinition $step): void
    {
        $arguments = [];
        $exported  = [];
        $values    = [];

        foreach ($step->parameters() as $parameter) {
            $pool = $parameter->pool();

            if ($pool === null) {
                $generator = $parameter->generator();

                assert($generator !== null);

                $value       = $generator->generate($this->source);
                $arguments[] = $value;

                // the value is exported before the step can change its state
                if ($this->exporter !== null) {
                    $exported[] = $parameter->name() . ': ' . $this->exporter->export($value);
                } else {
                    $values[count($exported)] = $value;
                    $exported[]               = $parameter->name() . ': ';
                }

                continue;
            }

            // the rule can only be applied when its pools have values, of
            // which the one that was produced last is the simplest one
            if ($parameter->consume()) {
                $produced = $this->source->consume($this->pool($pool));
            } else {
                $produced = $this->source->refer($this->pool($pool));
            }

            $arguments[] = $produced['value'];
            $exported[]  = $parameter->name() . ': $' . $produced['variable'];
        }

        $call     = sprintf('$machine->%s(%s);', $step->methodName(), implode(', ', $exported));
        $produces = $step->produces();
        $variable = null;

        if ($produces !== null) {
            $variable = $this->nextVariableFor($produces);
            $call     = '$' . $variable . ' = ' . $call;
        }

        if ($this->exporter !== null) {
            $this->stepBeingTaken = $call;
        } else {
            $this->stepsThatAreNotExported[] = ['methodName' => $step->methodName(), 'variable' => $variable, 'arguments' => $exported, 'values' => $values];
        }

        try {
            $result = $this->call($step->methodName(), $arguments);
        } catch (Throwable $t) {
            $this->record($call);

            throw $t;
        }

        if ($produces !== null) {
            assert($variable !== null);

            $this->source->add($this->pool($produces), ['value' => $result, 'variable' => $variable]);
        } elseif ($result !== null && $this->exporter !== null) {
            $call = $this->withComment($call, $this->exporter->export($result));
        }

        $this->record($call);
    }

    /**
     * @param non-empty-string $pool
     *
     * @return Pool<array{value: mixed, variable: non-empty-string}>
     */
    private function pool(string $pool): Pool
    {
        if (!array_key_exists($pool, $this->pools)) {
            /** @var Pool<array{value: mixed, variable: non-empty-string}> $values */
            $values = new Pool(AbstractGenerator::labelOf('pool', $this->definition->className(), $pool));

            $this->pools[$pool] = $values;
        }

        return $this->pools[$pool];
    }

    /**
     * Draws the choices that the rule draws for its arguments, and for the
     * value that it produces, when it is applied, without applying it.
     *
     * @throws ExampleRejectedException
     */
    private function skip(StepDefinition $rule): void
    {
        foreach ($rule->parameters() as $parameter) {
            $pool = $parameter->pool();

            if ($pool === null) {
                $generator = $parameter->generator();

                assert($generator !== null);

                $generator->generate($this->source);

                continue;
            }

            $numberOfValues = 0;

            if (array_key_exists($pool, $this->pools)) {
                $numberOfValues = $this->pools[$pool]->count();
            }

            $this->source->draw(max(0, $numberOfValues - 1), new UniformDistribution);
        }

        if ($rule->produces() !== null) {
            $this->source->draw(0, new ExactDistribution(0));
        }
    }

    /**
     * @param non-empty-string $methodName
     * @param list<mixed>      $arguments
     *
     * @throws Throwable
     */
    private function call(string $methodName, array $arguments): mixed
    {
        return new ReflectionMethod($this->machine, $methodName)->invokeArgs($this->machine, $arguments);
    }

    /**
     * @param list<non-empty-string> $invariants
     *
     * @throws Throwable
     */
    private function checkInvariants(array $invariants): void
    {
        foreach ($invariants as $invariant) {
            $this->stepBeingTaken = sprintf('$machine->%s();', $invariant);

            try {
                $this->call($invariant, []);
            } catch (Throwable $t) {
                $this->record(sprintf('$machine->%s(); // invariant failed', $invariant));

                throw $t;
            }

            $this->stepBeingTaken = null;
        }
    }

    /**
     * The name of the variable that the next value of a pool is assigned to in
     * the report: the name of the pool, followed by the number of the value.
     *
     * The names of two pools can differ only in digits at the end of one of
     * them: the eleventh value of the pool "x" and the first value of the pool
     * "x1" would both be assigned to $x11. The name of the pool and the number
     * of the value are separated by an underscore when the name of the
     * variable is already used.
     *
     * @param non-empty-string $pool
     *
     * @return non-empty-string
     */
    private function nextVariableFor(string $pool): string
    {
        $number    = $this->nextNumberOfValueProducedFor($pool);
        $separator = '';

        while (array_key_exists($pool . $separator . $number, $this->variables)) {
            $separator .= '_';
        }

        $variable = $pool . $separator . $number;

        $this->variables[$variable] = true;

        return $variable;
    }

    /**
     * @param non-empty-string $pool
     *
     * @return positive-int
     */
    private function nextNumberOfValueProducedFor(string $pool): int
    {
        if (!array_key_exists($pool, $this->numberOfValuesProduced)) {
            $this->numberOfValuesProduced[$pool] = 1;

            return 1;
        }

        $this->numberOfValuesProduced[$pool]++;

        return $this->numberOfValuesProduced[$pool];
    }

    /**
     * @param non-empty-string $step
     */
    private function record(string $step): void
    {
        $this->stepBeingTaken = null;

        if ($this->exporter !== null) {
            $this->steps[] = $step;
        }
    }

    /**
     * A value that is exported as more than one line is written as a comment
     * of its own after the step.
     *
     * A closing tag ends a comment that begins with two slashes, and the PHP
     * code with it, wherever it occurs in the comment, in a string, for
     * instance. It is written as ?\> so that the steps remain PHP code. This
     * cannot be mistaken for a backslash in a string that is exported as PHP
     * code, as that is exported as two backslashes.
     *
     * @param non-empty-string $step
     *
     * @return non-empty-string
     */
    private function withComment(string $step, string $value): string
    {
        $value = str_replace('?>', '?\>', $value);

        if (!str_contains($value, "\n")) {
            return $step . ' // ' . $value;
        }

        return $step . "\n// " . str_replace("\n", "\n// ", $value);
    }
}
