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
     */
    private function __construct(string $outcome, ?Falsification $falsification, ChoiceSequence $sequence)
    {
        $this->outcome       = $outcome;
        $this->falsification = $falsification;
        $this->sequence      = $sequence;
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
}
