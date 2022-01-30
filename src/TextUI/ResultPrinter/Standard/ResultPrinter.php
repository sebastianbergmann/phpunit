<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\ResultPrinter\Standard;

use const PHP_EOL;
use function implode;
use function max;
use function preg_split;
use function str_pad;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\RiskyTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\ResultPrinter\ResultPrinter as ResultPrinterInterface;
use PHPUnit\Util\Color;
use PHPUnit\Util\Printer;
use ReflectionMethod;
use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter extends Printer implements ResultPrinterInterface
{
    private bool $colors;
    private bool $verbose;
    private int $numberOfColumns;
    private bool $reverse;
    private int $column = 0;
    private int $numberOfTests;
    private int $numberOfTestsWidth;
    private int $maxColumn;
    private int $numberOfTestsRun   = 0;
    private bool $progressWritten   = false;
    private bool $defectListPrinted = false;
    private Timer $timer;
    private int $numberOfAssertions = 0;

    public function __construct(string $out, bool $verbose, bool $colors, int $numberOfColumns, bool $reverse)
    {
        parent::__construct($out);

        $this->verbose         = $verbose;
        $this->colors          = $colors;
        $this->numberOfColumns = $numberOfColumns;
        $this->reverse         = $reverse;

        $this->registerSubscribers();

        $this->timer = new Timer;

        $this->timer->start();
    }

    public function printResult(TestResult $result): void
    {
        $this->printHeader($result);
        $this->printTestsWithErrors($result);
        $this->printTestsWithWarnings($result);
        $this->printTestsWithFailedAssertions($result);
        $this->printRiskyTests($result);

        if ($this->verbose) {
            $this->printIncompleteTests($result);
            $this->printSkippedTests($result);
        }

        $this->printFooter($result);
    }

    public function testRunnerExecutionStarted(ExecutionStarted $event): void
    {
        $this->numberOfTests      = $event->testSuite()->count();
        $this->numberOfTestsWidth = strlen((string) $this->numberOfTests);
        $this->maxColumn          = $this->numberOfColumns - strlen('  /  (XXX%)') - (2 * $this->numberOfTestsWidth);
    }

    public function testAborted(): void
    {
        $this->writeProgressWithColor('fg-yellow, bold', 'I');
    }

    public function testConsideredRisky(): void
    {
        $this->writeProgressWithColor('fg-yellow, bold', 'R');
    }

    public function testErrored(): void
    {
        $this->writeProgressWithColor('fg-red, bold', 'E');
    }

    public function testFailed(): void
    {
        $this->writeProgressWithColor('bg-red, fg-white', 'F');
    }

    public function testFinished(Finished $event): void
    {
        $this->writeProgress('.');

        $this->progressWritten = false;

        $this->numberOfAssertions += $event->numberOfAssertionsPerformed();
    }

    public function testPassedWithWarning(): void
    {
        $this->writeProgressWithColor('fg-yellow, bold', 'W');
    }

    public function testSkipped(): void
    {
        $this->writeProgressWithColor('fg-cyan, bold', 'S');
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(): void
    {
        Facade::registerSubscriber(new TestRunnerExecutionStartedSubscriber($this));
        Facade::registerSubscriber(new TestFinishedSubscriber($this));
        Facade::registerSubscriber(new TestConsideredRiskySubscriber($this));
        Facade::registerSubscriber(new TestPassedWithWarningSubscriber($this));
        Facade::registerSubscriber(new TestErroredSubscriber($this));
        Facade::registerSubscriber(new TestFailedSubscriber($this));
        Facade::registerSubscriber(new TestAbortedSubscriber($this));
        Facade::registerSubscriber(new TestSkippedSubscriber($this));
    }

    private function writeProgressWithColor(string $color, string $progress): void
    {
        $progress = $this->colorizeTextBox($color, $progress);

        $this->writeProgress($progress);
    }

    private function writeProgress(string $progress): void
    {
        if ($this->progressWritten) {
            return;
        }

        $this->write($progress);

        $this->progressWritten = true;

        $this->column++;
        $this->numberOfTestsRun++;

        if ($this->column === $this->maxColumn || $this->numberOfTestsRun === $this->numberOfTests) {
            if ($this->numberOfTestsRun === $this->numberOfTests) {
                $this->write(str_repeat(' ', $this->maxColumn - $this->column));
            }

            $this->write(
                sprintf(
                    ' %' . $this->numberOfTestsWidth . 'd / %' .
                    $this->numberOfTestsWidth . 'd (%3s%%)',
                    $this->numberOfTestsRun,
                    $this->numberOfTests,
                    floor(($this->numberOfTestsRun / $this->numberOfTests) * 100)
                )
            );

            if ($this->column === $this->maxColumn) {
                $this->column = 0;
                $this->write("\n");
            }
        }
    }

    private function printHeader(TestResult $result): void
    {
        if (count($result) > 0) {
            $this->write(PHP_EOL . PHP_EOL . (new ResourceUsageFormatter)->resourceUsage($this->timer->stop()) . PHP_EOL . PHP_EOL);
        }
    }

    private function printTestsWithErrors(TestResult $result): void
    {
        $this->printDefects($result->errors(), 'error');
    }

    private function printTestsWithFailedAssertions(TestResult $result): void
    {
        $this->printDefects($result->failures(), 'failure');
    }

    private function printTestsWithWarnings(TestResult $result): void
    {
        $this->printDefects($result->warnings(), 'warning');
    }

    private function printRiskyTests(TestResult $result): void
    {
        $this->printDefects($result->risky(), 'risky test');
    }

    private function printIncompleteTests(TestResult $result): void
    {
        $this->printDefects($result->notImplemented(), 'incomplete test');
    }

    private function printSkippedTests(TestResult $result): void
    {
        $this->printDefects($result->skipped(), 'skipped test');
    }

    private function printDefects(array $defects, string $type): void
    {
        $count = count($defects);

        if ($count === 0) {
            return;
        }

        if ($this->defectListPrinted) {
            $this->write("\n--\n\n");
        }

        $this->write(
            sprintf(
                "There %s %d %s%s:\n",
                ($count === 1) ? 'was' : 'were',
                $count,
                $type,
                ($count === 1) ? '' : 's'
            )
        );

        $i = 1;

        if ($this->reverse) {
            $defects = array_reverse($defects);
        }

        foreach ($defects as $defect) {
            $this->printDefect($defect, $i++);
        }

        $this->defectListPrinted = true;
    }

    private function printDefect(TestFailure $defect, int $count): void
    {
        $this->printDefectHeader($defect, $count);
        $this->printDefectTrace($defect);
    }

    private function printDefectHeader(TestFailure $defect, int $count): void
    {
        $this->write(
            sprintf(
                "\n%d) %s\n",
                $count,
                $defect->getTestName()
            )
        );
    }

    private function printDefectTrace(TestFailure $defect): void
    {
        $e = $defect->thrownException();

        $this->write((string) $e);

        if ($defect->thrownException() instanceof RiskyTest) {
            $test = $defect->failedTest();

            assert($test instanceof TestCase);

            $reflector = new ReflectionMethod($test::class, $test->getName(false));

            $this->write(
                sprintf(
                    '%s%s:%d%s',
                    PHP_EOL,
                    $reflector->getFileName(),
                    $reflector->getStartLine(),
                    PHP_EOL
                )
            );
        } else {
            while ($e = $e->getPrevious()) {
                $this->write("\nCaused by\n" . $e);
            }
        }
    }

    private function printFooter(TestResult $result): void
    {
        if (count($result) === 0) {
            $this->writeWithColor(
                'fg-black, bg-yellow',
                'No tests executed!'
            );

            return;
        }

        if ($result->wasSuccessfulAndNoTestIsRiskyOrSkippedOrIncomplete()) {
            $this->writeWithColor(
                'fg-black, bg-green',
                sprintf(
                    'OK (%d test%s, %d assertion%s)',
                    count($result),
                    (count($result) === 1) ? '' : 's',
                    $this->numberOfAssertions,
                    ($this->numberOfAssertions === 1) ? '' : 's'
                )
            );

            return;
        }

        $color = 'fg-black, bg-yellow';

        if ($result->wasSuccessful()) {
            if ($this->verbose || !$result->allHarmless()) {
                $this->write("\n");
            }

            $this->writeWithColor(
                $color,
                'OK, but incomplete, skipped, or risky tests!'
            );
        } else {
            $this->write("\n");

            if ($result->errorCount()) {
                $color = 'fg-white, bg-red';

                $this->writeWithColor(
                    $color,
                    'ERRORS!'
                );
            } elseif ($result->failureCount()) {
                $color = 'fg-white, bg-red';

                $this->writeWithColor(
                    $color,
                    'FAILURES!'
                );
            } elseif ($result->warningCount()) {
                $color = 'fg-black, bg-yellow';

                $this->writeWithColor(
                    $color,
                    'WARNINGS!'
                );
            }
        }

        $this->writeCountString(count($result), 'Tests', $color, true);
        $this->writeCountString($this->numberOfAssertions, 'Assertions', $color, true);
        $this->writeCountString($result->errorCount(), 'Errors', $color);
        $this->writeCountString($result->failureCount(), 'Failures', $color);
        $this->writeCountString($result->warningCount(), 'Warnings', $color);
        $this->writeCountString($result->skippedCount(), 'Skipped', $color);
        $this->writeCountString($result->notImplementedCount(), 'Incomplete', $color);
        $this->writeCountString($result->riskyCount(), 'Risky', $color);
        $this->writeWithColor($color, '.');
    }

    private function writeCountString(int $count, string $name, string $color, bool $always = false): void
    {
        static $first = true;

        if ($always || $count > 0) {
            $this->writeWithColor(
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

    private function writeWithColor(string $color, string $buffer, bool $lf = true): void
    {
        $this->write($this->colorizeTextBox($color, $buffer));

        if ($lf) {
            $this->write(PHP_EOL);
        }
    }

    private function colorizeTextBox(string $color, string $buffer): string
    {
        if (!$this->colors) {
            return $buffer;
        }

        $lines   = preg_split('/\r\n|\r|\n/', $buffer);
        $padding = max(array_map('\strlen', $lines));

        $styledLines = [];

        foreach ($lines as $line) {
            $styledLines[] = Color::colorize($color, str_pad($line, $padding));
        }

        return implode(PHP_EOL, $styledLines);
    }
}
