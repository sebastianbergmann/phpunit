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

use SebastianBergmann\ChoiceSequence\ChoiceSequence;

/**
 * The result of an example of a property that was run on its own rather than
 * while the property was checked: whether it passed, was rejected, skipped,
 * or marked as incomplete, or falsified the property, and the choices from
 * which its arguments and the values that it drew were generated.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExampleResult
{
    /**
     * @var 'errored'|'failed'|'incomplete'|'passed'|'rejected'|'skipped'|'timeout'
     */
    private string $outcome;
    private ?Falsification $falsification;
    private ChoiceSequence $sequence;

    /**
     * @var ?array<non-empty-string, string>
     */
    private ?array $arguments;

    /**
     * @var ?list<array{label: ?non-empty-string, value: string}>
     */
    private ?array $drawnValues;

    /**
     * @var ?list<non-empty-string>
     */
    private ?array $steps;

    public static function passed(ChoiceSequence $sequence): self
    {
        return new self('passed', null, $sequence);
    }

    public static function rejected(ChoiceSequence $sequence): self
    {
        return new self('rejected', null, $sequence);
    }

    public static function skipped(ChoiceSequence $sequence): self
    {
        return new self('skipped', null, $sequence);
    }

    public static function incomplete(ChoiceSequence $sequence): self
    {
        return new self('incomplete', null, $sequence);
    }

    /**
     * @param bool $failed whether the example failed rather than errored
     */
    public static function falsified(Falsification $falsification, bool $failed, ChoiceSequence $sequence): self
    {
        if ($falsification->isTimeout()) {
            return new self('timeout', $falsification, $sequence);
        }

        return new self($failed ? 'failed' : 'errored', $falsification, $sequence);
    }

    /**
     * @param 'errored'|'failed'|'incomplete'|'passed'|'rejected'|'skipped'|'timeout' $outcome
     * @param ?array<non-empty-string, string>                                        $arguments
     * @param ?list<array{label: ?non-empty-string, value: string}>                   $drawnValues
     * @param ?list<non-empty-string>                                                 $steps
     */
    private function __construct(string $outcome, ?Falsification $falsification, ChoiceSequence $sequence, ?array $arguments = null, ?array $drawnValues = null, ?array $steps = null)
    {
        $this->outcome       = $outcome;
        $this->falsification = $falsification;
        $this->sequence      = $sequence;
        $this->arguments     = $arguments;
        $this->drawnValues   = $drawnValues;
        $this->steps         = $steps;
    }

    /**
     * The same result with the arguments of the example, the values that it
     * drew with draw(), and the steps of its state machine, exported as they
     * are reported for a counterexample.
     *
     * @param array<non-empty-string, string>                      $arguments
     * @param list<array{label: ?non-empty-string, value: string}> $drawnValues
     * @param list<non-empty-string>                               $steps
     */
    public function withExportedValues(array $arguments, array $drawnValues, array $steps): self
    {
        return new self($this->outcome, $this->falsification, $this->sequence, $arguments, $drawnValues, $steps);
    }

    /**
     * @return 'errored'|'failed'|'incomplete'|'passed'|'rejected'|'skipped'|'timeout'
     */
    public function outcome(): string
    {
        return $this->outcome;
    }

    /**
     * How the example falsified the property, null when it did not.
     */
    public function falsification(): ?Falsification
    {
        return $this->falsification;
    }

    /**
     * The choices that were consumed, with their spans and maxima.
     */
    public function sequence(): ChoiceSequence
    {
        return $this->sequence;
    }

    /**
     * The exported arguments by the names of their parameters, null when
     * they were not exported.
     *
     * @return ?array<non-empty-string, string>
     */
    public function arguments(): ?array
    {
        return $this->arguments;
    }

    /**
     * The exported values that the example drew with draw(), null when they
     * were not exported.
     *
     * @return ?list<array{label: ?non-empty-string, value: string}>
     */
    public function drawnValues(): ?array
    {
        return $this->drawnValues;
    }

    /**
     * The steps of the state machine of the example, null when they were not
     * exported.
     *
     * @return ?list<non-empty-string>
     */
    public function steps(): ?array
    {
        return $this->steps;
    }
}
