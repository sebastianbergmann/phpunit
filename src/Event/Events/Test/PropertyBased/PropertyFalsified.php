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

use const PHP_EOL;
use function sprintf;
use PHPUnit\Event\Code;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PropertyFalsified implements Event
{
    private Telemetry\Info $telemetryInfo;
    private Code\Test $test;

    /**
     * @var non-negative-int
     */
    private int $seed;

    /**
     * @var positive-int
     */
    private int $numberOfExamplesChecked;

    /**
     * @var non-negative-int
     */
    private int $numberOfShrinkingAttempts;

    /**
     * @var non-negative-int
     */
    private int $numberOfShrinkingSteps;
    private bool $shrinkingBudgetExhausted;
    private bool $shrinkingInterrupted;

    /**
     * @var list<non-negative-int>
     */
    private array $choiceSequence;

    /**
     * @var array<non-empty-string, string>
     */
    private array $counterexample;

    /**
     * @param non-negative-int                $seed
     * @param positive-int                    $numberOfExamplesChecked
     * @param non-negative-int                $numberOfShrinkingAttempts
     * @param non-negative-int                $numberOfShrinkingSteps
     * @param list<non-negative-int>          $choiceSequence
     * @param array<non-empty-string, string> $counterexample
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Telemetry\Info $telemetryInfo, Code\Test $test, int $seed, int $numberOfExamplesChecked, int $numberOfShrinkingAttempts, int $numberOfShrinkingSteps, bool $shrinkingBudgetExhausted, bool $shrinkingInterrupted, array $choiceSequence, array $counterexample)
    {
        $this->telemetryInfo             = $telemetryInfo;
        $this->test                      = $test;
        $this->seed                      = $seed;
        $this->numberOfExamplesChecked   = $numberOfExamplesChecked;
        $this->numberOfShrinkingAttempts = $numberOfShrinkingAttempts;
        $this->numberOfShrinkingSteps    = $numberOfShrinkingSteps;
        $this->shrinkingBudgetExhausted  = $shrinkingBudgetExhausted;
        $this->shrinkingInterrupted      = $shrinkingInterrupted;
        $this->choiceSequence            = $choiceSequence;
        $this->counterexample            = $counterexample;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function test(): Code\Test
    {
        return $this->test;
    }

    /**
     * The seed of the random number generator of the property, which is
     * derived from the seed of the test run and the ID of the test.
     *
     * @return non-negative-int
     */
    public function seed(): int
    {
        return $this->seed;
    }

    /**
     * The number of examples for which the property was checked until it was
     * falsified, including the example that falsified it.
     *
     * @return positive-int
     */
    public function numberOfExamplesChecked(): int
    {
        return $this->numberOfExamplesChecked;
    }

    /**
     * The number of simpler examples that were tried while the counterexample
     * was shrunk.
     *
     * @return non-negative-int
     */
    public function numberOfShrinkingAttempts(): int
    {
        return $this->numberOfShrinkingAttempts;
    }

    /**
     * The number of shrinking attempts that found a simpler counterexample.
     *
     * @return non-negative-int
     */
    public function numberOfShrinkingSteps(): int
    {
        return $this->numberOfShrinkingSteps;
    }

    /**
     * Whether shrinking was stopped because the maximum number of shrinking
     * attempts was reached, in which case a simpler counterexample may exist.
     */
    public function shrinkingBudgetExhausted(): bool
    {
        return $this->shrinkingBudgetExhausted;
    }

    /**
     * Whether shrinking was stopped because the test run was aborted, for
     * instance because its deadline has passed, in which case a simpler
     * counterexample may exist.
     */
    public function shrinkingInterrupted(): bool
    {
        return $this->shrinkingInterrupted;
    }

    /**
     * The choices from which the arguments of the minimal counterexample
     * were generated.
     *
     * @return list<non-negative-int>
     */
    public function choiceSequence(): array
    {
        return $this->choiceSequence;
    }

    /**
     * The arguments of the minimal counterexample, exported as PHP code, by
     * the name of the parameter they were passed to.
     *
     * @return array<non-empty-string, string>
     */
    public function counterexample(): array
    {
        return $this->counterexample;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        $shrinkingStopped = '';

        if ($this->shrinkingBudgetExhausted) {
            $shrinkingStopped = ', budget exhausted';
        }

        if ($this->shrinkingInterrupted) {
            $shrinkingStopped = ', shrinking interrupted';
        }

        $buffer = sprintf(
            'Test Property Falsified (%s, seed: %d, examples: %d, shrinking steps: %d, shrinking attempts: %d%s)',
            $this->test->id(),
            $this->seed,
            $this->numberOfExamplesChecked,
            $this->numberOfShrinkingSteps,
            $this->numberOfShrinkingAttempts,
            $shrinkingStopped,
        );

        foreach ($this->counterexample as $parameter => $value) {
            $buffer .= sprintf(
                PHP_EOL . '$%s = %s;',
                $parameter,
                $value,
            );
        }

        return $buffer;
    }
}
