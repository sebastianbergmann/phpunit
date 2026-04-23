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
use function str_starts_with;
use function strlen;
use function substr;
use function trim;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\Test\AfterLastTestMethodFailed;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodFailed;
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
    private bool $displayDetailsOnIncompleteTests;
    private bool $displayDetailsOnSkippedTests;
    private bool $displayDetailsOnTestsThatTriggerDeprecations;
    private bool $displayDetailsOnTestsThatTriggerErrors;
    private bool $displayDetailsOnTestsThatTriggerNotices;
    private bool $displayDetailsOnTestsThatTriggerWarnings;

    public function __construct(Printer $printer, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $displayDetailsOnTestsThatTriggerDeprecations, bool $displayDetailsOnTestsThatTriggerErrors, bool $displayDetailsOnTestsThatTriggerNotices, bool $displayDetailsOnTestsThatTriggerWarnings)
    {
        $this->printer                                      = $printer;
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

        $this->printSummaryLine($result);
        $this->printErrors($result);
        $this->printFailures($result);

        if ($this->displayDetailsOnTestsThatTriggerDeprecations) {
            $this->printDeprecations($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerWarnings) {
            $this->printWarnings($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerNotices) {
            $this->printNotices($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerErrors) {
            $this->printTestTriggeredErrors($result);
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

    private function printErrors(TestResult $result): void
    {
        if (!$result->hasTestErroredEvents()) {
            return;
        }

        foreach ($result->testErroredEvents() as $event) {
            if ($event instanceof AfterLastTestMethodErrored || $event instanceof BeforeFirstTestMethodErrored) {
                $title = $event->testClassName();
            } else {
                $title = $this->name($event->test());
            }

            $this->printer->print(PHP_EOL . '--- ERROR: ' . $title . PHP_EOL);
            $this->printThrowable($event->throwable());
        }
    }

    private function printFailures(TestResult $result): void
    {
        if (!$result->hasTestFailedEvents()) {
            return;
        }

        foreach ($result->testFailedEvents() as $event) {
            if ($event instanceof AfterLastTestMethodFailed || $event instanceof BeforeFirstTestMethodFailed) {
                $title = $event->testClassName();
            } else {
                $title = $this->name($event->test());
            }

            $this->printer->print(PHP_EOL . '--- FAILURE: ' . $title . PHP_EOL);

            $body = $event->throwable()->description();

            if (str_starts_with($body, 'AssertionError: ')) {
                $body = substr($body, strlen('AssertionError: '));
            }

            $this->printer->print(trim($body) . PHP_EOL);
            $this->printStackTrace($event->throwable()->stackTrace());
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

    private function printRiskyTests(TestResult $result): void
    {
        if (!$result->hasTestConsideredRiskyEvents()) {
            return;
        }

        foreach ($result->testConsideredRiskyEvents() as $reasons) {
            assert(isset($reasons[0]));

            $test = $reasons[0]->test();

            $this->printer->print(PHP_EOL . '--- RISKY: ' . $this->name($test) . PHP_EOL);

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
            $this->printer->print(PHP_EOL . '--- INCOMPLETE: ' . $this->name($event->test()) . PHP_EOL);
            $this->printer->print(trim($event->throwable()->description()) . PHP_EOL);
        }
    }

    private function printSkippedTests(TestResult $result): void
    {
        if (!$result->hasTestSkippedEvents()) {
            return;
        }

        foreach ($result->testSkippedEvents() as $event) {
            $this->printer->print(PHP_EOL . '--- SKIPPED: ' . $this->name($event->test()) . PHP_EOL);

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

    private function printThrowable(Throwable $throwable): void
    {
        $this->printer->print(trim($throwable->description()) . PHP_EOL);
        $this->printStackTrace($throwable->stackTrace());

        if ($throwable->hasPrevious()) {
            $this->printer->print('Caused by' . PHP_EOL);
            $this->printThrowable($throwable->previous());
        }
    }

    private function printStackTrace(string $stackTrace): void
    {
        $stackTrace = trim($stackTrace);

        if ($stackTrace === '') {
            return;
        }

        $this->printer->print(PHP_EOL . $stackTrace . PHP_EOL);
    }

    private function name(Test $test): string
    {
        if ($test->isTestMethod()) {
            assert($test instanceof TestMethod);

            if (!$test->testData()->hasDataFromDataProvider()) {
                return $test->nameWithClass();
            }

            return $test->className() . '::' . $test->methodName() . $test->testData()->dataFromDataProvider()->dataAsStringForResultOutput();
        }

        return $test->name();
    }
}
