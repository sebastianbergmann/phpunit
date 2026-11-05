<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TimeLimit;

use const SIGALRM;
use function assert;
use function ceil;
use function function_exists;
use function getmypid;
use function hrtime;
use function pcntl_alarm;
use function pcntl_async_signals;
use function pcntl_signal;
use function sprintf;
use PHPUnit\Event\Facade;

/**
 * Enforces the wall-clock time limit for the entire test run.
 *
 * The time limit is checked whenever a test has finished. When the pcntl
 * extension is available, a SIGALRM-based hard stop is additionally armed
 * for each test that runs in the same process as the test runner, so that
 * a test that hangs cannot keep the test run from ending.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TimeLimitHandler
{
    private static ?self $instance = null;
    private readonly Facade $facade;

    /**
     * @var positive-int
     */
    private readonly int $timeLimit;
    private readonly int $deadline;
    private readonly false|int $pid;
    private readonly bool $canUseAlarm;
    private bool $alarmFired = false;
    private bool $exceeded   = false;

    /**
     * @param positive-int $timeLimit
     */
    public static function init(Facade $facade, int $timeLimit): void
    {
        self::$instance = new self($facade, $timeLimit);
    }

    /**
     * Arms the hard stop for a test that is about to run in the same process
     * as the test runner. This is a no-op when no time limit is configured or
     * when the pcntl extension is not available.
     */
    public static function armAlarm(): void
    {
        self::$instance?->arm();
    }

    /**
     * Disarms the hard stop after a test that ran in the same process as the
     * test runner has finished.
     */
    public static function disarmAlarm(): void
    {
        self::$instance?->disarm();
    }

    /**
     * Whether a time limit is configured for the test run and its deadline
     * has passed.
     *
     * The parallel test runner checks this while it waits for its worker
     * processes, rather than only when a test finishes: the tests that a
     * worker process runs are not stopped by the alarm of this process, and
     * their results arrive in suite order, if at all.
     */
    public static function deadlineHasPassed(): bool
    {
        if (self::$instance === null) {
            return false;
        }

        return hrtime(true) >= self::$instance->deadline;
    }

    /**
     * Emits the event that tells the test runner that the time limit for the
     * test run was exceeded, unless it has already been emitted.
     */
    public static function timeLimitExceeded(): void
    {
        self::$instance?->exceed();
    }

    /**
     * The message that a test is aborted with when the time limit for the
     * test run is exceeded while the test runs.
     */
    public static function messageForAbortedTest(): string
    {
        assert(self::$instance !== null);

        return self::$instance->abortedTestMessage();
    }

    /**
     * @param positive-int $timeLimit
     */
    private function __construct(Facade $facade, int $timeLimit)
    {
        $this->facade      = $facade;
        $this->timeLimit   = $timeLimit;
        $this->deadline    = hrtime(true) + ($timeLimit * 1_000_000_000);
        $this->pid         = getmypid();
        $this->canUseAlarm = function_exists('pcntl_alarm') && function_exists('pcntl_signal') && function_exists('pcntl_async_signals');

        $this->facade->registerSubscribers(
            new TestFinishedSubscriber($this),
        );
    }

    public function testFinished(): void
    {
        if ($this->exceeded) {
            return;
        }

        if (!$this->alarmFired && hrtime(true) < $this->deadline) {
            return;
        }

        $this->exceed();
    }

    private function exceed(): void
    {
        if ($this->exceeded) {
            return;
        }

        $this->exceeded = true;

        $this->facade->emitter()->testRunnerTimeLimitExceeded($this->timeLimit);
    }

    private function abortedTestMessage(): string
    {
        $unit = 'seconds';

        if ($this->timeLimit === 1) {
            $unit = 'second';
        }

        return sprintf(
            'This test was aborted because the time limit of %d %s for the test run was exceeded',
            $this->timeLimit,
            $unit,
        );
    }

    private function arm(): void
    {
        if (!$this->canUseAlarm) {
            return;
        }

        // The per-test time limit that is enforced using --enforce-time-limit
        // replaces the SIGALRM handler and the alarm, which is why both have to
        // be set up again for each test
        // @codeCoverageIgnoreStart
        pcntl_signal(
            SIGALRM,
            function (): void
            {
                if (getmypid() !== $this->pid) {
                    return;
                }

                $this->alarmFired = true;

                throw new TimeLimitExceededException($this->abortedTestMessage());
            },
        );

        pcntl_async_signals(true);
        pcntl_alarm($this->secondsUntilDeadline());
        // @codeCoverageIgnoreEnd
    }

    private function disarm(): void
    {
        if (!$this->canUseAlarm) {
            return;
        }

        // @codeCoverageIgnoreStart
        pcntl_alarm(0);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return positive-int
     */
    private function secondsUntilDeadline(): int
    {
        $remaining = $this->deadline - hrtime(true);

        if ($remaining <= 0) {
            return 1;
        }

        $seconds = (int) ceil($remaining / 1_000_000_000);

        if ($seconds < 1) {
            return 1;
        }

        return $seconds;
    }
}
