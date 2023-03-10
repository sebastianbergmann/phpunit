<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Default;

use const PHP_EOL;
use function array_merge;
use function array_reverse;
use function assert;
use function count;
use function explode;
use function range;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestData\NoDataSetFromDataProviderException;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Output\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter
{
    private readonly Printer $printer;
    private readonly bool $displayPhpunitErrors;
    private readonly bool $displayPhpunitWarnings;
    private readonly bool $displayTestsWithErrors;
    private readonly bool $displayTestsWithFailedAssertions;
    private readonly bool $displayRiskyTests;
    private readonly bool $displayDetailsOnTestsThatTriggeredPhpunitDeprecations;
    private readonly bool $displayDetailsOnIncompleteTests;
    private readonly bool $displayDetailsOnSkippedTests;
    private readonly bool $displayDetailsOnTestsThatTriggerDeprecations;
    private readonly bool $displayDetailsOnTestsThatTriggerErrors;
    private readonly bool $displayDetailsOnTestsThatTriggerNotices;
    private readonly bool $displayDetailsOnTestsThatTriggerWarnings;
    private readonly bool $displayDefectsInReverseOrder;
    private bool $listPrinted = false;

    public function __construct(Printer $printer, bool $displayPhpunitErrors, bool $displayPhpunitWarnings, bool $displayTestsWithErrors, bool $displayTestsWithFailedAssertions, bool $displayRiskyTests, bool $displayDetailsOnTestsThatTriggeredPhpunitDeprecations, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $displayDetailsOnTestsThatTriggerDeprecations, bool $displayDetailsOnTestsThatTriggerErrors, bool $displayDetailsOnTestsThatTriggerNotices, bool $displayDetailsOnTestsThatTriggerWarnings, bool $displayDefectsInReverseOrder)
    {
        $this->printer                                               = $printer;
        $this->displayPhpunitErrors                                  = $displayPhpunitErrors;
        $this->displayPhpunitWarnings                                = $displayPhpunitWarnings;
        $this->displayTestsWithErrors                                = $displayTestsWithErrors;
        $this->displayTestsWithFailedAssertions                      = $displayTestsWithFailedAssertions;
        $this->displayRiskyTests                                     = $displayRiskyTests;
        $this->displayDetailsOnTestsThatTriggeredPhpunitDeprecations = $displayDetailsOnTestsThatTriggeredPhpunitDeprecations;
        $this->displayDetailsOnIncompleteTests                       = $displayDetailsOnIncompleteTests;
        $this->displayDetailsOnSkippedTests                          = $displayDetailsOnSkippedTests;
        $this->displayDetailsOnTestsThatTriggerDeprecations          = $displayDetailsOnTestsThatTriggerDeprecations;
        $this->displayDetailsOnTestsThatTriggerErrors                = $displayDetailsOnTestsThatTriggerErrors;
        $this->displayDetailsOnTestsThatTriggerNotices               = $displayDetailsOnTestsThatTriggerNotices;
        $this->displayDetailsOnTestsThatTriggerWarnings              = $displayDetailsOnTestsThatTriggerWarnings;
        $this->displayDefectsInReverseOrder                          = $displayDefectsInReverseOrder;
    }

    public function print(TestResult $result): void
    {
        if ($this->displayPhpunitErrors) {
            $this->printPhpunitErrors($result);
        }

        if ($this->displayPhpunitWarnings) {
            $this->printPhpunitWarnings($result);
        }

        if ($this->displayTestsWithErrors) {
            $this->printTestsWithErrors($result);
        }

        if ($this->displayTestsWithFailedAssertions) {
            $this->printTestsWithFailedAssertions($result);
        }

        if ($this->displayRiskyTests) {
            $this->printRiskyTests($result);
        }

        if ($this->displayDetailsOnTestsThatTriggeredPhpunitDeprecations) {
            $this->printDetailsOnTestsThatTriggeredPhpunitDeprecations($result);
        }

        if ($this->displayDetailsOnIncompleteTests) {
            $this->printIncompleteTests($result);
        }

        if ($this->displayDetailsOnSkippedTests) {
            $this->printSkippedTestSuites($result);
            $this->printSkippedTests($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerDeprecations) {
            $this->printDetailsOnTestsThatTriggerPhpDeprecations($result);
            $this->printDetailsOnTestsThatTriggerDeprecations($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerErrors) {
            $this->printDetailsOnTestsThatTriggerErrors($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerNotices) {
            $this->printDetailsOnTestsThatTriggerPhpNotices($result);
            $this->printDetailsOnTestsThatTriggerNotices($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerWarnings) {
            $this->printDetailsOnTestsThatTriggerPhpWarnings($result);
            $this->printDetailsOnTestsThatTriggerWarnings($result);
        }
    }

    public function flush(): void
    {
        $this->printer->flush();
    }

    private function printPhpunitErrors(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpunitErrorEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestTriggeredPhpunitErrorEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredPhpunitErrorEvents()),
            'PHPUnit error'
        );
    }

    private function printPhpunitWarnings(TestResult $result): void
    {
        if (!$result->hasTestRunnerTriggeredWarningEvents() &&
            !$result->hasTestTriggeredPhpunitWarningEvents()) {
            return;
        }

        $elements = [];

        foreach ($result->testRunnerTriggeredWarningEvents() as $event) {
            $elements[] = [
                'title' => $event->message(),
                'body'  => '',
            ];
        }

        $elements = array_merge(
            $elements,
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredPhpunitWarningEvents())
        );

        $this->printList(
            $result->numberOfTestRunnerTriggeredWarningEvents() + $result->numberOfTestsWithTestTriggeredPhpunitWarningEvents(),
            $elements,
            'PHPUnit warning'
        );
    }

    private function printDetailsOnTestsThatTriggeredPhpunitDeprecations(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpunitDeprecationEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestTriggeredPhpunitDeprecationEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredPhpunitDeprecationEvents()),
            'PHPUnit deprecation'
        );
    }

    private function printTestsWithErrors(TestResult $result): void
    {
        if (!$result->hasTestErroredEvents()) {
            return;
        }

        $elements = [];

        foreach ($result->testErroredEvents() as $event) {
            if ($event instanceof BeforeFirstTestMethodErrored) {
                $title = $event->testClassName();
            } else {
                $title = $this->name($event->test());
            }

            $elements[] = [
                'title' => $title,
                'body'  => $event->throwable()->asString(),
            ];
        }

        $this->printList(count($elements), $elements, 'error');
    }

    private function printTestsWithFailedAssertions(TestResult $result): void
    {
        if (!$result->hasTestFailedEvents()) {
            return;
        }

        $elements = [];

        foreach ($result->testFailedEvents() as $event) {
            $body = $event->throwable()->asString();

            if (str_starts_with($body, 'AssertionError: ')) {
                $body = substr($body, strlen('AssertionError: '));
            }

            $elements[] = [
                'title' => $this->name($event->test()),
                'body'  => $body,
            ];
        }

        $this->printList(count($elements), $elements, 'failure');
    }

    private function printRiskyTests(TestResult $result): void
    {
        if (!$result->hasTestConsideredRiskyEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestConsideredRiskyEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testConsideredRiskyEvents()),
            'risky test'
        );
    }

    private function printIncompleteTests(TestResult $result): void
    {
        if (!$result->hasTestMarkedIncompleteEvents()) {
            return;
        }

        $elements = [];

        foreach ($result->testMarkedIncompleteEvents() as $event) {
            $elements[] = [
                'title' => $this->name($event->test()),
                'body'  => $event->throwable()->asString(),
            ];
        }

        $this->printList(count($elements), $elements, 'incomplete test');
    }

    private function printSkippedTestSuites(TestResult $result): void
    {
        if (!$result->hasTestSuiteSkippedEvents()) {
            return;
        }

        $elements = [];

        foreach ($result->testSuiteSkippedEvents() as $event) {
            $elements[] = [
                'title' => $event->testSuite()->name(),
                'body'  => $event->message(),
            ];
        }

        $this->printList(count($elements), $elements, 'skipped test suite');
    }

    private function printSkippedTests(TestResult $result): void
    {
        if (!$result->hasTestSkippedEvents()) {
            return;
        }

        $elements = [];

        foreach ($result->testSkippedEvents() as $event) {
            $elements[] = [
                'title' => $this->name($event->test()),
                'body'  => $event->message(),
            ];
        }

        $this->printList(count($elements), $elements, 'skipped test');
    }

    private function printDetailsOnTestsThatTriggerPhpDeprecations(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpDeprecationEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestTriggeredPhpDeprecationEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredPhpDeprecationEvents()),
            'PHP deprecation'
        );
    }

    private function printDetailsOnTestsThatTriggerDeprecations(TestResult $result): void
    {
        if (!$result->hasTestTriggeredDeprecationEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestTriggeredDeprecationEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredDeprecationEvents()),
            'deprecation'
        );
    }

    private function printDetailsOnTestsThatTriggerErrors(TestResult $result): void
    {
        if (!$result->hasTestTriggeredErrorEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestTriggeredErrorEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredErrorEvents()),
            'error'
        );
    }

    private function printDetailsOnTestsThatTriggerPhpNotices(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpNoticeEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestTriggeredPhpNoticeEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredPhpNoticeEvents()),
            'PHP notice'
        );
    }

    private function printDetailsOnTestsThatTriggerNotices(TestResult $result): void
    {
        if (!$result->hasTestTriggeredNoticeEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestTriggeredNoticeEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredNoticeEvents()),
            'notice'
        );
    }

    private function printDetailsOnTestsThatTriggerPhpWarnings(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpWarningEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestTriggeredPhpWarningEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredPhpWarningEvents()),
            'PHP warning'
        );
    }

    private function printDetailsOnTestsThatTriggerWarnings(TestResult $result): void
    {
        if (!$result->hasTestTriggeredWarningEvents()) {
            return;
        }

        $this->printList(
            $result->numberOfTestsWithTestTriggeredWarningEvents(),
            $this->mapTestsWithIssuesEventsToElements($result->testTriggeredWarningEvents()),
            'warning'
        );
    }

    /**
     * @psalm-param list<array{title: string, body: string}> $elements
     */
    private function printList(int $count, array $elements, string $type): void
    {
        if ($this->listPrinted) {
            $this->printer->print("--\n\n");
        }

        $this->listPrinted = true;

        $this->printer->print(
            sprintf(
                "There %s %d %s%s:\n\n",
                ($count === 1) ? 'was' : 'were',
                $count,
                $type,
                ($count === 1) ? '' : 's'
            )
        );

        $i = 1;

        if ($this->displayDefectsInReverseOrder) {
            $elements = array_reverse($elements);
        }

        foreach ($elements as $element) {
            $this->printListElement($i++, $element['title'], $element['body']);
        }

        $this->printer->print("\n");
    }

    private function printListElement(int $number, string $title, string $body): void
    {
        $body = trim($body);

        $this->printer->print(
            sprintf(
                "%s%d) %s\n%s%s",
                $number > 1 ? "\n" : '',
                $number,
                $title,
                $body,
                !empty($body) ? "\n" : ''
            )
        );
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    private function name(Test $test): string
    {
        if ($test->isTestMethod()) {
            assert($test instanceof TestMethod);

            return $test->nameWithClass();
        }

        return $test->name();
    }

    private function location(Test $test): string
    {
        if (!$test->isTestMethod()) {
            return '';
        }
        assert($test instanceof TestMethod);

        return sprintf(
            '%s%s:%d%s',
            PHP_EOL,
            $test->file(),
            $test->line(),
            PHP_EOL
        );
    }

    /**
     * @psalm-param array<string,list<ConsideredRisky|DeprecationTriggered|PhpDeprecationTriggered|PhpunitDeprecationTriggered|ErrorTriggered|NoticeTriggered|PhpNoticeTriggered|WarningTriggered|PhpWarningTriggered|PhpunitErrorTriggered|PhpunitWarningTriggered>> $events
     *
     * @psalm-return list<array{title: string, body: string}>
     */
    private function mapTestsWithIssuesEventsToElements(array $events): array
    {
        $elements = [];

        foreach ($events as $reasons) {
            $test     = $reasons[0]->test();
            $title    = $this->name($test);
            $location = $this->location($test);

            if (count($reasons) === 1) {
                $body = trim($reasons[0]->message()) . PHP_EOL;
            } else {
                $body  = '';
                $first = true;

                foreach ($reasons as $reason) {
                    if ($first) {
                        $first = false;
                    } else {
                        $body .= PHP_EOL;
                    }

                    $lines = explode(PHP_EOL, trim($reason->message()));

                    $body .= '* ' . $lines[0] . PHP_EOL;

                    if (count($lines) > 1) {
                        foreach (range(1, count($lines) - 1) as $line) {
                            $body .= '  ' . $lines[$line] . PHP_EOL;
                        }
                    }
                }
            }

            if (!empty($location)) {
                $body .= $location;
            }

            $elements[] = [
                'title' => $title,
                'body'  => $body,
            ];
        }

        return $elements;
    }
}
