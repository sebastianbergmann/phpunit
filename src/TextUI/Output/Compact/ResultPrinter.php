<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Compact;

use const PHP_EOL;
use function assert;
use function implode;
use function ksort;
use function sprintf;
use function trim;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitNoticeTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\TestRunner\ErrorTriggered;
use PHPUnit\Event\TestRunner\Issue as TestRunnerIssue;
use PHPUnit\Event\TestRunner\PhpDeprecationTriggered;
use PHPUnit\Event\TestRunner\PhpNoticeTriggered;
use PHPUnit\Event\TestRunner\PhpWarningTriggered;
use PHPUnit\TestRunner\TestResult\Issues\Issue;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Output\Printer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ResultPrinter
{
    private Printer $printer;
    private Renderer $renderer;
    private bool $displayDetailsOnIncompleteTests;
    private bool $displayDetailsOnSkippedTests;
    private bool $displayDetailsOnTestsThatTriggerDeprecations;
    private bool $displayDetailsOnTestsThatTriggerErrors;
    private bool $displayDetailsOnTestsThatTriggerNotices;
    private bool $displayDetailsOnTestsThatTriggerWarnings;

    public function __construct(Printer $printer, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $displayDetailsOnTestsThatTriggerDeprecations, bool $displayDetailsOnTestsThatTriggerErrors, bool $displayDetailsOnTestsThatTriggerNotices, bool $displayDetailsOnTestsThatTriggerWarnings)
    {
        $this->printer                                      = $printer;
        $this->renderer                                     = new Renderer($printer);
        $this->displayDetailsOnIncompleteTests              = $displayDetailsOnIncompleteTests;
        $this->displayDetailsOnSkippedTests                 = $displayDetailsOnSkippedTests;
        $this->displayDetailsOnTestsThatTriggerDeprecations = $displayDetailsOnTestsThatTriggerDeprecations;
        $this->displayDetailsOnTestsThatTriggerErrors       = $displayDetailsOnTestsThatTriggerErrors;
        $this->displayDetailsOnTestsThatTriggerNotices      = $displayDetailsOnTestsThatTriggerNotices;
        $this->displayDetailsOnTestsThatTriggerWarnings     = $displayDetailsOnTestsThatTriggerWarnings;
    }

    public function print(TestResult $result): void
    {
        if ($result->numberOfTestsRun() === 0) {
            $this->printer->print('No tests executed!' . PHP_EOL);

            return;
        }

        if ($result->hasTestErroredEvents() || $result->hasTestFailedEvents()) {
            $this->printer->print(PHP_EOL);
        }

        $this->printSummaryLine($result);
        $this->printPhpunitErrors($result);
        $this->printTestRunnerWarnings($result);
        $this->printTestRunnerDeprecations($result);
        $this->printTestRunnerNotices($result);
        $this->printPhpunitWarnings($result);
        $this->printPhpunitDeprecations($result);
        $this->printPhpunitNotices($result);

        if ($this->displayDetailsOnTestsThatTriggerDeprecations) {
            $this->printDeprecations($result);
            $this->printIssuesTriggeredOutsideOfTests($result->testRunnerTriggeredIssuePhpDeprecationEvents(), 'PHP DEPRECATION');
            $this->printIssuesTriggeredOutsideOfTests($result->testRunnerTriggeredIssueDeprecationEvents(), 'DEPRECATION');
        }

        if ($this->displayDetailsOnTestsThatTriggerWarnings) {
            $this->printWarnings($result);
            $this->printIssuesTriggeredOutsideOfTests($result->testRunnerTriggeredIssuePhpWarningEvents(), 'PHP WARNING');
            $this->printIssuesTriggeredOutsideOfTests($result->testRunnerTriggeredIssueWarningEvents(), 'WARNING');
        }

        if ($this->displayDetailsOnTestsThatTriggerNotices) {
            $this->printNotices($result);
            $this->printIssuesTriggeredOutsideOfTests($result->testRunnerTriggeredIssuePhpNoticeEvents(), 'PHP NOTICE');
            $this->printIssuesTriggeredOutsideOfTests($result->testRunnerTriggeredIssueNoticeEvents(), 'NOTICE');
        }

        if ($this->displayDetailsOnTestsThatTriggerErrors) {
            $this->printTestTriggeredErrors($result);
            $this->printIssuesTriggeredOutsideOfTests($result->testRunnerTriggeredIssueErrorEvents(), 'ERROR');
        }

        $this->printRiskyTests($result);

        if ($this->displayDetailsOnIncompleteTests) {
            $this->printIncompleteTests($result);
        }

        if ($this->displayDetailsOnSkippedTests) {
            $this->printSkippedTests($result);
        }
    }

    private function printSummaryLine(TestResult $result): void
    {
        $counts = [];

        $counts[] = sprintf(
            '%d test%s',
            $result->numberOfTestsRun(),
            $result->numberOfTestsRun() === 1 ? '' : 's',
        );

        $counts[] = sprintf(
            '%d assertion%s',
            $result->numberOfAssertions(),
            $result->numberOfAssertions() === 1 ? '' : 's',
        );

        if ($result->numberOfErrors() > 0) {
            $counts[] = sprintf('%d error%s', $result->numberOfErrors(), $result->numberOfErrors() === 1 ? '' : 's');
        }

        if ($result->numberOfTestFailedEvents() > 0) {
            $counts[] = sprintf('%d failure%s', $result->numberOfTestFailedEvents(), $result->numberOfTestFailedEvents() === 1 ? '' : 's');
        }

        if ($result->numberOfPhpOrUserDeprecations() > 0) {
            $counts[] = sprintf('%d deprecation%s', $result->numberOfPhpOrUserDeprecations(), $result->numberOfPhpOrUserDeprecations() === 1 ? '' : 's');
        }

        if ($result->numberOfWarnings() > 0) {
            $counts[] = sprintf('%d warning%s', $result->numberOfWarnings(), $result->numberOfWarnings() === 1 ? '' : 's');
        }

        if ($result->numberOfNotices() > 0) {
            $counts[] = sprintf('%d notice%s', $result->numberOfNotices(), $result->numberOfNotices() === 1 ? '' : 's');
        }

        $skipped = $result->numberOfTestSkippedByTestSuiteSkippedEvents() + $result->numberOfTestSkippedEvents();

        if ($skipped > 0) {
            $counts[] = sprintf('%d skipped', $skipped);
        }

        if ($result->numberOfTestMarkedIncompleteEvents() > 0) {
            $counts[] = sprintf('%d incomplete', $result->numberOfTestMarkedIncompleteEvents());
        }

        if ($result->numberOfTestsWithTestConsideredRiskyEvents() > 0) {
            $counts[] = sprintf('%d risky', $result->numberOfTestsWithTestConsideredRiskyEvents());
        }

        $countString = implode(', ', $counts);

        if ($result->wasSuccessful() && !$result->hasIssues() &&
            !$result->hasTestSuiteSkippedEvents() && !$result->hasTestSkippedEvents()) {
            $this->printer->print(sprintf('OK (%s)' . PHP_EOL, $countString));
        } elseif ($result->wasSuccessful()) {
            $this->printer->print(sprintf('OK (%s)' . PHP_EOL, $countString));
        } elseif ($result->hasTestErroredEvents() || $result->hasTestTriggeredPhpunitErrorEvents()) {
            $this->printer->print(sprintf('ERRORS (%s)' . PHP_EOL, $countString));
        } else {
            $this->printer->print(sprintf('FAILURES (%s)' . PHP_EOL, $countString));
        }
    }

    private function printDeprecations(TestResult $result): void
    {
        $this->printIssueList('DEPRECATION', $result->phpDeprecations());
        $this->printIssueList('DEPRECATION', $result->deprecations());
    }

    private function printWarnings(TestResult $result): void
    {
        $this->printIssueList('WARNING', $result->phpWarnings());
        $this->printIssueList('WARNING', $result->warnings());
    }

    private function printNotices(TestResult $result): void
    {
        $this->printIssueList('NOTICE', $result->phpNotices());
        $this->printIssueList('NOTICE', $result->notices());
    }

    private function printTestTriggeredErrors(TestResult $result): void
    {
        $this->printIssueList('ERROR', $result->errors());
    }

    private function printPhpunitErrors(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpunitErrorEvents()) {
            return;
        }

        foreach ($result->testTriggeredPhpunitErrorEvents() as $events) {
            assert(isset($events[0]));

            $this->printer->print(PHP_EOL . '--- PHPUNIT ERROR: ' . $this->renderer->nameOfTest($events[0]->test()) . PHP_EOL);

            foreach ($events as $event) {
                assert($event instanceof PhpunitErrorTriggered);

                $this->printer->print(trim($event->message()) . PHP_EOL);
            }
        }
    }

    private function printPhpunitWarnings(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpunitWarningEvents()) {
            return;
        }

        foreach ($result->testTriggeredPhpunitWarningEvents() as $events) {
            assert(isset($events[0]));

            $this->printer->print(PHP_EOL . '--- PHPUNIT WARNING: ' . $this->renderer->nameOfTest($events[0]->test()) . PHP_EOL);

            foreach ($events as $event) {
                assert($event instanceof PhpunitWarningTriggered);

                $this->printer->print(trim($event->message()) . PHP_EOL);
            }
        }
    }

    private function printPhpunitDeprecations(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpunitDeprecationEvents()) {
            return;
        }

        foreach ($result->testTriggeredPhpunitDeprecationEvents() as $events) {
            assert(isset($events[0]));

            $this->printer->print(PHP_EOL . '--- PHPUNIT DEPRECATION: ' . $this->renderer->nameOfTest($events[0]->test()) . PHP_EOL);

            foreach ($events as $event) {
                assert($event instanceof PhpunitDeprecationTriggered);

                $this->printer->print(trim($event->message()) . PHP_EOL);
            }
        }
    }

    private function printPhpunitNotices(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpunitNoticeEvents()) {
            return;
        }

        foreach ($result->testTriggeredPhpunitNoticeEvents() as $events) {
            assert(isset($events[0]));

            $this->printer->print(PHP_EOL . '--- PHPUNIT NOTICE: ' . $this->renderer->nameOfTest($events[0]->test()) . PHP_EOL);

            foreach ($events as $event) {
                assert($event instanceof PhpunitNoticeTriggered);

                $this->printer->print(trim($event->message()) . PHP_EOL);
            }
        }
    }

    private function printTestRunnerWarnings(TestResult $result): void
    {
        if (!$result->hasTestRunnerTriggeredWarningEvents()) {
            return;
        }

        $seen = [];

        foreach ($result->testRunnerTriggeredWarningEvents() as $event) {
            $message = $event->message();

            if (isset($seen[$message])) {
                continue;
            }

            $seen[$message] = true;

            $this->printer->print(PHP_EOL . '--- PHPUNIT TEST RUNNER WARNING' . PHP_EOL);
            $this->printer->print(trim($message) . PHP_EOL);
        }
    }

    private function printTestRunnerDeprecations(TestResult $result): void
    {
        if (!$result->hasTestRunnerTriggeredDeprecationEvents()) {
            return;
        }

        foreach ($result->testRunnerTriggeredDeprecationEvents() as $event) {
            $this->printer->print(PHP_EOL . '--- PHPUNIT TEST RUNNER DEPRECATION' . PHP_EOL);
            $this->printer->print(trim($event->message()) . PHP_EOL);
        }
    }

    private function printTestRunnerNotices(TestResult $result): void
    {
        if (!$result->hasTestRunnerTriggeredNoticeEvents()) {
            return;
        }

        $seen = [];

        foreach ($result->testRunnerTriggeredNoticeEvents() as $event) {
            $message = $event->message();

            if (isset($seen[$message])) {
                continue;
            }

            $seen[$message] = true;

            $this->printer->print(PHP_EOL . '--- PHPUNIT TEST RUNNER NOTICE' . PHP_EOL);
            $this->printer->print(trim($message) . PHP_EOL);
        }
    }

    /**
     * @param list<ErrorTriggered|PhpDeprecationTriggered|PhpNoticeTriggered|PhpWarningTriggered|TestRunnerIssue\DeprecationTriggered|TestRunnerIssue\NoticeTriggered|TestRunnerIssue\WarningTriggered> $events
     */
    private function printIssuesTriggeredOutsideOfTests(array $events, string $type): void
    {
        $seen = [];

        foreach ($events as $event) {
            $key = $event->file() . ':' . $event->line() . ':' . $event->message();

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            $this->printer->print(
                PHP_EOL . sprintf(
                    '--- %s: %s:%d',
                    $type,
                    $event->file(),
                    $event->line(),
                ) . PHP_EOL,
            );

            $this->printer->print(trim($event->message()) . PHP_EOL);
        }
    }

    private function printRiskyTests(TestResult $result): void
    {
        if (!$result->hasTestConsideredRiskyEvents()) {
            return;
        }

        foreach ($result->testConsideredRiskyEvents() as $reasons) {
            assert(isset($reasons[0]));

            $test = $reasons[0]->test();

            $this->printer->print(PHP_EOL . '--- RISKY: ' . $this->renderer->nameOfTest($test) . PHP_EOL);

            foreach ($reasons as $reason) {
                $this->printer->print($reason->message() . PHP_EOL);
            }
        }
    }

    private function printIncompleteTests(TestResult $result): void
    {
        if (!$result->hasTestMarkedIncompleteEvents()) {
            return;
        }

        foreach ($result->testMarkedIncompleteEvents() as $event) {
            $this->printer->print(PHP_EOL . '--- INCOMPLETE: ' . $this->renderer->nameOfTest($event->test()) . PHP_EOL);
            $this->printer->print(trim($event->throwable()->description()) . PHP_EOL);
        }
    }

    private function printSkippedTests(TestResult $result): void
    {
        if (!$result->hasTestSkippedEvents()) {
            return;
        }

        foreach ($result->testSkippedEvents() as $event) {
            $this->printer->print(PHP_EOL . '--- SKIPPED: ' . $this->renderer->nameOfTest($event->test()) . PHP_EOL);

            if ($event->message() !== '') {
                $this->printer->print($event->message() . PHP_EOL);
            }
        }
    }

    /**
     * @param list<Issue> $issues
     */
    private function printIssueList(string $type, array $issues): void
    {
        foreach ($issues as $issue) {
            $this->printer->print(
                PHP_EOL . sprintf(
                    '--- %s: %s:%d',
                    $type,
                    $issue->file(),
                    $issue->line(),
                ) . PHP_EOL,
            );

            $this->printer->print(trim($issue->description()) . PHP_EOL);

            if (!$issue->triggeredInTest()) {
                $triggeringTests = $issue->triggeringTests();

                ksort($triggeringTests);

                foreach ($triggeringTests as $triggeringTest) {
                    $location = $triggeringTest['test']->id();

                    if ($triggeringTest['test']->isTestMethod()) {
                        $location .= ' (' . $triggeringTest['test']->file() . ':' . $triggeringTest['test']->line() . ')';
                    }

                    $this->printer->print('Triggered by: ' . $location . PHP_EOL);
                }
            }
        }
    }
}
