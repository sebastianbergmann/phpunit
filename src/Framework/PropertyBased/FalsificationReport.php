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

use PHPUnit\Event\Code\Counterexample;
use PHPUnit\Event\Code\OtherFalsification;

/**
 * How a property was falsified, as it is reported: with the
 * Test\PropertyFalsified event and as part of the failure or error of the
 * property.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FalsificationReport
{
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
    private Counterexample $counterexample;

    /**
     * @var list<OtherFalsification>
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
     * @param non-negative-int         $seed
     * @param non-negative-int         $numberOfExamplesChecked
     * @param non-negative-int         $numberOfShrinkingAttempts
     * @param non-negative-int         $numberOfShrinkingSteps
     * @param list<OtherFalsification> $otherFalsifications
     * @param non-negative-int         $numberOfReplays
     * @param non-negative-int         $numberOfReproductions
     */
    public function __construct(int $seed, int $numberOfExamplesChecked, int $numberOfShrinkingAttempts, int $numberOfShrinkingSteps, bool $shrinkingBudgetExhausted, bool $shrinkingInterrupted, Counterexample $counterexample, array $otherFalsifications, int $numberOfReplays, int $numberOfReproductions)
    {
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

    /**
     * @return non-negative-int
     */
    public function seed(): int
    {
        return $this->seed;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfExamplesChecked(): int
    {
        return $this->numberOfExamplesChecked;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfShrinkingAttempts(): int
    {
        return $this->numberOfShrinkingAttempts;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfShrinkingSteps(): int
    {
        return $this->numberOfShrinkingSteps;
    }

    public function shrinkingBudgetExhausted(): bool
    {
        return $this->shrinkingBudgetExhausted;
    }

    public function shrinkingInterrupted(): bool
    {
        return $this->shrinkingInterrupted;
    }

    public function counterexample(): Counterexample
    {
        return $this->counterexample;
    }

    /**
     * @return list<OtherFalsification>
     */
    public function otherFalsifications(): array
    {
        return $this->otherFalsifications;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfReplays(): int
    {
        return $this->numberOfReplays;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfReproductions(): int
    {
        return $this->numberOfReproductions;
    }
}
