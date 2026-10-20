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

namespace Fidry\CpuCoreCounter\Executor;

use function fclose;
use function function_exists;
use function is_resource;
use function proc_close;
use function proc_open;
use function rewind;
use function sprintf;
use function stream_get_contents;
use function tmpfile;

final class ProcOpenExecutor implements ProcessExecutor
{
    private const REQUIRED_FUNCTIONS = [
        'proc_open',
        'proc_close',
        'tmpfile',
        'fclose',
        'rewind',
        'stream_get_contents',
    ];

    public function getUnavailabilityReason(): ?string
    {
        // Any of them may be disabled, e.g. with disable_functions.
        foreach (self::REQUIRED_FUNCTIONS as $function) {
            if (!function_exists($function)) {
                return sprintf(
                    'The function "%s" is not available.',
                    $function
                );
            }
        }

        return null;
    }

    public function execute(string $command): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        // Do not use a pipe for the STDERR: reading the STDOUT to the end
        // first would block forever if the command fills the STDERR pipe.
        $stderrFile = @tmpfile();

        if (false === $stderrFile) {
            return null;
        }

        $pipes = [];

        $process = @proc_open(
            $command,
            [
                ['pipe', 'rb'],
                ['pipe', 'wb'], // stdout
                $stderrFile,
            ],
            $pipes
        );
        // https://github.com/phpstan/phpstan/issues/13197
        /** @var array{resource, resource} $pipes */
        if (!is_resource($process)) {
            fclose($stderrFile);

            return null;
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);

        proc_close($process);

        rewind($stderrFile);
        $stderr = stream_get_contents($stderrFile);

        fclose($stderrFile);

        if (false === $stdout || false === $stderr) {
            return null;
        }

        return [$stdout, $stderr];
    }

    private function isAvailable(): bool
    {
        return null === $this->getUnavailabilityReason();
    }
}
