<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

use function assert;
use function fclose;
use function feof;
use function fwrite;
use function is_resource;
use function proc_close;
use function proc_get_status;
use function proc_terminate;
use function rewind;
use function stream_get_contents;
use function stream_set_blocking;
use function unlink;

/**
 * Handle for a worker process that has been spawned by the JobRunner.
 *
 * Spawning (proc_open) and reaping (proc_close) a process are separated so
 * that a single thread of control can drive more than one process at a time:
 * a caller may start several jobs, multiplex their output streams with
 * stream_select(), feed each ready stream to consume(), and finally reap each
 * process with wait(). The synchronous JobRunner::run() is the degenerate case
 * of starting one job and immediately waiting for it.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RunningJob
{
    /**
     * @var resource
     */
    private $process;

    /**
     * @var ?resource
     */
    private $stdin;

    /**
     * @var ?resource
     */
    private $stdout;

    /**
     * @var ?resource
     */
    private $stderr;

    /**
     * @var ?resource
     */
    private $mergedOutputStream;
    private ?string $temporaryFile;
    private string $stdoutBuffer = '';
    private string $stderrBuffer = '';
    private ?Result $result      = null;

    /**
     * @param resource             $process
     * @param array<int, resource> $pipes
     * @param ?resource            $mergedOutputStream
     */
    public function __construct(mixed $process, array $pipes, mixed $mergedOutputStream, ?string $temporaryFile)
    {
        $this->process            = $process;
        $this->mergedOutputStream = $mergedOutputStream;
        $this->temporaryFile      = $temporaryFile;

        if (isset($pipes[0])) {
            $this->stdin = $pipes[0];
        }

        if (isset($pipes[1])) {
            $this->stdout = $pipes[1];
        }

        if (isset($pipes[2])) {
            $this->stderr = $pipes[2];
        }
    }

    /**
     * Write to the standard input of the worker process.
     */
    public function write(string $bytes): void
    {
        assert(is_resource($this->stdin));

        fwrite($this->stdin, $bytes);
    }

    /**
     * Signal end-of-input to the worker process by closing its standard input.
     */
    public function closeStdin(): void
    {
        if (is_resource($this->stdin)) {
            fclose($this->stdin);
        }

        $this->stdin = null;
    }

    /**
     * @return ?resource
     */
    public function stdout(): mixed
    {
        return $this->stdout;
    }

    /**
     * The output streams that are still open and can be passed to
     * stream_select() to wait for the worker process to produce output.
     *
     * @return list<resource>
     */
    public function readableStreams(): array
    {
        $streams = [];

        if (is_resource($this->stdout)) {
            $streams[] = $this->stdout;
        }

        if (is_resource($this->stderr)) {
            $streams[] = $this->stderr;
        }

        return $streams;
    }

    /**
     * Whether the worker process is still executing. Once it has terminated,
     * wait() can be called to reap it without blocking. Intended for callers
     * that cannot wait on the process' output streams — for instance because
     * its output is redirected to a file rather than a pipe — and therefore
     * poll its liveness instead.
     */
    public function isRunning(): bool
    {
        if ($this->result !== null) {
            return false;
        }

        return proc_get_status($this->process)['running'];
    }

    /**
     * Read whatever output is currently available without blocking. Intended
     * to be called after stream_select() has reported one of the streams
     * returned by readableStreams() as ready.
     */
    public function consume(): void
    {
        $this->stdoutBuffer .= $this->readAvailable($this->stdout);
        $this->stderrBuffer .= $this->readAvailable($this->stderr);
    }

    /**
     * Terminate the worker process instead of waiting for it to finish, then
     * reap it. Used when the test runner stops early and abandons the work
     * the process is doing.
     */
    public function terminate(): void
    {
        if ($this->result !== null) {
            return;
        }

        $this->closeStdin();

        proc_terminate($this->process);

        $this->wait();
    }

    /**
     * Block until the worker process has terminated, then reap it and return
     * its accumulated output.
     */
    public function wait(): Result
    {
        if ($this->result !== null) {
            return $this->result;
        }

        $this->stdoutBuffer .= $this->drain($this->stdout);
        $this->stderrBuffer .= $this->drain($this->stderr);

        $this->stdout = null;
        $this->stderr = null;

        proc_close($this->process);

        if ($this->mergedOutputStream !== null) {
            rewind($this->mergedOutputStream);

            $merged = stream_get_contents($this->mergedOutputStream);

            fclose($this->mergedOutputStream);

            assert($merged !== false);

            $this->stdoutBuffer = $merged;
            $this->stderrBuffer = '';
        }

        if ($this->temporaryFile !== null) {
            unlink($this->temporaryFile);
        }

        $this->result = new Result($this->stdoutBuffer, $this->stderrBuffer);

        return $this->result;
    }

    /**
     * @param ?resource $stream
     */
    private function readAvailable(mixed &$stream): string
    {
        if (!is_resource($stream)) {
            return '';
        }

        stream_set_blocking($stream, false);

        $buffer = stream_get_contents($stream);

        assert($buffer !== false);

        if (feof($stream)) {
            fclose($stream);

            $stream = null;
        }

        return $buffer;
    }

    /**
     * @param ?resource $stream
     */
    private function drain(mixed $stream): string
    {
        if (!is_resource($stream)) {
            return '';
        }

        stream_set_blocking($stream, true);

        $buffer = stream_get_contents($stream);

        fclose($stream);

        assert($buffer !== false);

        return $buffer;
    }
}
