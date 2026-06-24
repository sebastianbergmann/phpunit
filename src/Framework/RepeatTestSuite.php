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

use function array_pop;
use function array_reverse;
use PHPUnit\Event;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RepeatTestSuite extends IterativeTestSuite
{
    /**
     * @var positive-int
     */
    private int $failureThreshold = 1;

    /**
     * @param non-empty-string         $name
     * @param non-empty-list<TestCase> $tests
     * @param positive-int             $failureThreshold
     * @param list<non-empty-string>   $groups
     */
    public static function fromTests(string $name, array $tests, int $failureThreshold, array $groups = []): self
    {
        $suite = self::empty($name);

        $suite->failureThreshold = $failureThreshold;

        foreach ($tests as $test) {
            $suite->addTest($test, $groups);
        }

        return $suite;
    }

    /**
     * @param list<TestCase> $tests
     */
    protected function execute(array $tests, Event\Emitter $emitter): void
    {
        $tests = array_reverse($tests);

        $failureCount         = 0;
        $lastFailedRepetition = 0;

        while (($test = array_pop($tests)) !== null) {
            if (TestResultFacade::shouldStop()) {
                $emitter->testRunnerExecutionAborted();

                break;
            }

            if ($failureCount >= $this->failureThreshold) {
                $test->markSkippedForRepeatAbort($lastFailedRepetition);

                continue;
            }

            $test->run();

            if ($test->status()->isFailure() || $test->status()->isError()) {
                $failureCount++;
                $lastFailedRepetition = $test->repetition();
            }
        }
    }
}
