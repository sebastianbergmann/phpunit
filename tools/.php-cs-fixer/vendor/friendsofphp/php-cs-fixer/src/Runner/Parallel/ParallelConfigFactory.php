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

use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\FinderRegistry;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ParallelConfigFactory
{
    private static ?CpuCoreCounter $cpuDetector = null;

    private function __construct() {}

    public static function sequential(): ParallelConfig
    {
        return new ParallelConfig(1);
    }

    /**
     * @param null|positive-int $filesPerProcess
     * @param null|positive-int $processTimeout
     * @param null|positive-int $maxProcesses
     */
    public static function detect(
        ?int $filesPerProcess = null,
        ?int $processTimeout = null,
        ?int $maxProcesses = null
    ): ParallelConfig {
        if (null === self::$cpuDetector) {
            self::$cpuDetector = new CpuCoreCounter([
                ...FinderRegistry::getDefaultLogicalFinders(),
                new DummyCpuCoreFinder(1),
            ]);
        }

        // Reserve 1 core for the main orchestrating process
        $available = self::$cpuDetector->getAvailableForParallelisation(1, $maxProcesses);

        return new ParallelConfig(
            $available->availableCpus,
            $filesPerProcess ?? ParallelConfig::DEFAULT_FILES_PER_PROCESS,
            $processTimeout ?? ParallelConfig::DEFAULT_PROCESS_TIMEOUT,
        );
    }
}
