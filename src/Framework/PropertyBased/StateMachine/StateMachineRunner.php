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

use function array_filter;
use function array_intersect;
use function array_key_exists;
use function array_keys;
use function array_reverse;
use function array_values;
use function assert;
use function count;
use function get_debug_type;
use function implode;
use function is_bool;
use function range;
use function sprintf;
use function str_contains;
use function str_replace;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\CounterexampleExporter;
use PHPUnit\Framework\PropertyBased\Distribution\ExactDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\InvalidStateMachineException;
use ReflectionMethod;
use Throwable;

/**
 * Runs a state machine with the choices of an example of a property, so that
 * its steps are generated, replayed, and shrunk together with the arguments of
 * the property.
 *
 * The initializations are taken first, in the order in which they are
 * declared. Then each step is drawn like an element of a list: a choice
 * whether another step is taken (0 = stop), the index of its rule in the order
 * in which the rules are declared, and the arguments of the rule, all in one
 * span. A value from a pool is drawn as its position counted from the value
 * that was produced last, so that it refers to the same value when earlier
 * steps are deleted.
 *
 * While choices are drawn at random, only rules whose preconditions hold and
 * whose pools have values are drawn, and each example enables a random subset
 * of the rules (swarm testing), without drawing choices for this. When a
 * choice sequence is replayed, a rule that cannot be applied is skipped and
 * does not count as a step, so that the steps after it are not reinterpreted.
 * No more steps are taken when no rule can be applied.
 *
 * The invariants are checked after the initializations and after every step,
 * except for those with #[Invariant(atEnd: true)], which are checked after the
 * initializations and after the last step.
 *
 * With an exporter, the steps are recorded as PHP code that calls the methods
 * of the state machine, for the report of the minimal counterexample: a value
 * that a step produced is assigned to a variable that is named after its pool,
 * the value that another step returned is written as a comment unless it is
 * null, and an invariant is written only when it failed.
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
    private readonly ChoiceSource $source;
    private readonly ?CounterexampleExporter $exporter;
    private readonly StateMachineDefinition $definition;
    private readonly object $machine;

    /**
     * The values that the steps produced and that were not consumed, by pool,
     * in the order in which they were produced, with the names of the
     * variables that they are assigned to in the report.
     *
     * @var array<non-empty-string, list<array{value: mixed, variable: non-empty-string}>>
     */
    private array $pools = [];

    /**
     * @var array<non-empty-string, positive-int>
     */
    private array $numberOfValuesProduced = [];

    /**
     * @var list<non-empty-string>
     */
    private array $steps = [];

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
        // likely
        $plannedNumberOfSteps = $this->source->suggest(range(1, $maximumNumberOfSteps), 1.0);
        $enabledRules         = $this->enabledRules();

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
                    $numberOfValues[$pool] = count($this->pools[$pool]);
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
                }

                continue;
            }

            // the value that was produced last is the simplest one
            $produced = $this->source->choose(array_reverse($this->valuesOf($pool)), new UniformDistribution);

            $arguments[] = $produced['value'];
            $exported[]  = $parameter->name() . ': $' . $produced['variable'];

            if ($parameter->consume()) {
                $this->pools[$pool] = array_values(
                    array_filter(
                        $this->valuesOf($pool),
                        static fn (array $value): bool => $value['variable'] !== $produced['variable'],
                    ),
                );
            }
        }

        $call     = sprintf('$machine->%s(%s);', $step->methodName(), implode(', ', $exported));
        $produces = $step->produces();
        $variable = null;

        if ($produces !== null) {
            $variable = $produces . $this->nextNumberOfValueProducedFor($produces);
            $call     = '$' . $variable . ' = ' . $call;
        }

        try {
            $result = $this->call($step->methodName(), $arguments);
        } catch (Throwable $t) {
            $this->record($call);

            throw $t;
        }

        if ($produces !== null) {
            assert($variable !== null);

            $this->pools[$produces][] = ['value' => $result, 'variable' => $variable];
        } elseif ($result !== null && $this->exporter !== null) {
            $call = $this->withComment($call, $this->exporter->export($result));
        }

        $this->record($call);
    }

    /**
     * @param non-empty-string $pool
     *
     * @return non-empty-list<array{value: mixed, variable: non-empty-string}>
     */
    private function valuesOf(string $pool): array
    {
        // the rule can only be applied when its pools have values
        assert(array_key_exists($pool, $this->pools) && $this->pools[$pool] !== []);

        return $this->pools[$pool];
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
            try {
                $this->call($invariant, []);
            } catch (Throwable $t) {
                $this->record(sprintf('$machine->%s(); // invariant failed', $invariant));

                throw $t;
            }
        }
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
        if ($this->exporter !== null) {
            $this->steps[] = $step;
        }
    }

    /**
     * A value that is exported as more than one line is written as a comment
     * of its own after the step.
     *
     * @param non-empty-string $step
     *
     * @return non-empty-string
     */
    private function withComment(string $step, string $value): string
    {
        if (!str_contains($value, "\n")) {
            return $step . ' // ' . $value;
        }

        return $step . "\n// " . str_replace("\n", "\n// ", $value);
    }
}
