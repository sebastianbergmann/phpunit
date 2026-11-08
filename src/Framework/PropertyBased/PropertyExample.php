<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function array_key_exists;
use function in_array;
use function is_finite;
use function sprintf;
use PHPUnit\Framework\Exception;
use Throwable;

/**
 * An example of a property: the arguments with which its test method is
 * called, and what the example records while it runs, with assume(), draw(),
 * runStateMachine(), classify(), observe(), and target() of TestCase, which
 * delegate to it, and the throwable that ended it.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PropertyExample
{
    /**
     * @var list<mixed>
     */
    private readonly array $arguments;
    private readonly bool $failureIsReported;
    private readonly ?DrawnValues $drawnValues;
    private bool $rejected = false;

    /**
     * Why the example was rejected, if it was: by assume(), or because draw()
     * could not generate a value.
     */
    private RejectionReason $rejectionReason = RejectionReason::Assumption;
    private bool $stateMachineWasRun         = false;

    /**
     * @var ?non-negative-int
     */
    private ?int $numberOfStepsOfStateMachine = null;

    /**
     * @var list<non-empty-string>
     */
    private array $classifications = [];

    /**
     * @var list<array{label: non-empty-string, value: float|int}>
     */
    private array $observations = [];

    /**
     * @var array<non-empty-string, float|int>
     */
    private array $targets                   = [];
    private ?Throwable $throwableThatEndedIt = null;

    /**
     * @param list<mixed>  $arguments
     * @param bool         $failureIsReported whether the failure or error of the example is the one that is reported for the property, which it is for the final replay of the minimal counterexample and for an explicit example
     * @param ?DrawnValues $drawnValues       from which the values that the example draws with draw() are drawn, null for an explicit example, for which values cannot be drawn
     */
    public function __construct(array $arguments, bool $failureIsReported, ?DrawnValues $drawnValues)
    {
        $this->arguments         = $arguments;
        $this->failureIsReported = $failureIsReported;
        $this->drawnValues       = $drawnValues;
    }

    /**
     * @return list<mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    public function failureIsReported(): bool
    {
        return $this->failureIsReported;
    }

    /**
     * @throws ExampleRejectedByAssumptionException when $condition is false
     */
    public function assume(bool $condition): void
    {
        if ($condition) {
            return;
        }

        throw new ExampleRejectedByAssumptionException;
    }

    /**
     * @template T
     *
     * @param ValueGenerator<T> $generator
     * @param ?non-empty-string $label
     *
     * @throws ExampleRejectedByAssumptionException when no value can be generated
     * @throws Exception                            when it is called for an explicit example, or the label is empty
     *
     * @return T
     */
    public function draw(ValueGenerator $generator, ?string $label): mixed
    {
        if ($this->drawnValues === null) {
            throw new Exception('draw() cannot be used while an explicit example is checked');
        }

        if ($label === '') {
            throw new Exception('The label passed to draw() must not be empty');
        }

        try {
            return $this->drawnValues->draw($generator, $label);
        } catch (ExampleRejectedException $e) {
            $this->rejectionReason = $e->reason();

            throw new ExampleRejectedByAssumptionException;
        }
    }

    /**
     * Runs the state machine, and observes the number of steps that it takes,
     * and the number of steps that it takes with each rule.
     *
     * @param positive-int $maximumNumberOfSteps
     *
     * @throws ExampleRejectedByAssumptionException when no arguments can be generated for a step
     * @throws Exception                            when it is called for an explicit example, it is called more than once, or the maximum number of steps is not positive
     * @throws InvalidStateMachineException         when the attributes of the state machine are not valid or a precondition does not return a bool
     */
    public function runStateMachine(object $machine, int $maximumNumberOfSteps): void
    {
        if ($this->drawnValues === null) {
            throw new Exception('runStateMachine() cannot be used while an explicit example is checked');
        }

        if ($maximumNumberOfSteps < 1) {
            throw new Exception('The maximum number of steps passed to runStateMachine() must be positive');
        }

        if ($this->stateMachineWasRun) {
            throw new Exception('runStateMachine() can only be used once per example');
        }

        $this->stateMachineWasRun = true;

        try {
            $stateMachine = $this->drawnValues->runStateMachine($machine, $maximumNumberOfSteps);
        } catch (ExampleRejectedException $e) {
            $this->rejectionReason = $e->reason();

            throw new ExampleRejectedByAssumptionException;
        }

        $this->numberOfStepsOfStateMachine = $stateMachine->numberOfSteps();

        $this->observe('steps', $stateMachine->numberOfSteps());

        foreach ($stateMachine->numberOfStepsByRule() as $rule => $numberOfSteps) {
            $this->observe(sprintf('steps with %s()', $rule), $numberOfSteps);
        }
    }

    /**
     * @param non-empty-string $label
     *
     * @throws Exception when the label is empty
     */
    public function classify(string $label): void
    {
        if ($label === '') {
            throw new Exception('The label passed to classify() must not be empty');
        }

        if (!in_array($label, $this->classifications, true)) {
            $this->classifications[] = $label;
        }
    }

    /**
     * @param non-empty-string $label
     *
     * @throws Exception when the label is empty or the value is not finite
     */
    public function observe(string $label, float|int $value): void
    {
        if ($label === '') {
            throw new Exception('The label passed to observe() must not be empty');
        }

        if (!is_finite($value)) {
            throw new Exception('The value passed to observe() must be finite');
        }

        $this->observations[] = ['label' => $label, 'value' => $value];
    }

    /**
     * @param non-empty-string $label
     *
     * @throws Exception when the label is empty, the score is not finite, or a score was already targeted with the label
     */
    public function target(string $label, float|int $score): void
    {
        if ($label === '') {
            throw new Exception('The label passed to target() must not be empty');
        }

        if (!is_finite($score)) {
            throw new Exception('The score passed to target() must be finite');
        }

        if (array_key_exists($label, $this->targets)) {
            throw new Exception(
                sprintf(
                    'target() can only be used once per example with the label "%s"',
                    $label,
                ),
            );
        }

        $this->targets[$label] = $score;
    }

    /**
     * Whether the example was discarded because assume() was called with
     * false, or because draw() or runStateMachine() could not generate a
     * value. An example that failed or errored afterwards, in tearDown() or
     * in the destructor of a test double, for instance, was not discarded.
     */
    public function wasRejected(): bool
    {
        return $this->rejected;
    }

    public function setRejected(bool $rejected): void
    {
        $this->rejected = $rejected;
    }

    /**
     * Why the example was rejected, when it was.
     */
    public function rejectionReason(): RejectionReason
    {
        return $this->rejectionReason;
    }

    /**
     * The labels with which the example was classified by classify().
     *
     * @return list<non-empty-string>
     */
    public function classifications(): array
    {
        return $this->classifications;
    }

    /**
     * The numeric values that the example observed with observe(), with their
     * labels, in the order in which they were observed.
     *
     * @return list<array{label: non-empty-string, value: float|int}>
     */
    public function observations(): array
    {
        return $this->observations;
    }

    /**
     * The scores that the example targeted with target(), by their label.
     *
     * @return array<non-empty-string, float|int>
     */
    public function targets(): array
    {
        return $this->targets;
    }

    /**
     * The number of steps that the state machine that the example ran with
     * runStateMachine() took, null when it did not run one or the state
     * machine did not finish.
     *
     * @return ?non-negative-int
     */
    public function numberOfStepsOfStateMachine(): ?int
    {
        return $this->numberOfStepsOfStateMachine;
    }

    /**
     * The throwable that ended the example: the one that caused its failure
     * or error, as it was raised, or the TimeoutException when the example
     * exceeded its time limit.
     */
    public function throwableThatEndedIt(): ?Throwable
    {
        return $this->throwableThatEndedIt;
    }

    public function rememberThrowableThatEndedIt(Throwable $throwable): void
    {
        $this->throwableThatEndedIt = $throwable;
    }
}
