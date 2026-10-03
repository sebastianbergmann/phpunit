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
use function count;
use function range;
use Generator;
use PHPUnit\Event;
use PHPUnit\Event\EventCollector;
use PHPUnit\Runner\Phpt\Interruption;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\Result;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PhptRepeatTestSuite extends PhptIterativeTestSuite
{
    /**
     * The repetitions to run are all of them, unless only some are given: the
     * ones that test selection picked, such as with --run-test-id, when the
     * parallel test runner rebuilds the suite.
     *
     * @param non-empty-string   $filename
     * @param positive-int       $numberOfRuns
     * @param list<positive-int> $repetitions
     */
    public static function for(string $filename, Event\Emitter $emitter, int $numberOfRuns, array $repetitions = []): self
    {
        if ($repetitions === []) {
            $repetitions = range(1, $numberOfRuns);
        }

        $suite = self::empty($filename, $emitter);

        foreach ($repetitions as $repetition) {
            $suite->addTest(new PhptTestCase($filename, $repetition, $numberOfRuns));
        }

        return $suite;
    }

    /**
     * @return positive-int
     */
    public function numberOfRuns(): int
    {
        $numberOfRuns = count($this->tests());

        assert($numberOfRuns > 0);

        return $numberOfRuns;
    }

    /**
     * @param list<Test> $tests
     *
     * @throws Event\RuntimeException
     *
     * @return Generator<int, Job, Result, void>
     */
    protected function iterate(array $tests, Event\Emitter $emitter, EventCollector $collector, ?Interruption $interruption = null): Generator
    {
        $lastFailedRepetition = 0;

        foreach ($tests as $test) {
            assert($test instanceof PhptTestCase);

            if ($this->shouldStop($interruption)) {
                $emitter->testRunnerExecutionAborted();

                break;
            }

            if ($lastFailedRepetition !== 0) {
                $this->skipRemainingRepetition($test->valueObjectForEvents(), $lastFailedRepetition, $emitter);

                continue;
            }

            $events = yield from $this->executeCollectingEvents($test, $emitter, $collector, $interruption);

            $collector->forward($events);

            if ($this->failedOrErrored($events)) {
                $lastFailedRepetition = $test->repetition();
            }
        }
    }
}
