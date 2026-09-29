<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactAnalysis;

use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * Reports what it is told was executed while it ran, and nothing that it is
 * told was executed while it did not run.
 */
final class DriverThatReportsWhatItIsTold extends Driver
{
    private bool $running = false;

    /**
     * @var array<string, array<int, int>>
     */
    private array $executed = [];

    public function name(): string
    {
        return 'DriverThatReportsWhatItIsTold';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function start(): void
    {
        $this->running = true;
    }

    public function stop(): RawCodeCoverageData
    {
        $executed = $this->executed;

        $this->running  = false;
        $this->executed = [];

        return RawCodeCoverageData::fromLineCoverage($executed);
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function execute(string $file, int $line): void
    {
        if (!$this->running) {
            return;
        }

        $this->executed[$file][$line] = Driver::LINE_EXECUTED;
    }
}
