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
     * @var non-empty-string
     */
    private string $filename;

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
            /*
             * The events of an attempt are only forwarded when no further
             * attempt is made, so the test result of an attempt cannot make
             * the test runner stop before the next attempt is made. And a
             * PHPT test that interrupts the test runner does not fail, so it
             * is not retried. This makes the check below unreachable, but it
             * is kept so that this class does not silently ignore a reason to
             * stop that a future change may introduce.
             */
            // @codeCoverageIgnoreStart
            if (TestResultFacade::shouldStop()) {
                $emitter->testRunnerExecutionAborted();

                return;
            }
            // @codeCoverageIgnoreEnd

            $test = new PhptTestCase($this->filename, 1, 1, $attempt, $this->maxAttempts);

            $events = $this->runCollectingEvents($test);

            if (!$this->failedOrErrored($events) || $attempt === $this->maxAttempts) {
                $facade->forward($events);

                return;
            }

            // A PHPT test reports failures and errors as events, so a failed or
            // errored run always carries a Failed or Errored event from which
            // the attempt event can be synthesized.
            $this->emitAttemptEvent($events, $emitter);
        }
    }
}
