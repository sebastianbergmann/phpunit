<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fidry\CpuCoreCounter;

/**
 * @readonly
 */
final class ParallelisationResult
{
    /**
     * @var positive-int|0
     */
    public $passedReservedCpus;

    /**
     * @var non-zero-int|null
     */
    public $passedCountLimit;

    /**
     * @var float|null
     */
    public $passedLoadLimit;

    /**
     * @var float|null
     */
    public $passedSystemLoadAverage;

    /**
     * @var non-zero-int|null
     */
    public $correctedCountLimit;

    /**
     * @var float|null
     */
    public $correctedSystemLoadAverage;

    /**
     * @var positive-int
     */
    public $totalCoresCount;

    /**
     * @var positive-int
     */
    public $availableCpus;

    /**
     * @param positive-int|0    $passedReservedCpus
     * @param non-zero-int|null $passedCountLimit
     * @param non-zero-int|null $correctedCountLimit
     * @param positive-int      $totalCoresCount
     * @param positive-int      $availableCpus
     */
    public function __construct(
        int $passedReservedCpus,
        ?int $passedCountLimit,
        ?float $passedLoadLimit,
        ?float $passedSystemLoadAverage,
        ?int $correctedCountLimit,
        ?float $correctedSystemLoadAverage,
        int $totalCoresCount,
        int $availableCpus
    ) {
        $this->passedReservedCpus = $passedReservedCpus;
        $this->passedCountLimit = $passedCountLimit;
        $this->passedLoadLimit = $passedLoadLimit;
        $this->passedSystemLoadAverage = $passedSystemLoadAverage;
        $this->correctedCountLimit = $correctedCountLimit;
        $this->correctedSystemLoadAverage = $correctedSystemLoadAverage;
        $this->totalCoresCount = $totalCoresCount;
        $this->availableCpus = $availableCpus;
    }
}
