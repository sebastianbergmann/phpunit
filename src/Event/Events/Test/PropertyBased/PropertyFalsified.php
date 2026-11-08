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
use function is_int;
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
     * @var non-negative-int
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
    private Code\Counterexample $counterexample;

    /**
     * @var list<Code\OtherFalsification>
     */
    private array $otherFalsifications;

    /**
     * @var non-negative-int
     */
    private int $numberOfReplays;

    /**
     * @var non-negative-int
     */
    private int $numberOfReproductions;

    /**
     * @param non-negative-int              $seed
     * @param non-negative-int              $numberOfExamplesChecked
     * @param non-negative-int              $numberOfShrinkingAttempts
     * @param non-negative-int              $numberOfShrinkingSteps
     * @param list<Code\OtherFalsification> $otherFalsifications
     * @param non-negative-int              $numberOfReplays
     * @param non-negative-int              $numberOfReproductions
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Telemetry\Info $telemetryInfo, Code\Test $test, int $seed, int $numberOfExamplesChecked, int $numberOfShrinkingAttempts, int $numberOfShrinkingSteps, bool $shrinkingBudgetExhausted, bool $shrinkingInterrupted, Code\Counterexample $counterexample, array $otherFalsifications, int $numberOfReplays, int $numberOfReproductions)
    {
        $this->telemetryInfo             = $telemetryInfo;
        $this->test                      = $test;
        $this->seed                      = $seed;
        $this->numberOfExamplesChecked   = $numberOfExamplesChecked;
        $this->numberOfShrinkingAttempts = $numberOfShrinkingAttempts;
        $this->numberOfShrinkingSteps    = $numberOfShrinkingSteps;
        $this->shrinkingBudgetExhausted  = $shrinkingBudgetExhausted;
        $this->shrinkingInterrupted      = $shrinkingInterrupted;
        $this->counterexample            = $counterexample;
        $this->otherFalsifications       = $otherFalsifications;
        $this->numberOfReplays           = $numberOfReplays;
        $this->numberOfReproductions     = $numberOfReproductions;
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
     * The number of generated examples for which the property was checked
     * until it was falsified, including the example that falsified it.
     * Explicit examples are not counted, and they are checked before examples
     * are generated, so it is 0 when an explicit example falsified the
     * property.
     *
     * @return non-negative-int
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
     * The minimal counterexample, or the explicit example that falsified the
     * property. An explicit example is not shrunk, so there are no shrinking
     * attempts for it.
     */
    public function counterexample(): Code\Counterexample
    {
        return $this->counterexample;
    }

    /**
     * The other ways in which the property was falsified while the
     * counterexample was shrunk, each with the simplest counterexample that
     * falsified it that way, in the order in which they were first seen. It
     * is empty when the property was falsified by an explicit example, which
     * is not shrunk.
     *
     * @return list<Code\OtherFalsification>
     */
    public function otherFalsifications(): array
    {
        return $this->otherFalsifications;
    }

    /**
     * How often the example that first falsified the property was replayed
     * before its counterexample was shrunk, to tell whether it falsifies the
     * property again. Replays whose generators did not generate the same
     * arguments are not counted. It is 0 when the property was falsified by an
     * explicit example, which is not replayed, or by an example that exceeded
     * its time limit, which would take as long as the time limit again.
     *
     * @return non-negative-int
     */
    public function numberOfReplays(): int
    {
        return $this->numberOfReplays;
    }

    /**
     * How many of the replays of the example that first falsified the
     * property falsified it again in the same way. When this is less than the
     * number of replays, the outcome of the property does not depend on its
     * arguments alone.
     *
     * @return non-negative-int
     */
    public function numberOfReproductions(): int
    {
        return $this->numberOfReproductions;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        $explicitExample = $this->counterexample->explicitExample();

        if ($explicitExample !== null) {
            return $this->withCounterexample(
                sprintf(
                    'Test Property Falsified (%s, explicit example %s)',
                    $this->test->id(),
                    $this->labelOfExplicitExample($explicitExample),
                ),
            );
        }

        $shrinkingStopped = '';

        if ($this->shrinkingBudgetExhausted) {
            $shrinkingStopped = ', budget exhausted';
        }

        if ($this->shrinkingInterrupted) {
            $shrinkingStopped = ', shrinking interrupted';
        }

        $reproduced = '';

        if ($this->numberOfReproductions < $this->numberOfReplays) {
            $reproduced = sprintf(', reproduced in %d of %d replays', $this->numberOfReproductions, $this->numberOfReplays);
        }

        return $this->withCounterexample(
            sprintf(
                'Test Property Falsified (%s, seed: %d, examples: %d, shrinking steps: %d, shrinking attempts: %d%s%s)',
                $this->test->id(),
                $this->seed,
                $this->numberOfExamplesChecked,
                $this->numberOfShrinkingSteps,
                $this->numberOfShrinkingAttempts,
                $shrinkingStopped,
                $reproduced,
            ),
        );
    }

    /**
     * @param non-empty-string $buffer
     *
     * @return non-empty-string
     */
    private function withCounterexample(string $buffer): string
    {
        $buffer = self::withArguments($buffer, $this->counterexample);

        foreach ($this->otherFalsifications as $otherFalsification) {
            $buffer = self::withArguments(
                $buffer . PHP_EOL . 'Also falsified by ' . $otherFalsification->description() . ':',
                $otherFalsification->counterexample(),
            );
        }

        return $buffer;
    }

    /**
     * @param int|non-empty-string $name
     *
     * @return non-empty-string
     */
    private function labelOfExplicitExample(int|string $name): string
    {
        if (is_int($name)) {
            return '#' . $name;
        }

        return sprintf('"%s"', $name);
    }

    /**
     * @param non-empty-string $buffer
     *
     * @return non-empty-string
     */
    private static function withArguments(string $buffer, Code\Counterexample $counterexample): string
    {
        foreach ($counterexample->arguments() as $parameter => $value) {
            $buffer .= sprintf(
                PHP_EOL . '$%s = %s;',
                $parameter,
                $value,
            );
        }

        foreach ($counterexample->drawnValues() as $number => $drawnValue) {
            $buffer .= PHP_EOL . self::describeDrawnValue($number + 1, $drawnValue->label()) . ': ' . $drawnValue->value();
        }

        foreach ($counterexample->steps() as $step) {
            $buffer .= PHP_EOL . $step;
        }

        return $buffer;
    }

    /**
     * Describes a value that was drawn with draw() by its number and its
     * label, for instance "Draw 2 (index)".
     *
     * @param positive-int      $number
     * @param ?non-empty-string $label
     *
     * @return non-empty-string
     */
    private static function describeDrawnValue(int $number, ?string $label): string
    {
        if ($label === null) {
            return 'Draw ' . $number;
        }

        return sprintf('Draw %d (%s)', $number, $label);
    }
}
