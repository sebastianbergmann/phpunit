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

use Generator;
use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\EventCollector;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\Phpt\Interruption;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunnerRegistry;
use PHPUnit\Util\PHP\Result;
use Throwable;

/**
 * A test suite that aggregates the repetitions or attempts of a single PHPT
 * test. Unlike the repetitions and attempts of a test method, a PHPT test does
 * not track its own status, so the outcome of each run is determined from the
 * events it emits.
 *
 * Each run of the test is a generator of the child process jobs its sections
 * need, so the suite's orchestration is a generator as well: it advances one
 * run at a time and examines the run's events before deciding on the next one.
 * A sequential run drives that generator to completion here, one job after
 * another; a parallel run drives it through the PHPT runner, which interleaves
 * it with the other tests it advances. Both therefore make the same decisions
 * about which run happens and which of its events are reported.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class PhptIterativeTestSuite extends IterativeTestSuite
{
    /**
     * The suite as one generator of the jobs its runs need, for a caller that
     * advances several tests side by side: the events the runs emit, and the
     * suite envelope around them, go into the collector the caller reads them
     * back from once the generator has finished.
     *
     * @throws Event\RuntimeException
     *
     * @return Generator<int, Job, Result, void>
     */
    final public function executeInterleaved(Event\Emitter $emitter, EventCollector $collector, ?Interruption $interruption = null): Generator
    {
        $testSuiteValueObjectForEvents = Event\TestSuite\TestSuiteBuilder::from($this);

        $emitter->testSuiteStarted($testSuiteValueObjectForEvents);

        yield from $this->iterate($this->tests(), $emitter, $collector, $interruption);

        $emitter->testSuiteFinished($testSuiteValueObjectForEvents);
    }

    /**
     * @param list<Test> $tests
     *
     * @throws Event\RuntimeException
     */
    final protected function execute(array $tests, Event\Emitter $emitter): void
    {
        $generator = $this->iterate($tests, $emitter, EventFacade::instance());

        $generator->rewind();

        while ($generator->valid()) {
            $generator->send(JobRunnerRegistry::run($generator->current()));
        }
    }

    /**
     * @param list<Test> $tests
     *
     * @throws Event\RuntimeException
     *
     * @return Generator<int, Job, Result, void>
     */
    abstract protected function iterate(array $tests, Event\Emitter $emitter, EventCollector $collector, ?Interruption $interruption = null): Generator;

    /**
     * Run a single repetition or attempt with its events collected instead of
     * processed, and return them.
     *
     * The collection is stopped even when running the test throws, because a
     * destination that is left collecting events swallows every event that is
     * emitted afterwards. The events collected before the throwable was raised
     * are forwarded rather than discarded.
     *
     * @throws Throwable
     *
     * @return Generator<int, Job, Result, EventCollection>
     */
    final protected function executeCollectingEvents(PhptTestCase $test, Event\Emitter $emitter, EventCollector $collector, ?Interruption $interruption): Generator
    {
        $collector->startCollectingEvents();

        try {
            yield from $test->execute($emitter, $interruption);
            // @codeCoverageIgnoreStart
        } catch (Throwable $t) {
            $collector->forward($collector->stopCollectingEvents());

            throw $t;
            // @codeCoverageIgnoreEnd
        }

        return $collector->stopCollectingEvents();
    }

    /**
     * Whether the events collected for a single run of a PHPT test indicate
     * that the run failed or errored.
     */
    final protected function failedOrErrored(EventCollection $events): bool
    {
        foreach ($events as $event) {
            if ($event instanceof Event\Test\Failed || $event instanceof Event\Test\Errored) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the run must not go on to the next repetition or attempt: either
     * the caller has abandoned this test — which is how a parallel run stops
     * the test it is advancing, its results being released by the aggregator
     * rather than collected here — or the results collected so far call for
     * the test runner to stop.
     */
    final protected function shouldStop(?Interruption $interruption): bool
    {
        if ($interruption !== null && $interruption->interrupted()) {
            return true;
        }

        return TestResultFacade::shouldStop();
    }
}
