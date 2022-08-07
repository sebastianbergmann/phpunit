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
use SebastianBergmann\Timer\ResourceUsageFormatter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter
{
    private Printer $printer;
    private bool $colorizeOutput;
    private bool $displayDetailsOnIncompleteTests;
    private bool $displayDetailsOnSkippedTests;
    private bool $displayDefectsInReverseOrder;
    private bool $listPrinted = false;

    public function __construct(Printer $printer, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $colorizeOutput, bool $displayDefectsInReverseOrder)
    {
        $this->printer = $printer;

        $this->displayDetailsOnIncompleteTests = $displayDetailsOnIncompleteTests;
        $this->displayDetailsOnSkippedTests    = $displayDetailsOnSkippedTests;
        $this->colorizeOutput                  = $colorizeOutput;
        $this->displayDefectsInReverseOrder    = $displayDefectsInReverseOrder;
    }

    public function printResult(TestResult $result): void
    {
        $this->printHeader($result);
        $this->printTestsWithErrors($result);
        $this->printTestsWithWarnings($result);
        $this->printTestsWithFailedAssertions($result);
        $this->printRiskyTests($result);

        if ($this->displayDetailsOnIncompleteTests) {
            $this->printIncompleteTests($result);
        }

        if ($this->displayDetailsOnSkippedTests) {
            $this->printSkippedTests($result);
        }

        $this->printFooter($result);
    }

    public function flush(): void
    {
        $this->printer->flush();
    }

    private function printHeader(TestResult $result): void
    {
        if ($result->numberOfTestsRun() > 0) {
            $this->printer->print(PHP_EOL . PHP_EOL . (new ResourceUsageFormatter)->resourceUsageSinceStartOfRequest() . PHP_EOL . PHP_EOL);
        }
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

    private function printTestsWithWarnings(TestResult $result): void
    {
    }

    private function printRiskyTests(TestResult $result): void
    {
        if (!$result->hasTestConsideredRiskyEvents()) {
            return;
        }

        $elements = [];

        foreach ($result->testConsideredRiskyEvents() as $reasons) {
            foreach ($reasons as $reason) {
                $body = $reason->message() . PHP_EOL;
                $test = $reason->test();

                if ($test->isTestMethod()) {
                    assert($test instanceof TestMethod);

                    $body .= sprintf(
                        '%s%s:%d%s',
                        PHP_EOL,
                        $test->file(),
                        $test->line(),
                        PHP_EOL
                    );
                }

                $elements[] = [
                    'title' => $this->name($test),
                    'body'  => $body,
                ];
            }
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
        $this->printer->print(
            sprintf(
                "\n%d) %s\n%s\n",
                $number,
                $title,
                trim($body)
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
                'OK, but incomplete, skipped, or risky tests!'
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
            } elseif ($result->hasTestPassedWithWarningEvents()) {
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
        $this->printCountString($result->numberOfTestPassedWithWarningEvents(), 'Warnings', $color);
        $this->printCountString($result->numberOfTestSkippedEvents(), 'Skipped', $color);
        $this->printCountString($result->numberOfTestMarkedIncompleteEvents(), 'Incomplete', $color);
        $this->printCountString($result->numberOfTestsWithTestConsideredRiskyEvents(), 'Risky', $color);
        $this->printWithColor($color, '.');
    }

    private function printCountString(int $count, string $name, string $color, bool $always = false): void
    {
        static $first = true;

        if ($always || $count > 0) {
            $this->printWithColor(
                $color,
                sprintf(
                    '%s%s: %d',
                    !$first ? ', ' : '',
                    $name,
                    $count
                ),
                false
            );

            $first = false;
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
}
