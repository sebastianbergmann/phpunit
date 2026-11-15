<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Receipt;

use function count;
use DateTimeImmutable;
use PHPUnit\TestRunner\TestResult\TestResult;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class OutcomeBuilder
{
    /**
     * The numbers come from the result that the shell exit code is calculated
     * from, so that the receipt and the shell exit code cannot disagree about
     * what happened.
     *
     * @param bool $started     whether the test runner got to the point at which it runs tests
     * @param bool $interrupted whether a signal ended the test run
     */
    public function build(TestResult $result, int $shellExitCode, bool $started, bool $interrupted): Outcome
    {
        return new Outcome(
            new DateTimeImmutable,
            $this->status($result, $started, $interrupted),
            $shellExitCode,
            $result->numberOfTestsRun(),
            $result->numberOfAssertions(),
            $result->numberOfTestErroredEvents(),
            $result->numberOfTestFailedEvents(),
            $result->numberOfTestSkippedByTestSuiteSkippedEvents() + $result->numberOfTestSkippedEvents(),
            $result->numberOfTestMarkedIncompleteEvents(),
            $result->numberOfTestsWithTestConsideredRiskyEvents(),
            new IssueCounts(
                count($result->errors()),
                count($result->deprecations()) + count($result->phpDeprecations()),
                count($result->notices()) + count($result->phpNotices()),
                count($result->warnings()) + count($result->phpWarnings()),
                $result->numberOfTestsWithTestTriggeredPhpunitErrorEvents(),
                $result->numberOfTestsWithTestTriggeredPhpunitDeprecationEvents(),
                $result->numberOfTestsWithTestTriggeredPhpunitNoticeEvents(),
                $result->numberOfTestsWithTestTriggeredPhpunitWarningEvents(),
            ),
            new IssueCounts(
                count($result->testRunnerTriggeredIssueErrorEvents()),
                count($result->testRunnerTriggeredIssueDeprecationEvents()) + count($result->testRunnerTriggeredIssuePhpDeprecationEvents()),
                count($result->testRunnerTriggeredIssueNoticeEvents()) + count($result->testRunnerTriggeredIssuePhpNoticeEvents()),
                count($result->testRunnerTriggeredIssueWarningEvents()) + count($result->testRunnerTriggeredIssuePhpWarningEvents()),
                0,
                $result->numberOfTestRunnerTriggeredDeprecationEvents(),
                $result->numberOfTestRunnerTriggeredNoticeEvents(),
                $result->numberOfTestRunnerTriggeredWarningEvents(),
            ),
        );
    }

    /**
     * The order mirrors the precedence of the shell exit code: an exceeded
     * time limit ends the test run no matter what else happened.
     */
    private function status(TestResult $result, bool $started, bool $interrupted): Status
    {
        if ($result->wasTimeLimitExceeded()) {
            return Status::TimeLimitExceeded;
        }

        if (!$started) {
            return Status::NotStarted;
        }

        if ($interrupted) {
            return Status::Interrupted;
        }

        if ($result->wasExecutionAborted()) {
            return Status::Stopped;
        }

        return Status::Completed;
    }
}
