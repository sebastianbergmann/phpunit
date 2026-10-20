<?php

namespace React\ChildProcess;

use Evenement\EventEmitter;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableResourceStream;
use React\Stream\ReadableStreamInterface;
use React\Stream\WritableResourceStream;
use React\Stream\WritableStreamInterface;
use React\Stream\DuplexResourceStream;
use React\Stream\DuplexStreamInterface;

/**
 * Process component.
 *
 * This class borrows logic from Symfony's Process component for ensuring
 * compatibility when PHP is compiled with the --enable-sigchild option.
 *
 * This class also implements the `EventEmitterInterface`
 * which allows you to react to certain events:
 *
 * exit event:
 *     The `exit` event will be emitted whenever the process is no longer running.
 *     Event listeners will receive the exit code and termination signal as two
 *     arguments:
 *
 *     ```php
 *     $process = new Process('sleep 10');
 *     $process->start();
 *
 *     $process->on('exit', function ($code, $term) {
 *         if ($term === null) {
 *             echo 'exit with code ' . $code . PHP_EOL;
 *         } else {
 *             echo 'terminated with signal ' . $term . PHP_EOL;
 *         }
 *     });
 *     ```
 *
 *     Note that `$code` is `null` if the process has terminated, but the exit
 *     code could not be determined (for example
 *     [sigchild compatibility](#sigchild-compatibility) was disabled).
 *     Similarly, `$term` is `null` unless the process has terminated in response to
 *     an uncaught signal sent to it.
 *     This is not a limitation of this project, but actual how exit codes and signals
 *     are exposed on POSIX systems, for more details see also
 *     [here](https://unix.stackexchange.com/questions/99112/default-exit-code-when-process-is-terminated).
 *
 *     It's also worth noting that process termination depends on all file descriptors
 *     being closed beforehand.
 *     This means that all [process pipes](#stream-properties) will emit a `close`
 *     event before the `exit` event and that no more `data` events will arrive after
 *     the `exit` event.
 *     Accordingly, if either of these pipes is in a paused state (`pause()` method
 *     or internally due to a `pipe()` call), this detection may not trigger.
 */
class Process extends EventEmitter
{
    /**
     * @var WritableStreamInterface|null|DuplexStreamInterface|ReadableStreamInterface
     */
    public $stdin;

    /**
     * @var ReadableStreamInterface|null|DuplexStreamInterface|WritableStreamInterface
     */
    public $stdout;

    /**
     * @var ReadableStreamInterface|null|DuplexStreamInterface|WritableStreamInterface
     */
    public $stderr;

    /**
     * Array with all process pipes (once started)
     *
     * Unless explicitly configured otherwise during construction, the following
     * standard I/O pipes will be assigned by default:
     * - 0: STDIN (`WritableStreamInterface`)
     * - 1: STDOUT (`ReadableStreamInterface`)
     * - 2: STDERR (`ReadableStreamInterface`)
     *
     * @var array<ReadableStreamInterface|WritableStreamInterface|DuplexStreamInterface>
     */
    public $pipes = array();

    private $cmd;
    private $cwd;
    private $env;
    private $fds;

    private $enhanceSigchildCompatibility;
    private $sigchildPipe;

    private $process;
    private $status;
    private $exitCode;
    private $fallbackExitCode;
    private $stopSignal;
    private $termSignal;

    private static $sigchild;

