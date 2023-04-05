<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetry;

use PHPUnit\Event\RuntimeException;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class GarbageCollectorStatus
{
    private readonly int $runs;
    private readonly int $collected;
    private readonly int $threshold;
    private readonly int $roots;
    private readonly ?bool $running;
    private readonly ?bool $protected;
    private readonly ?bool $full;
    private readonly ?int $bufferSize;

    public function __construct(int $runs, int $collected, int $threshold, int $roots, ?bool $running, ?bool $protected, ?bool $full, ?int $bufferSize)
    {
        $this->runs       = $runs;
        $this->collected  = $collected;
        $this->threshold  = $threshold;
        $this->roots      = $roots;
        $this->running    = $running;
        $this->protected  = $protected;
        $this->full       = $full;
        $this->bufferSize = $bufferSize;
    }

    public function runs(): int
    {
        return $this->runs;
    }

    public function collected(): int
    {
        return $this->collected;
    }

    public function threshold(): int
    {
        return $this->threshold;
    }

    public function roots(): int
    {
        return $this->roots;
    }

    /**
     * @psalm-assert-if-true !null $this->running
     * @psalm-assert-if-true !null $this->protected
     * @psalm-assert-if-true !null $this->full
     * @psalm-assert-if-true !null $this->bufferSize
     */
    public function hasExtendedInformation(): bool
    {
        return $this->running !== null;
    }

    /**
     * @throws RuntimeException on PHP < 8.3
     */
    public function isRunning(): bool
    {
        if ($this->running === null) {
            throw new RuntimeException('Information not available');
        }

        return $this->running;
    }

    /**
     * @throws RuntimeException on PHP < 8.3
     */
    public function isProtected(): bool
    {
        if ($this->protected === null) {
            throw new RuntimeException('Information not available');
        }

        return $this->protected;
    }

    /**
     * @throws RuntimeException on PHP < 8.3
     */
    public function isFull(): bool
    {
        if ($this->full === null) {
            throw new RuntimeException('Information not available');
        }

        return $this->full;
    }

    /**
     * @throws RuntimeException on PHP < 8.3
     */
    public function bufferSize(): int
    {
        if ($this->bufferSize === null) {
            throw new RuntimeException('Information not available');
        }

        return $this->bufferSize;
    }
}
