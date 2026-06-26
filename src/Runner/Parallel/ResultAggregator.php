<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function hash_equals;
use function property_exists;
use function sprintf;
use function strlen;
use function substr;
use function unserialize;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;
use stdClass;

/**
 * Collects the results that the workers produce and replays them into the
 * parent process' event subsystem.
 *
 * Workers finish their units out of order, but every printer, logger, and
 * report assumes the suite-ordered arrival it sees in sequential mode. The
 * aggregator therefore buffers each finished unit and forwards it only once all
 * of the units that precede it in suite order have been forwarded; the result
 * is an event stream that is byte-for-byte identical to the sequential one,
 * which keeps CI diffs stable.
 *
 * Forwarding a unit means replaying its collected event stream through the
 * parent dispatcher, importing the tests it recorded as passed, and merging its
 * code coverage. Because every user-facing output is a downstream subscriber of
 * these events, the parent's entire output, logging, and result subsystem works
 * unchanged.
 *
 * Some units do not run in a worker but in the main process (see
 * ParallelTestRunner): a unit attributed with #[DoNotRunInParallel], one that
 * needs process isolation, or one whose data cannot be serialized. Such a unit
 * is registered with its suite index and run, by the aggregator, at the moment
 * its index comes up in the release sequence. Running it there — between the
 * units that precede and follow it in suite order — lets its events reach the
 * dispatcher live and still in global suite order, so the ordering guarantee
 * holds for every output format, not just the ones that re-sort.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultAggregator
{
    private readonly Facade $eventFacade;
    private readonly Emitter $emitter;
    private readonly PassedTests $passedTests;
    private readonly CodeCoverage $codeCoverage;

    /**
     * @var array<non-negative-int, CompletedWorkUnit>
     */
    private array $buffer = [];

    /**
     * @var array<non-negative-int, callable():void>
     */
    private array $inProcessRunners = [];

    /**
     * @var non-negative-int
     */
    private int $nextIndex = 0;

    public function __construct(Facade $eventFacade, Emitter $emitter, PassedTests $passedTests, CodeCoverage $codeCoverage)
    {
        $this->eventFacade  = $eventFacade;
        $this->emitter      = $emitter;
        $this->passedTests  = $passedTests;
        $this->codeCoverage = $codeCoverage;
    }

    /**
     * Register a unit that is to be run, in the main process, at the point its
     * suite index comes up in the release sequence.
     *
     * @param non-negative-int $index
     * @param callable():void  $runner
     */
    public function registerInProcessUnit(int $index, callable $runner): void
    {
        $this->inProcessRunners[$index] = $runner;
    }

    /**
     * Accept a finished worker unit and release every unit that has now become
     * releasable in suite order.
     */
    public function add(CompletedWorkUnit $completed): void
    {
        $this->buffer[$completed->unit()->index()] = $completed;

        $this->release();
    }

    /**
     * Release every unit that is releasable in suite order, running registered
     * in-process units in place as their index comes up. Called both after a
     * worker finishes and directly by the runner, so that in-process units that
     * precede or follow all worker units are run even when no worker completion
     * drives the release.
     */
    public function flush(): void
    {
        $this->release();
    }

    private function release(): void
    {
        while (true) {
            if (isset($this->buffer[$this->nextIndex])) {
                $this->forward($this->buffer[$this->nextIndex]);

                unset($this->buffer[$this->nextIndex]);

                $this->nextIndex++;

                continue;
            }

            if (isset($this->inProcessRunners[$this->nextIndex])) {
                $runner = $this->inProcessRunners[$this->nextIndex];

                unset($this->inProcessRunners[$this->nextIndex]);

                $runner();

                $this->nextIndex++;

                continue;
            }

            break;
        }
    }

    private function forward(CompletedWorkUnit $completed): void
    {
        if ($completed->crashed()) {
            $message = $completed->message();

            if ($message === null || $message === '') {
                $message = sprintf(
                    'The worker process running %s ended unexpectedly',
                    $completed->unit()->name(),
                );
            }

            $this->emitter->childProcessErrored();
            $this->emitter->testRunnerTriggeredPhpunitWarning($message);

            return;
        }

        $serializedResult = $completed->serializedResult();
        $nonce            = $completed->nonce();

        if ($nonce !== null && $serializedResult !== '') {
            $nonceLength = strlen($nonce);

            if (strlen($serializedResult) < $nonceLength ||
                !hash_equals($nonce, substr($serializedResult, 0, $nonceLength))) {
                $this->emitter->childProcessErrored();
                $this->emitter->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'The result of the worker process running %s was tampered with or written by an unexpected process',
                        $completed->unit()->name(),
                    ),
                );

                return;
            }

            $serializedResult = substr($serializedResult, $nonceLength);
        }

        $childResult = @unserialize($serializedResult);

        if (!$childResult instanceof stdClass ||
            !property_exists($childResult, 'events') ||
            !property_exists($childResult, 'passedTests') ||
            !$childResult->events instanceof EventCollection ||
            !$childResult->passedTests instanceof PassedTests) {
            $this->emitter->childProcessErrored();
            $this->emitter->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'The worker process running %s ended unexpectedly',
                    $completed->unit()->name(),
                ),
            );

            return;
        }

        $this->eventFacade->forward($childResult->events);
        $this->passedTests->import($childResult->passedTests);

        if (!$this->codeCoverage->isActive()) {
            return;
        }

        // @codeCoverageIgnoreStart
        if (!isset($childResult->codeCoverage) || !$childResult->codeCoverage instanceof \SebastianBergmann\CodeCoverage\CodeCoverage) {
            return;
        }

        CodeCoverage::instance()->codeCoverage()->merge(
            $childResult->codeCoverage,
        );
        // @codeCoverageIgnoreEnd
    }
}
