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

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class GarbageCollectorStatus
{
    private int $runs;
    private int $collected;
    private int $threshold;
    private int $roots;
    private float $applicationTime;
    private float $collectorTime;
    private float $destructorTime;
    private float $freeTime;
    private bool $running;
    private bool $protected;
    private bool $full;
    private int $bufferSize;

    public function __construct(int $runs, int $collected, int $threshold, int $roots, float $applicationTime, float $collectorTime, float $destructorTime, float $freeTime, bool $running, bool $protected, bool $full, int $bufferSize)
    {
        $this->runs            = $runs;
        $this->collected       = $collected;
        $this->threshold       = $threshold;
        $this->roots           = $roots;
        $this->applicationTime = $applicationTime;
        $this->collectorTime   = $collectorTime;
        $this->destructorTime  = $destructorTime;
        $this->freeTime        = $freeTime;
        $this->running         = $running;
        $this->protected       = $protected;
        $this->full            = $full;
        $this->bufferSize      = $bufferSize;
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

    public function applicationTime(): float
    {
        return $this->applicationTime;
    }

    public function collectorTime(): float
    {
        return $this->collectorTime;
    }

    public function destructorTime(): float
    {
        return $this->destructorTime;
    }

    public function freeTime(): float
    {
        return $this->freeTime;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function isProtected(): bool
    {
        return $this->protected;
    }

    public function isFull(): bool
    {
        return $this->full;
    }

    public function bufferSize(): int
    {
        return $this->bufferSize;
    }
}
