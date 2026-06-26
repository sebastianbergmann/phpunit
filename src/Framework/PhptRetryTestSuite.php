<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PhptRetryTestSuite extends PhptIterativeTestSuite
{
    /**
     * @var positive-int
     */
    private int $maxAttempts = 1;

    /**
     * @param non-empty-string $filename
     * @param positive-int     $maxAttempts
     */
    public static function for(string $filename, int $maxAttempts): self
    {
        $suite = self::empty($filename);

        $suite->filename    = $filename;
        $suite->maxAttempts = $maxAttempts;

        $suite->addTest(new PhptTestCase($filename, 1, 1, 1, $maxAttempts));

        return $suite;
    }

    /**
     * @return positive-int
     */
    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * @param list<Test> $tests
     */
    protected function execute(array $tests, Event\Emitter $emitter): void
    {
        $facade = EventFacade::instance();

        for ($attempt = 1; $attempt <= $this->maxAttempts; $attempt++) {
            if (TestResultFacade::shouldStop()) {
                $emitter->testRunnerExecutionAborted();

                return;
            }

            $test = new PhptTestCase($this->filename, 1, 1, $attempt, $this->maxAttempts);

            $facade->startCollectingEvents();

            $test->run();

            $events = $facade->stopCollectingEvents();

            $retryable = $this->failedOrErrored($events) && $attempt < $this->maxAttempts;

            if (!$retryable) {
                $facade->forward($events);

                return;
            }

            if (!$this->emitAttemptEvent($events, $emitter)) {
                // the attempt's outcome could not be determined from its events,
                // forward them unchanged rather than discard information
                $facade->forward($events);

                return;
            }
        }
    }

    private function emitAttemptEvent(EventCollection $events, Event\Emitter $emitter): bool
    {
        $duration = $this->durationOf($events);

        foreach ($events as $event) {
            if ($event instanceof Event\Test\Failed) {
                $comparisonFailure = null;

                if ($event->hasComparisonFailure()) {
                    $comparisonFailure = $event->comparisonFailure();
                }

                $emitter->testAttemptFailed(
                    $event->test(),
                    $event->throwable(),
                    $comparisonFailure,
                    $duration,
                );

                return true;
            }

            if ($event instanceof Event\Test\Errored) {
                $emitter->testAttemptErrored(
                    $event->test(),
                    $event->throwable(),
                    $duration,
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Determine the wall-clock time spent on an attempt from its collected
     * events. This mirrors the way duration-aware loggers measure the time
     * of a test, namely from its Prepared event to its Finished event, so
     * that the durations of retried attempts can be accounted for even though
     * the attempt's events themselves are not forwarded.
     */
    private function durationOf(EventCollection $events): Event\Telemetry\Duration
    {
        $start = null;
        $end   = null;

        foreach ($events as $event) {
            if ($event instanceof Event\Test\Prepared) {
                $start = $event->telemetryInfo()->time();
            }

            if ($event instanceof Event\Test\Finished) {
                $end = $event->telemetryInfo()->time();
            }
        }

        if ($start === null || $end === null) {
            return Event\Telemetry\Duration::fromSecondsAndNanoseconds(0, 0);
        }

        return $end->duration($start);
    }
}
