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
use function range;
use PHPUnit\Event;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PhptRepeatTestSuite extends PhptIterativeTestSuite
{
    /**
     * @var positive-int
     */
    private int $failureThreshold = 1;

    /**
     * @param non-empty-string $filename
     * @param positive-int     $numberOfRuns
     * @param positive-int     $failureThreshold
     */
    public static function for(string $filename, int $numberOfRuns, int $failureThreshold): self
    {
        $suite = self::empty($filename);

        $suite->filename         = $filename;
        $suite->failureThreshold = $failureThreshold;

        foreach (range(1, $numberOfRuns) as $repetition) {
            $suite->addTest(new PhptTestCase($filename, $repetition, $numberOfRuns));
        }

        return $suite;
    }

    /**
     * @param list<Test> $tests
     */
    protected function execute(array $tests, Event\Emitter $emitter): void
    {
        $facade = EventFacade::instance();

        $failureCount         = 0;
        $lastFailedRepetition = 0;

        foreach ($tests as $test) {
            assert($test instanceof PhptTestCase);

            if (TestResultFacade::shouldStop()) {
                $emitter->testRunnerExecutionAborted();

                break;
            }

            if ($failureCount >= $this->failureThreshold) {
                $test->markSkippedForRepeatAbort($lastFailedRepetition);

                continue;
            }

            $facade->startCollectingEvents();

            $test->run();

            $events = $facade->stopCollectingEvents();

            $facade->forward($events);

            if ($this->failedOrErrored($events)) {
                $failureCount++;
                $lastFailedRepetition = $test->repetition();
            }
        }
    }
}
