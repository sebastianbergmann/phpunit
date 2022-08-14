<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use const PHP_EOL;
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
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\Util\Color;
use PHPUnit\Util\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter
{
    private Printer $printer;
    private bool $colorizeOutput;
    private bool $displayDetailsOnIncompleteTests;
    private bool $displayDetailsOnSkippedTests;
    private bool $displayDetailsOnTestsThatTriggerDeprecations;
    private bool $displayDetailsOnTestsThatTriggerErrors;
    private bool $displayDetailsOnTestsThatTriggerNotices;
    private bool $displayDetailsOnTestsThatTriggerWarnings;
    private bool $displayDefectsInReverseOrder;
    private bool $listPrinted  = false;
    private bool $countPrinted = false;

    public function __construct(Printer $printer, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $displayDetailsOnTestsThatTriggerDeprecations, bool $displayDetailsOnTestsThatTriggerErrors, bool $displayDetailsOnTestsThatTriggerNotices, bool $displayDetailsOnTestsThatTriggerWarnings, bool $colorizeOutput, bool $displayDefectsInReverseOrder)
    {
        $this->printer                                      = $printer;
        $this->displayDetailsOnIncompleteTests              = $displayDetailsOnIncompleteTests;
        $this->displayDetailsOnSkippedTests                 = $displayDetailsOnSkippedTests;
        $this->displayDetailsOnTestsThatTriggerDeprecations = $displayDetailsOnTestsThatTriggerDeprecations;
        $this->displayDetailsOnTestsThatTriggerErrors       = $displayDetailsOnTestsThatTriggerErrors;
        $this->displayDetailsOnTestsThatTriggerNotices      = $displayDetailsOnTestsThatTriggerNotices;
        $this->displayDetailsOnTestsThatTriggerWarnings     = $displayDetailsOnTestsThatTriggerWarnings;
        $this->colorizeOutput                               = $colorizeOutput;
        $this->displayDefectsInReverseOrder                 = $displayDefectsInReverseOrder;
    }

    public function printResult(TestResult $result): void
    {
        $this->printTestRunnerWarnings($result);
        $this->printTestWarnings($result);
        $this->printTestsWithErrors($result);
        $this->printTestsWithFailedAssertions($result);
        $this->printRiskyTests($result);
        $this->printPhpunitDeprecations($result);

        if ($this->displayDetailsOnIncompleteTests) {
            $this->printIncompleteTests($result);
        }

        if ($this->displayDetailsOnSkippedTests) {
            $this->printSkippedTests($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerDeprecations) {
            $this->printDetailsOnTestsThatTriggerDeprecations($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerErrors) {
            $this->printDetailsOnTestsThatTriggerErrors($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerNotices) {
            $this->printDetailsOnTestsThatTriggerNotices($result);
        }

        if ($this->displayDetailsOnTestsThatTriggerWarnings) {
            $this->printDetailsOnTestsThatTriggerWarnings($result);
        }

        $this->printFooter($result);
    }

    public function flush(): void
    {
        $this->printer->flush();
    }

    private function printTestRunnerWarnings(TestResult $result): void
    {
        if (!$result->hasTestRunnerTriggeredWarningEvents()) {
            return;
        }

        $elements = [];

        foreach ($result->testRunnerTriggeredWarningEvents() as $event) {
            $elements[] = [
                'title' => $event->message(),
                'body'  => '',
            ];
        }

        $this->printList(count($elements), $elements, 'test runner warning');
    }

    private function printTestWarnings(TestResult $result): void
    {
    }

    private function printPhpunitDeprecations(TestResult $result): void
    {
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

        $elements = [];

        foreach ($result->testConsideredRiskyEvents() as $reasons) {
            $test     = $reasons[0]->test();
            $title    = $this->name($test);
            $location = $this->location($test);

            if (count($reasons) === 1) {
                $body = $reasons[0]->message() . PHP_EOL;
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

        $this->printList($result->numberOfTestsWithTestConsideredRiskyEvents(), $elements, 'risky test');
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

    private function printDetailsOnTestsThatTriggerDeprecations(TestResult $result): void
    {
    }

    private function printDetailsOnTestsThatTriggerErrors(TestResult $result): void
    {
    }

    private function printDetailsOnTestsThatTriggerNotices(TestResult $result): void
    {
    }

    private function printDetailsOnTestsThatTriggerWarnings(TestResult $result): void
    {
    }

    /**
     * @psalm-param list<array{title: string, body: string}> $elements
     */
    private function printList(int $count, array $elements, string $type): void
    {
        if ($this->listPrinted) {
            $this->printer->print("\n--\n\n");
        }

        $this->listPrinted = true;

        $this->printer->print(
            sprintf(
                "There %s %d %s%s:\n",
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
    }

    private function printListElement(int $number, string $title, string $body): void
    {
        $body = trim($body);

        if (!empty($body)) {
            $body .= "\n";
        }

        $this->printer->print(
            sprintf(
                "\n%d) %s\n%s",
                $number,
                $title,
                $body
            )
        );
    }

    private function printFooter(TestResult $result): void
    {
        if ($result->numberOfTestsRun() === 0) {
            $this->printWithColor(
                'fg-black, bg-yellow',
                'No tests executed!'
            );

            return;
        }

        if ($result->wasSuccessfulAndNoTestIsRiskyOrSkippedOrIncomplete()) {
            $this->printWithColor(
                'fg-black, bg-green',
                sprintf(
                    'OK (%d test%s, %d assertion%s)',
                    $result->numberOfTestsRun(),
                    $result->numberOfTestsRun() === 1 ? '' : 's',
                    $result->numberOfAssertions(),
                    $result->numberOfAssertions() === 1 ? '' : 's'
                )
            );

            return;
        }

        $color = 'fg-black, bg-yellow';

        if ($result->wasSuccessful()) {
            if ($this->displayDetailsOnIncompleteTests || $this->displayDetailsOnSkippedTests || $result->hasTestConsideredRiskyEvents()) {
                $this->printer->print("\n");
            }

            $this->printWithColor(
                $color,
                'OK, but some tests have issues!'
            );
        } else {
            $this->printer->print("\n");

            if ($result->hasTestErroredEvents()) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'ERRORS!'
                );
            } elseif ($result->hasTestFailedEvents()) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'FAILURES!'
                );
            } elseif ($result->hasWarningEvents()) {
                $this->printWithColor(
                    $color,
                    'WARNINGS!'
                );
            }
        }

        $this->printCountString($result->numberOfTestsRun(), 'Tests', $color, true);
        $this->printCountString($result->numberOfAssertions(), 'Assertions', $color, true);
        $this->printCountString($result->numberOfTestErroredEvents(), 'Errors', $color);
        $this->printCountString($result->numberOfTestFailedEvents(), 'Failures', $color);
        $this->printCountString($result->numberOfWarningEvents(), 'Warnings', $color);
        $this->printCountString($result->numberOfTestSkippedEvents(), 'Skipped', $color);
        $this->printCountString($result->numberOfTestMarkedIncompleteEvents(), 'Incomplete', $color);
        $this->printCountString($result->numberOfTestsWithTestConsideredRiskyEvents(), 'Risky', $color);
        $this->printWithColor($color, '.');
    }

    private function printCountString(int $count, string $name, string $color, bool $always = false): void
    {
        if ($always || $count > 0) {
            $this->printWithColor(
                $color,
                sprintf(
                    '%s%s: %d',
                    $this->countPrinted ? ', ' : '',
                    $name,
                    $count
                ),
                false
            );

            $this->countPrinted = true;
        }
    }

    private function printWithColor(string $color, string $buffer, bool $lf = true): void
    {
        if ($this->colorizeOutput) {
            $buffer = Color::colorizeTextBox($color, $buffer);
        }

        $this->printer->print($buffer);

        if ($lf) {
            $this->printer->print(PHP_EOL);
        }
    }

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
}
