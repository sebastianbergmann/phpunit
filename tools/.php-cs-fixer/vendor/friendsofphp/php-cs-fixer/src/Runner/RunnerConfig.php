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

namespace PhpCsFixer\Runner;

use PhpCsFixer\Runner\Parallel\ParallelConfig;

/**
 * @author Greg Korba <greg@codito.dev>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class RunnerConfig
{
    private bool $isDryRun;
    private bool $stopOnViolation;
    private ParallelConfig $parallelConfig;
    private ?string $configFile;

    public function __construct(
        bool $isDryRun,
        bool $stopOnViolation,
        ParallelConfig $parallelConfig,
        ?string $configFile = null
    ) {
        $this->isDryRun = $isDryRun;
        $this->stopOnViolation = $stopOnViolation;
        $this->parallelConfig = $parallelConfig;
        $this->configFile = $configFile;
    }

    public function isDryRun(): bool
    {
        return $this->isDryRun;
    }

    public function shouldStopOnViolation(): bool
    {
        return $this->stopOnViolation;
    }

    public function getParallelConfig(): ParallelConfig
    {
        return $this->parallelConfig;
    }

    public function getConfigFile(): ?string
    {
        return $this->configFile;
    }
}
