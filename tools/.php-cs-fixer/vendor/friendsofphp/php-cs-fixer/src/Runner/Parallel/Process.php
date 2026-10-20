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

use React\ChildProcess\Process as ReactProcess;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\WritableStreamInterface;

/**
 * Represents single process that is handled within parallel run.
 * Inspired by:
 *   - https://github.com/phpstan/phpstan-src/blob/9ce425bca5337039fb52c0acf96a20a2b8ace490/src/Parallel/Process.php
 *   - https://github.com/phpstan/phpstan-src/blob/1477e752b4b5893f323b6d2c43591e68b3d85003/src/Process/ProcessHelper.php.
 *
 * @author Greg Korba <greg@codito.dev>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Process
{
    // Properties required for process instantiation
    private string $command;
    private LoopInterface $loop;
    private int $timeoutSeconds;

    // Properties required for process execution
    private ?ReactProcess $process = null;
    private ?WritableStreamInterface $in = null;

    /** @var resource */
    private $stdErr;

    /** @var resource */
    private $stdOut;

    /** @var callable(array<array-key, mixed>): void */
    private $onData;

    /** @var callable(\Throwable): void */
    private $onError;

    private ?TimerInterface $timer = null;

    public function __construct(string $command, LoopInterface $loop, int $timeoutSeconds)
    {
        $this->command = $command;
        $this->loop = $loop;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * @param callable(array<array-key, mixed> $json): void  $onData  callback to be called when data is received from the parallelisation operator
     * @param callable(\Throwable $exception): void          $onError callback to be called when an exception occurs
     * @param callable(?int $exitCode, string $output): void $onExit  callback to be called when the process exits
     */
    public function start(callable $onData, callable $onError, callable $onExit): void
    {
        $sysTempDir = sys_get_temp_dir();
        if (!is_writable($sysTempDir)) {
            throw new ParallelisationException(\sprintf(
                'Failed creating temp file as sys_get_temp_dir="%s" is not writable.',
                $sysTempDir,
            ));
        }

        $stdOut = tmpfile();
        if (false === $stdOut) {
            throw new ParallelisationException('Failed creating temp file for stdOut.');
        }
        $this->stdOut = $stdOut;

        $stdErr = tmpfile();
        if (false === $stdErr) {
            throw new ParallelisationException('Failed creating temp file for stdErr.');
        }
        $this->stdErr = $stdErr;

        $this->onData = $onData;
        $this->onError = $onError;

        $this->process = new ReactProcess($this->command, null, null, [
            1 => $this->stdOut,
            2 => $this->stdErr,
        ]);
        $this->process->start($this->loop);
        $this->process->on('exit', function ($exitCode) use ($onExit): void {
            $this->cancelTimer();

            $output = '';
            rewind($this->stdOut);
            $stdOut = stream_get_contents($this->stdOut);
            if (\is_string($stdOut)) {
                $output .= $stdOut;
            }

            rewind($this->stdErr);
            $stdErr = stream_get_contents($this->stdErr);
            if (\is_string($stdErr)) {
                $output .= $stdErr;
            }

            $onExit($exitCode, $output);

            fclose($this->stdOut);
            fclose($this->stdErr);
        });
    }

    /**
     * Handles requests from parallelisation operator to its worker (spawned process).
     *
     * @param array<array-key, mixed> $data
     */
    public function request(array $data): void
    {
        $this->cancelTimer(); // Configured process timeout actually means "chunk timeout" (each request resets timer)

        if (null === $this->in) {
            throw new ParallelisationException(
                'Process not connected with parallelisation operator, ensure `bindConnection()` was called',
            );
        }

        $this->in->write($data);
        $this->timer = $this->loop->addTimer($this->timeoutSeconds, function (): void {
            ($this->onError)(
                new \Exception(
                    \sprintf(
                        'Child process timed out after %d seconds. Try making it longer using `ParallelConfig`.',
                        $this->timeoutSeconds,
                    ),
                )
            );
        });
    }

    public function quit(): void
    {
        $this->cancelTimer();
        if (null === $this->process || !$this->process->isRunning()) {
            return;
        }

        foreach ($this->process->pipes as $pipe) {
            $pipe->close();
        }

        if (null === $this->in) {
            return;
        }

        $this->in->end();
    }

    public function bindConnection(ReadableStreamInterface $out, WritableStreamInterface $in): void
    {
        $this->in = $in;

        $in->on('error', function (\Throwable $error): void {
            ($this->onError)($error);
        });

        $out->on('data', function (array $json): void {
            $this->cancelTimer();

            // Pass everything to the parallelisation operator, it should decide how to handle the data
            ($this->onData)($json);
        });
        $out->on('error', function (\Throwable $error): void {
            ($this->onError)($error);
        });
    }

    private function cancelTimer(): void
    {
        if (null === $this->timer) {
            return;
        }

        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
    }
}
