<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class PropertyBasedTesting
{
    public const int DEFAULT_NUMBER_OF_EXAMPLES                                 = 100;
    public const int DEFAULT_EXAMPLES_MULTIPLIER                                = 1;
    public const int DEFAULT_MAXIMUM_NUMBER_OF_SHRINKING_ATTEMPTS               = 1000;
    public const int DEFAULT_EXAMPLES_CHECKED_UNTIL_HEALTH_IS_ASSUMED           = 10;
    public const int DEFAULT_MAXIMUM_REJECTED_EXAMPLES_UNTIL_HEALTH_IS_ASSUMED  = 50;
    public const int DEFAULT_MAXIMUM_TOO_LARGE_EXAMPLES_UNTIL_HEALTH_IS_ASSUMED = 20;

    /**
     * @var positive-int
     */
    private int $examples;

    /**
     * @var positive-int
     */
    private int $examplesMultiplier;

    /**
     * @var non-negative-int
     */
    private int $maximumShrinkingAttempts;

    /**
     * @var non-negative-int
     */
    private int $examplesCheckedUntilHealthIsAssumed;

    /**
     * @var positive-int
     */
    private int $maximumRejectedExamplesUntilHealthIsAssumed;

    /**
     * @var positive-int
     */
    private int $maximumTooLargeExamplesUntilHealthIsAssumed;

    /**
     * @param positive-int     $examples
     * @param positive-int     $examplesMultiplier
     * @param non-negative-int $maximumShrinkingAttempts
     * @param non-negative-int $examplesCheckedUntilHealthIsAssumed
     * @param positive-int     $maximumRejectedExamplesUntilHealthIsAssumed
     * @param positive-int     $maximumTooLargeExamplesUntilHealthIsAssumed
     */
    public function __construct(int $examples, int $examplesMultiplier, int $maximumShrinkingAttempts, int $examplesCheckedUntilHealthIsAssumed, int $maximumRejectedExamplesUntilHealthIsAssumed, int $maximumTooLargeExamplesUntilHealthIsAssumed)
    {
        $this->examples                                    = $examples;
        $this->examplesMultiplier                          = $examplesMultiplier;
        $this->maximumShrinkingAttempts                    = $maximumShrinkingAttempts;
        $this->examplesCheckedUntilHealthIsAssumed         = $examplesCheckedUntilHealthIsAssumed;
        $this->maximumRejectedExamplesUntilHealthIsAssumed = $maximumRejectedExamplesUntilHealthIsAssumed;
        $this->maximumTooLargeExamplesUntilHealthIsAssumed = $maximumTooLargeExamplesUntilHealthIsAssumed;
    }

    /**
     * The number of examples that are checked for a property for which no
     * number of examples is specified.
     *
     * @return positive-int
     */
    public function examples(): int
    {
        return $this->examples;
    }

    /**
     * The number by which the number of examples of every property is
     * multiplied, whether it is specified for the property or not.
     *
     * @return positive-int
     */
    public function examplesMultiplier(): int
    {
        return $this->examplesMultiplier;
    }

    /**
     * @return non-negative-int
     */
    public function maximumShrinkingAttempts(): int
    {
        return $this->maximumShrinkingAttempts;
    }

    /**
     * While fewer examples of a property than this have been checked, no
     * further examples are generated once too many of them were rejected.
     * With 0, examples are generated regardless of how many were rejected.
     *
     * @return non-negative-int
     */
    public function examplesCheckedUntilHealthIsAssumed(): int
    {
        return $this->examplesCheckedUntilHealthIsAssumed;
    }

    /**
     * How many examples of a property can be rejected before enough of them
     * were checked.
     *
     * @return positive-int
     */
    public function maximumRejectedExamplesUntilHealthIsAssumed(): int
    {
        return $this->maximumRejectedExamplesUntilHealthIsAssumed;
    }

    /**
     * How many examples of a property can be rejected because they were too
     * large before enough of them were checked.
     *
     * @return positive-int
     */
    public function maximumTooLargeExamplesUntilHealthIsAssumed(): int
    {
        return $this->maximumTooLargeExamplesUntilHealthIsAssumed;
    }
}
