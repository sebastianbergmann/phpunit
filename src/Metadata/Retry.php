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
    private int $maxRetries;
    private int $delay;

    /**
     * @var ?non-empty-string
     */
    private ?string $retryOn;

    /**
     * @param ?non-empty-string $retryOn
     */
    public function __construct(int $level, int $maxRetries, int $delay, ?string $retryOn)
    {
        parent::__construct($level);

        $this->maxRetries = $maxRetries;
        $this->delay      = $delay;
        $this->retryOn    = $retryOn;
    }

    public function isRetry(): bool
    {
        return true;
    }

    public function maxRetries(): int
    {
        return $this->maxRetries;
    }

    public function delay(): int
    {
        return $this->delay;
    }

    /**
     * @return ?non-empty-string
     */
    public function retryOn(): ?string
    {
        return $this->retryOn;
    }
}
