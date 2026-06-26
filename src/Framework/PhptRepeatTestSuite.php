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
     * @param non-empty-string $filename
     * @param positive-int     $numberOfRuns
     */
    public static function for(string $filename, int $numberOfRuns): self
    {
        $suite = self::empty($filename);

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

        $lastFailedRepetition = 0;

        foreach ($tests as $test) {
            assert($test instanceof PhptTestCase);

            if (TestResultFacade::shouldStop()) {
                $emitter->testRunnerExecutionAborted();

                break;
            }

            if ($lastFailedRepetition !== 0) {
                $test->markSkippedForRepeatAbort($lastFailedRepetition);

                continue;
            }

            $facade->startCollectingEvents();

            $test->run();

            $events = $facade->stopCollectingEvents();

            $facade->forward($events);

            if ($this->failedOrErrored($events)) {
                $lastFailedRepetition = $test->repetition();
            }
        }
    }
}
