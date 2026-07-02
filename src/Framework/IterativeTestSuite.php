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

use function assert;
use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\NoPreviousThrowableException;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;

/**
 * A test suite that aggregates the executions of a single test method, such as
 * the repetitions of a repeated test or the attempts of a retried test.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class IterativeTestSuite extends TestSuite
{
    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $dependencies = [];

    /**
     * @param list<ExecutionOrderDependency> $dependencies
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;

        foreach ($this->tests() as $test) {
            if (!$test instanceof TestCase) {
                continue;
            }

            $test->setDependencies($dependencies);
        }
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    public function provides(): array
    {
        $tests = $this->tests();

        if ($tests === []) {
            return [];
        }

        assert($tests[0] instanceof Reorderable);

        return $tests[0]->provides();
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    public function requires(): array
    {
        $tests = $this->tests();

        if ($tests === []) {
            return [];
        }

        assert($tests[0] instanceof Reorderable);

        return $tests[0]->requires();
    }

    public function sortId(): string
    {
        $tests = $this->tests();

        assert($tests !== []);
        assert($tests[0] instanceof Reorderable);

        return $tests[0]->sortId();
    }

    /**
     * @param list<Test> $tests
     *
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     */
    final protected function runTests(array $tests, Event\Emitter $emitter): void
    {
        $this->execute($tests, $emitter);
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    final protected function dependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param list<Test> $tests
     *
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     */
    abstract protected function execute(array $tests, Event\Emitter $emitter): void;

    final protected function emitAttemptEvent(EventCollection $events, Event\Emitter $emitter): bool
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
    final protected function durationOf(EventCollection $events): Event\Telemetry\Duration
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
