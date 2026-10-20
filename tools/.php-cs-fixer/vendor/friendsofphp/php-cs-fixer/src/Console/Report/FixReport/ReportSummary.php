<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Console\Report\FixReport;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ReportSummary
{
    /**
     * @var array<string, array{appliedFixers: list<string>, diff: string}>
     */
    private array $changed;

    private int $filesCount;

    private int $time;

    private int $memory;

    private bool $addAppliedFixers;

    private bool $isDryRun;

    private bool $isDecoratedOutput;

    /**
     * @param array<string, array{appliedFixers: list<string>, diff: string}> $changed
     * @param int                                                             $time    duration in milliseconds
     * @param int                                                             $memory  memory usage in bytes
     */
    public function __construct(
        array $changed,
        int $filesCount,
        int $time,
        int $memory,
        bool $addAppliedFixers,
        bool $isDryRun,
        bool $isDecoratedOutput
    ) {
        $this->changed = $changed;
        $this->filesCount = $filesCount;
        $this->time = $time;
        $this->memory = $memory;
        $this->addAppliedFixers = $addAppliedFixers;
        $this->isDryRun = $isDryRun;
        $this->isDecoratedOutput = $isDecoratedOutput;
    }

    public function isDecoratedOutput(): bool
    {
        return $this->isDecoratedOutput;
    }

    public function isDryRun(): bool
    {
        return $this->isDryRun;
    }

    /**
     * @return array<string, array{appliedFixers: list<string>, diff: string}>
     */
    public function getChanged(): array
    {
        return $this->changed;
    }

    public function getMemory(): int
    {
        return $this->memory;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getFilesCount(): int
    {
        return $this->filesCount;
    }

    public function shouldAddAppliedFixers(): bool
    {
        return $this->addAppliedFixers;
    }
}
