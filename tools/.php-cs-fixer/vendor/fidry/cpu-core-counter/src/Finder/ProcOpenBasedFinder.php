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

namespace Fidry\CpuCoreCounter\Finder;

use Fidry\CpuCoreCounter\Executor\ProcessExecutor;
use Fidry\CpuCoreCounter\Executor\ProcOpenExecutor;
use function explode;
use function filter_var;
use function is_int;
use function method_exists;
use function sprintf;
use function trim;
use const FILTER_VALIDATE_INT;
use const PHP_EOL;

abstract class ProcOpenBasedFinder implements CpuCoreFinder
{
    /**
     * @var ProcessExecutor
     */
    private $executor;

    public function __construct(?ProcessExecutor $executor = null)
    {
        $this->executor = $executor ?? new ProcOpenExecutor();
    }

    public function diagnose(): string
    {
        // Keep this check until getUnavailabilityReason() is declared in
        // ProcessExecutor: implementations written before it may not have it.
        if (method_exists($this->executor, 'getUnavailabilityReason')) {
            $unavailabilityReason = $this->executor->getUnavailabilityReason();

            if (null !== $unavailabilityReason) {
                return $unavailabilityReason;
            }
        }

        $command = $this->getCommand();
        $output = $this->executor->execute($command);

        if (null === $output) {
            return sprintf(
                'Failed to execute the command "%s".',
                $command
            );
        }

        [$stdout, $stderr] = $output;
        $failed = '' !== trim($stderr);

        return $failed
            ? sprintf(
                'Executed the command "%s" which wrote the following output to the STDERR:%s%s%sWill return "null".',
                $command,
                PHP_EOL,
                $stderr,
                PHP_EOL
            )
            : sprintf(
                'Executed the command "%s" and got the following (STDOUT) output:%s%s%sWill return "%s".',
                $command,
                PHP_EOL,
                $stdout,
                PHP_EOL,
                $this->countCpuCores($stdout) ?? 'null'
            );
    }

    /**
     * @return positive-int|null
     */
    public function find(): ?int
    {
        $output = $this->executor->execute($this->getCommand());

        if (null === $output) {
            return null;
        }

        [$stdout, $stderr] = $output;
        $failed = '' !== trim($stderr);

        return $failed
            ? null
            : $this->countCpuCores($stdout);
    }

    /**
     * @internal
     *
     * @return positive-int|null
     */
    protected function countCpuCores(string $process): ?int
    {
        $cpuCount = filter_var($process, FILTER_VALIDATE_INT);

        return is_int($cpuCount) && $cpuCount > 0 ? $cpuCount : null;
    }

    /**
     * Sums the lines that are a number, e.g. for commands that output one row
     * per CPU socket. Other lines, such as headers, are ignored.
     *
     * @internal
     *
     * @return positive-int|null
     */
    protected function sumCpuCoresPerLine(string $process): ?int
    {
        $cpuCount = 0;

        foreach (explode("\n", $process) as $line) {
            $lineCpuCount = filter_var($line, FILTER_VALIDATE_INT);

            if (is_int($lineCpuCount) && $lineCpuCount > 0) {
                $cpuCount += $lineCpuCount;
            }
        }

        return $cpuCount > 0 ? $cpuCount : null;
    }

    abstract protected function getCommand(): string;
}
