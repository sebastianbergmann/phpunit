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

namespace PhpCsFixer\Runner\Parallel;

/**
 * @author Greg Korba <greg@codito.dev>
 *
 * @readonly
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ParallelConfig
{
    /** @internal */
    public const DEFAULT_FILES_PER_PROCESS = 10;

    /** @internal */
    public const DEFAULT_PROCESS_TIMEOUT = 120;

    private int $filesPerProcess;
    private int $maxProcesses;
    private int $processTimeout;

    /**
     * @param positive-int $maxProcesses
     * @param positive-int $filesPerProcess
     * @param positive-int $processTimeout
     */
    public function __construct(
        int $maxProcesses = 2,
        int $filesPerProcess = self::DEFAULT_FILES_PER_PROCESS,
        int $processTimeout = self::DEFAULT_PROCESS_TIMEOUT
    ) {
        if ($maxProcesses <= 0 || $filesPerProcess <= 0 || $processTimeout <= 0) {
            throw new \InvalidArgumentException('Invalid parallelisation configuration: only positive integers are allowed');
        }

        $this->maxProcesses = $maxProcesses;
        $this->filesPerProcess = $filesPerProcess;
        $this->processTimeout = $processTimeout;
    }

    public function getFilesPerProcess(): int
    {
        return $this->filesPerProcess;
    }

    public function getMaxProcesses(): int
    {
        return $this->maxProcesses;
    }

    public function getProcessTimeout(): int
    {
        return $this->processTimeout;
    }
}
