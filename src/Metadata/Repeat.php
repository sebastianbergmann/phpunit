<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Repeat extends Metadata
{
    /**
     * @var positive-int
     */
    private int $times;

    /**
     * @var positive-int
     */
    private int $failureThreshold;

    /**
     * @param positive-int $times
     * @param positive-int $failureThreshold
     */
    protected function __construct(Level $level, int $times, int $failureThreshold)
    {
        parent::__construct($level);

        $this->times            = $times;
        $this->failureThreshold = $failureThreshold;
    }

    public function isRepeat(): true
    {
        return true;
    }

    /**
     * @return positive-int
     */
    public function times(): int
    {
        return $this->times;
    }

    /**
     * @return positive-int
     */
    public function failureThreshold(): int
    {
        return $this->failureThreshold;
    }
}