    /**
    * Constructor.
    *
    * @param string $cmd      Command line to run
    * @param null|string $cwd Current working directory or null to inherit
    * @param null|array  $env Environment variables or null to inherit
    * @param null|array  $fds File descriptors to allocate for this process (or null = default STDIO streams)
    * @throws \LogicException On windows or when proc_open() is not installed
    */
    public function __construct($cmd, $cwd = null, $env = null, $fds = null)
    {
        if ($env !== null && !\is_array($env)) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #3 ($env) expected null|array');
        }
        if ($fds !== null && !\is_array($fds)) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #4 ($fds) expected null|array');
        }
        if (!\function_exists('proc_open')) {
            throw new \LogicException('The Process class relies on proc_open(), which is not available on your PHP installation.');
        }

        $this->cmd = $cmd;
        $this->cwd = $cwd;

        if (null !== $env) {
            $this->env = array();
            foreach ($env as $key => $value) {
                $this->env[(string) $key] = (string) $value;
            }
        }

        if ($fds === null) {
            $fds = array(
                array('pipe', 'r'), // stdin
                array('pipe', 'w'), // stdout
                array('pipe', 'w'), // stderr
            );
        }

        if (\DIRECTORY_SEPARATOR === '\\') {
            foreach ($fds as $fd) {
                if (isset($fd[0]) && $fd[0] === 'pipe') {
                    throw new \LogicException('Process pipes are not supported on Windows due to their blocking nature on Windows');
                }
            }
        }

        $this->fds = $fds;
        $this->enhanceSigchildCompatibility = self::isSigchildEnabled();
    }

    /**
     * Start the process.
     *
     * After the process is started, the standard I/O streams will be constructed
     * and available via public properties.
     *
     * This method takes an optional `LoopInterface|null $loop` parameter that can be used to
     * pass the event loop instance to use for this process. You can use a `null` value
     * here in order to use the [default loop](https://github.com/reactphp/event-loop#loop).
     * This value SHOULD NOT be given unless you're sure you want to explicitly use a
     * given event loop instance.
     *
     * @param ?LoopInterface $loop        Loop interface for stream construction
     * @param float          $interval    Interval to periodically monitor process state (seconds)
     * @throws \RuntimeException If the process is already running or fails to start
     */
    public function start($loop = null, $interval = 0.1)
    {
        if ($loop !== null && !$loop instanceof LoopInterface) { // manual type check to support legacy PHP < 7.1
            throw new \InvalidArgumentException('Argument #1 ($loop) expected null|React\EventLoop\LoopInterface');
        }
        if ($this->isRunning()) {
            throw new \RuntimeException('Process is already running');
        }

        $loop = $loop ?: Loop::get();
        $cmd = $this->cmd;
        $fdSpec = $this->fds;
        $sigchild = null;

        // Read exit code through fourth pipe to work around --enable-sigchild
        if ($this->enhanceSigchildCompatibility) {
            $fdSpec[] = array('pipe', 'w');
            \end($fdSpec);
            $sigchild = \key($fdSpec);

            // make sure this is fourth or higher (do not mess with STDIO)
            if ($sigchild < 3) {
                $fdSpec[3] = $fdSpec[$sigchild];
                unset($fdSpec[$sigchild]);
                $sigchild = 3;
            }

            $cmd = \sprintf('(%s) ' . $sigchild . '>/dev/null; code=$?; echo $code >&' . $sigchild . '; exit $code', $cmd);
        }

        // on Windows, we do not launch the given command line in a shell (cmd.exe) by default and omit any error dialogs
        // the cmd.exe shell can explicitly be given as part of the command as detailed in both documentation and tests
        $options = array();
        if (\DIRECTORY_SEPARATOR === '\\') {
            $options['bypass_shell'] = true;
            $options['suppress_errors'] = true;
        }

        $errstr = '';
        \set_error_handler(function ($_, $error) use (&$errstr) {
            // Match errstr from PHP's warning message.
            // proc_open(/dev/does-not-exist): Failed to open stream: No such file or directory
            $errstr = $error;
        });

        $pipes = array();
        $this->process = @\proc_open($cmd, $fdSpec, $pipes, $this->cwd, $this->env, $options);

        \restore_error_handler();

        if (!\is_resource($this->process)) {
            throw new \RuntimeException('Unable to launch a new process: ' . $errstr);
        }

        // count open process pipes and await close event for each to drain buffers before detecting exit
        $that = $this;
        $closeCount = 0;
        $streamCloseHandler = function () use (&$closeCount, $loop, $interval, $that) {
            $closeCount--;

            if ($closeCount > 0) {
                return;
            }

            // process already closed => report immediately
            if (!$that->isRunning()) {
                $that->close();
                $that->emit('exit', array($that->getExitCode(), $that->getTermSignal()));
                return;
            }

            // close not detected immediately => check regularly
            $loop->addPeriodicTimer($interval, function ($timer) use ($that, $loop) {
                if (!$that->isRunning()) {
                    $loop->cancelTimer($timer);
                    $that->close();
                    $that->emit('exit', array($that->getExitCode(), $that->getTermSignal()));
                }
            });
        };

        if ($sigchild !== null) {
            $this->sigchildPipe = $pipes[$sigchild];
            unset($pipes[$sigchild]);
        }

        foreach ($pipes as $n => $fd) {
            // use open mode from stream meta data or fall back to pipe open mode for legacy HHVM
            $meta = \stream_get_meta_data($fd);
            $mode = $meta['mode'] === '' ? ($this->fds[$n][1] === 'r' ? 'w' : 'r') : $meta['mode'];

            if ($mode === 'r+') {
                $stream = new DuplexResourceStream($fd, $loop);
                $stream->on('close', $streamCloseHandler);
                $closeCount++;
            } elseif ($mode === 'w') {
                $stream = new WritableResourceStream($fd, $loop);
            } else {
                $stream = new ReadableResourceStream($fd, $loop);
                $stream->on('close', $streamCloseHandler);
                $closeCount++;
            }
            $this->pipes[$n] = $stream;
        }

        $this->stdin  = isset($this->pipes[0]) ? $this->pipes[0] : null;
        $this->stdout = isset($this->pipes[1]) ? $this->pipes[1] : null;
        $this->stderr = isset($this->pipes[2]) ? $this->pipes[2] : null;

        // immediately start checking for process exit when started without any I/O pipes
        if (!$closeCount) {
            $streamCloseHandler();
        }
    }

    /**
     * Close the process.
     *
     * This method should only be invoked via the periodic timer that monitors
     * the process state.
     */
    public function close()
    {
        if ($this->process === null) {
            return;
        }

        foreach ($this->pipes as $pipe) {
            $pipe->close();
        }

        if ($this->enhanceSigchildCompatibility) {
            $this->pollExitCodePipe();
            $this->closeExitCodePipe();
        }

        $exitCode = \proc_close($this->process);
        $this->process = null;

        if ($this->exitCode === null && $exitCode !== -1) {
            $this->exitCode = $exitCode;
        }

        if ($this->exitCode === null && $this->status['exitcode'] !== -1) {
            $this->exitCode = $this->status['exitcode'];
        }

        if ($this->exitCode === null && $this->fallbackExitCode !== null) {
            $this->exitCode = $this->fallbackExitCode;
            $this->fallbackExitCode = null;
        }
    }

    /**
     * Terminate the process with an optional signal.
     *
     * @param int $signal Optional signal (default: SIGTERM)
     * @return bool Whether the signal was sent successfully
     */
    public function terminate($signal = null)
    {
        if ($this->process === null) {
            return false;
        }

        if ($signal !== null) {
            return \proc_terminate($this->process, $signal);
        }

        return \proc_terminate($this->process);
    }

    /**
     * Get the command string used to launch the process.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->cmd;
    }

    /**
     * Get the exit code returned by the process.
     *
     * This value is only meaningful if isRunning() has returned false. Null
     * will be returned if the process is still running.
     *
     * Null may also be returned if the process has terminated, but the exit
     * code could not be determined (e.g. sigchild compatibility was disabled).
     *
     * @return int|null
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * Get the process ID.
     *
     * @return int|null
     */
    public function getPid()
    {
        $status = $this->getCachedStatus();

        return $status !== null ? $status['pid'] : null;
    }

    /**
     * Get the signal that caused the process to stop its execution.
     *
     * This value is only meaningful if isStopped() has returned true. Null will
     * be returned if the process was never stopped.
     *
     * @return int|null
     */
    public function getStopSignal()
    {
        return $this->stopSignal;
    }

    /**
     * Get the signal that caused the process to terminate its execution.
     *
     * This value is only meaningful if isTerminated() has returned true. Null
     * will be returned if the process was never terminated.
     *
     * @return int|null
     */
    public function getTermSignal()
    {
        return $this->termSignal;
    }

    /**
     * Return whether the process is still running.
     *
     * @return bool
     */
    public function isRunning()
    {
        if ($this->process === null) {
            return false;
        }

        $status = $this->getFreshStatus();

        return $status !== null ? $status['running'] : false;
    }

    /**
     * Return whether the process has been stopped by a signal.
     *
     * @return bool
     */
    public function isStopped()
    {
        $status = $this->getFreshStatus();

        return $status !== null ? $status['stopped'] : false;
    }

    /**
     * Return whether the process has been terminated by an uncaught signal.
     *
     * @return bool
     */
    public function isTerminated()
    {
        $status = $this->getFreshStatus();

        return $status !== null ? $status['signaled'] : false;
    }

    /**
     * Return whether PHP has been compiled with the '--enable-sigchild' option.
     *
     * @see \Symfony\Component\Process\Process::isSigchildEnabled()
     * @return bool
     */
    public final static function isSigchildEnabled()
    {
        if (null !== self::$sigchild) {
            return self::$sigchild;
        }

        if (!\function_exists('phpinfo')) {
            return self::$sigchild = false; // @codeCoverageIgnore
        }

        \ob_start();
        \phpinfo(INFO_GENERAL);

        return self::$sigchild = false !== \strpos(\ob_get_clean(), '--enable-sigchild');
    }

    /**
     * Enable or disable sigchild compatibility mode.
     *
     * Sigchild compatibility mode is required to get the exit code and
     * determine the success of a process when PHP has been compiled with
     * the --enable-sigchild option.
     *
     * @param bool $sigchild
     * @return void
     */
    public final static function setSigchildEnabled($sigchild)
    {
        self::$sigchild = (bool) $sigchild;
    }

    /**
     * Check the fourth pipe for an exit code.
     *
     * This should only be used if --enable-sigchild compatibility was enabled.
     */
    private function pollExitCodePipe()
    {
        if ($this->sigchildPipe === null) {
            return;
        }

        $r = array($this->sigchildPipe);
        $w = $e = null;

        $n = @\stream_select($r, $w, $e, 0);

        if (1 !== $n) {
            return;
        }

        $data = \fread($r[0], 8192);

        if (\strlen($data) > 0) {
            $this->fallbackExitCode = (int) $data;
        }
    }

    /**
     * Close the fourth pipe used to relay an exit code.
     *
     * This should only be used if --enable-sigchild compatibility was enabled.
     */
    private function closeExitCodePipe()
    {
        if ($this->sigchildPipe === null) {
            return;
        }

        \fclose($this->sigchildPipe);
        $this->sigchildPipe = null;
    }

    /**
     * Return the cached process status.
     *
     * @return array
     */
    private function getCachedStatus()
    {
        if ($this->status === null) {
            $this->updateStatus();
        }

        return $this->status;
    }

    /**
     * Return the updated process status.
     *
     * @return array
     */
    private function getFreshStatus()
    {
        $this->updateStatus();

        return $this->status;
    }

    /**
     * Update the process status, stop/term signals, and exit code.
     *
     * Stop/term signals are only updated if the process is currently stopped or
     * signaled, respectively. Otherwise, signal values will remain as-is so the
     * corresponding getter methods may be used at a later point in time.
     */
    private function updateStatus()
    {
        if ($this->process === null) {
            return;
        }

        $this->status = \proc_get_status($this->process);

        if ($this->status === false) {
            throw new \UnexpectedValueException('proc_get_status() failed');
        }

        if ($this->status['stopped']) {
            $this->stopSignal = $this->status['stopsig'];
        }

        if ($this->status['signaled']) {
            $this->termSignal = $this->status['termsig'];
        }

        if (!$this->status['running'] && -1 !== $this->status['exitcode']) {
            $this->exitCode = $this->status['exitcode'];
        }
    }
}
