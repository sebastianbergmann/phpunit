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

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ShrinkingResult
{
    private ChoiceSequence $sequence;

    /**
     * @var non-negative-int
     */
    private int $numberOfAttempts;

    /**
     * @var non-negative-int
     */
    private int $numberOfSteps;
    private bool $budgetExhausted;
    private bool $interrupted;

    /**
     * @param non-negative-int $numberOfAttempts
     * @param non-negative-int $numberOfSteps
     */
    public function __construct(ChoiceSequence $sequence, int $numberOfAttempts, int $numberOfSteps, bool $budgetExhausted, bool $interrupted)
    {
        $this->sequence         = $sequence;
        $this->numberOfAttempts = $numberOfAttempts;
        $this->numberOfSteps    = $numberOfSteps;
        $this->budgetExhausted  = $budgetExhausted;
        $this->interrupted      = $interrupted;
    }

    /**
     * The choice sequence of the minimal counterexample.
     */
    public function sequence(): ChoiceSequence
    {
        return $this->sequence;
    }

    /**
     * The number of simpler choice sequences that were replayed.
     *
     * @return non-negative-int
     */
    public function numberOfAttempts(): int
    {
        return $this->numberOfAttempts;
    }

    /**
     * The number of simpler choice sequences that were replayed and
     * falsified the property in the same way.
     *
     * @return non-negative-int
     */
    public function numberOfSteps(): int
    {
        return $this->numberOfSteps;
    }

    /**
     * Whether shrinking was stopped because the maximum number of attempts
     * was reached, in which case a simpler counterexample may exist.
     */
    public function budgetExhausted(): bool
    {
        return $this->budgetExhausted;
    }

    /**
     * Whether shrinking was stopped because the test run is to be stopped,
     * for instance because its deadline has passed, in which case a simpler
     * counterexample may exist.
     */
    public function interrupted(): bool
    {
        return $this->interrupted;
    }
}
