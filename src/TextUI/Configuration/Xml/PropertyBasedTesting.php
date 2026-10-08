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
    public const int DEFAULT_NUMBER_OF_EXAMPLES                   = 100;
    public const int DEFAULT_MAXIMUM_NUMBER_OF_SHRINKING_ATTEMPTS = 1000;

    /**
     * @var positive-int
     */
    private int $examples;

    /**
     * @var non-negative-int
     */
    private int $maximumShrinkingAttempts;

    /**
     * @param positive-int     $examples
     * @param non-negative-int $maximumShrinkingAttempts
     */
    public function __construct(int $examples, int $maximumShrinkingAttempts)
    {
        $this->examples                 = $examples;
        $this->maximumShrinkingAttempts = $maximumShrinkingAttempts;
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
     * @return non-negative-int
     */
    public function maximumShrinkingAttempts(): int
    {
        return $this->maximumShrinkingAttempts;
    }
}
