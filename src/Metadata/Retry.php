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
final readonly class Retry extends Metadata
{
    /**
     * @var positive-int
     */
    private int $maxAttempts;

    /**
     * @param positive-int $maxAttempts
     */
    protected function __construct(Level $level, int $maxAttempts)
    {
        parent::__construct($level);

        $this->maxAttempts = $maxAttempts;
    }

    public function isRetry(): true
    {
        return true;
    }

    /**
     * @return positive-int
     */
    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }
}
