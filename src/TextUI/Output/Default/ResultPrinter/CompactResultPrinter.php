<?php
declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Default\ResultPrinter;

use function array_key_exists;
use function array_reverse;
use function assert;
use function count;
use function sprintf;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitNoticeTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Output\Printer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CompactResultPrinter
{
    private readonly Printer $printer;
    private readonly bool $displayPhpunitErrors;
    private readonly bool $displayTestsWithErrors;
    private readonly bool $displayTestsWithFailedAssertions;
    private readonly bool $displayDefectsInReverseOrder;
    private bool $listPrinted = false;

    public function __construct(
        Printer $printer,
        bool $displayPhpunitErrors,
        bool $displayTestsWithErrors,
        bool $displayTestsWithFailedAssertions,
        bool $displayDefectsInReverseOrder
    ) {
        $this->printer                          = $printer;
        $this->displayPhpunitErrors             = $displayPhpunitErrors;
        $this->displayTestsWithErrors           = $displayTestsWithErrors;
        $this->displayTestsWithFailedAssertions = $displayTestsWithFailedAssertions;
        $this->displayDefectsInReverseOrder     = $displayDefectsInReverseOrder;
    }

    public function print(TestResult $result, bool $stackTraceForDeprecations = false): void
    {
        if ($this->displayPhpunitErrors) {
            $this->printPhpunitErrors($result);
        }

        if ($this->displayTestsWithErrors) {
            $this->printTestsWithErrors($result);
        }

        if ($this->displayTestsWithFailedAssertions) {
            $this->printTestsWithFailedAssertions($result);
        }
    }

    private function printPhpunitErrors(TestResult $result): void
    {
        if (!$result->hasTestTriggeredPhpunitErrorEvents()) {
            return;
        }

        $elements = $this->mapTestsWithIssuesEventsToElements($result->testTriggeredPhpunitErrorEvents());

        $this->printListHeaderWithNumber($elements['numberOfTestsWithIssues'], 'PHPUnit error');
        $this->printList($elements['elements']);
    }

    private function printTestsWithErrors(TestResult $result): void
    {
        if (!$result->hasTestErroredEvents()) {
            return;
        }

        $elements        = [];
        $processedByName = [];

        foreach ($result->testErroredEvents() as $event) {
            if ($event instanceof AfterLastTestMethodErrored || $event instanceof BeforeFirstTestMethodErrored) {
                $title = $event->testClassName();
            } else {
                $title = $this->name($event->test());
            }

            if (array_key_exists($title, $processedByName)) {
                continue;
            }
            $processedByName[$title] = true;
            $elements[]              = [
                'title' => $title,
            ];
        }

        $this->printListHeaderWithNumber(count($elements), 'error');
        $this->printList($elements);
    }

    private function printTestsWithFailedAssertions(TestResult $result): void
    {
        if (!$result->hasTestFailedEvents()) {
            return;
        }

        $elements        = [];
        $processedByName = [];

        foreach ($result->testFailedEvents() as $event) {
            $title = $this->name($event->test());

            if (array_key_exists($title, $processedByName)) {
                continue;
            }
            $processedByName[$title] = true;
            $elements[]              = [
                'title' => $this->name($event->test()),
            ];
        }

        $this->printListHeaderWithNumber(count($elements), 'failure');
        $this->printList($elements);
    }

    private function printListHeaderWithNumber(int $number, string $type): void
    {
        $this->printListHeader(
            sprintf(
                "There %s %d %s%s:\n\n",
                ($number === 1) ? 'was' : 'were',
                $number,
                $type,
                ($number === 1) ? '' : 's',
            ),
        );
    }

    private function printListHeader(string $header): void
    {
        if ($this->listPrinted) {
            $this->printer->print("--\n\n");
        }

        $this->listPrinted = true;

        $this->printer->print($header);
    }

    /**
     * @param list<array{title: string}> $elements
     */
    private function printList(array $elements): void
    {
        $i = 1;

        if ($this->displayDefectsInReverseOrder) {
            $elements = array_reverse($elements);
        }

        foreach ($elements as $element) {
            $this->printListElement($i++, $element['title']);
        }

        $this->printer->print("\n");
    }

    private function printListElement(int $number, string $title): void
    {
        $this->printer->print(
            sprintf(
                "%s%d) %s\n",
                $number > 1 ? "\n" : '',
                $number,
                $title,
            ),
        );
    }

    private function name(Test $test): string
    {
        if ($test->isTestMethod()) {
            assert($test instanceof TestMethod);

            return $test->className();
        }

        return $test->name();
    }

    /**
     * @param array<string,list<ConsideredRisky|DeprecationTriggered|ErrorTriggered|NoticeTriggered|PhpDeprecationTriggered|PhpNoticeTriggered|PhpunitDeprecationTriggered|PhpunitErrorTriggered|PhpunitNoticeTriggered|PhpunitWarningTriggered|PhpWarningTriggered|WarningTriggered>> $events
     *
     * @return array{numberOfTestsWithIssues: int, numberOfIssues: int, elements: list<array{title: string}>}
     */
    private function mapTestsWithIssuesEventsToElements(array $events): array
    {
        $elements              = [];
        $issues                = 0;
        $processedTestsByTitle = [];

        foreach ($events as $reasons) {
            $test  = $reasons[0]->test();
            $title = $this->name($test);

            $issues += count($reasons);

            if (array_key_exists($title, $processedTestsByTitle)) {
                continue;
            }
            $processedTestsByTitle[$title] = true;
            $elements[]                    = [
                'title' => $title,
            ];
        }

        return [
            'numberOfTestsWithIssues' => count($events),
            'numberOfIssues'          => $issues,
            'elements'                => $elements,
        ];
    }
}
