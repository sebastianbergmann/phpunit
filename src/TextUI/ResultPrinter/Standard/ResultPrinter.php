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
use function str_contains;
use function str_pad;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\RiskyTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestStatus\TestStatus;
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
    private bool $displayDetailsOnIncompleteTests;
    private bool $displayDetailsOnSkippedTests;
    private int $numberOfColumns;
    private bool $reverse;
    private int $column = 0;
    private int $numberOfTests;
    private int $numberOfTestsWidth;
    private int $maxColumn;
    private int $numberOfTestsRun   = 0;
    private bool $defectListPrinted = false;
    private Timer $timer;
    private int $numberOfAssertions = 0;
    private ?TestStatus $status     = null;
    private bool $prepared          = false;

    public function __construct(string $out, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $colors, int $numberOfColumns, bool $reverse)
    {
        parent::__construct($out);

        $this->displayDetailsOnIncompleteTests = $displayDetailsOnIncompleteTests;
        $this->displayDetailsOnSkippedTests    = $displayDetailsOnSkippedTests;
        $this->colors                          = $colors;
        $this->numberOfColumns                 = $numberOfColumns;
        $this->reverse                         = $reverse;

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

        if ($this->displayDetailsOnIncompleteTests) {
            $this->printIncompleteTests($result);
        }

        if ($this->displayDetailsOnSkippedTests) {
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

    public function beforeTestClassMethodErrored(): void
    {
        $this->printProgressForError();
        $this->updateTestStatus(TestStatus::error());
    }

    public function testPrepared(): void
    {
        $this->prepared = true;
    }

    public function testSkipped(): void
    {
        if (!$this->prepared) {
            $this->printProgressForSkipped();
        } else {
            $this->updateTestStatus(TestStatus::skipped());
        }
    }

    public function testAborted(): void
    {
        $this->updateTestStatus(TestStatus::incomplete());
    }

    public function testConsideredRisky(): void
    {
        $this->updateTestStatus(TestStatus::risky());
    }

    public function testPassedWithWarning(): void
    {
        $this->updateTestStatus(TestStatus::warning());
    }

    public function testFailed(): void
    {
        $this->updateTestStatus(TestStatus::failure());
    }

    public function testErrored(Errored $event): void
    {
        /*
         * @todo Eliminate this special case
         */
        if (str_contains($event->asString(), 'Test was run in child process and ended unexpectedly')) {
            $this->updateTestStatus(TestStatus::error());

            return;
        }

        if (!$this->prepared) {
            $this->printProgressForError();
        } else {
            $this->updateTestStatus(TestStatus::error());
        }
    }

    public function testFinished(Finished $event): void
    {
        if ($this->status === null) {
            $this->printProgressForSuccess();
        } elseif ($this->status->isSkipped()) {
            $this->printProgressForSkipped();
        } elseif ($this->status->isIncomplete()) {
            $this->printProgressForIncomplete();
        } elseif ($this->status->isRisky()) {
            $this->printProgressForRisky();
        } elseif ($this->status->isWarning()) {
            $this->printProgressForWarning();
        } elseif ($this->status->isFailure()) {
            $this->printProgressForFailure();
        } else {
            $this->printProgressForError();
        }

        $this->numberOfAssertions += $event->numberOfAssertionsPerformed();

        $this->status   = null;
        $this->prepared = false;
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(): void
    {
        Facade::registerSubscriber(new TestRunnerExecutionStartedSubscriber($this));
        Facade::registerSubscriber(new TestPreparedSubscriber($this));
        Facade::registerSubscriber(new TestFinishedSubscriber($this));
        Facade::registerSubscriber(new TestConsideredRiskySubscriber($this));
        Facade::registerSubscriber(new TestPassedWithWarningSubscriber($this));
        Facade::registerSubscriber(new TestErroredSubscriber($this));
        Facade::registerSubscriber(new TestFailedSubscriber($this));
        Facade::registerSubscriber(new TestAbortedSubscriber($this));
        Facade::registerSubscriber(new TestSkippedSubscriber($this));
        Facade::registerSubscriber(new BeforeTestClassMethodErroredSubscriber($this));
    }

    private function updateTestStatus(TestStatus $status): void
    {
        if ($this->status === null) {
            $this->status = $status;

            return;
        }

        if ($this->status->isMoreImportantThan($status)) {
            return;
        }

        $this->status = $status;
    }

    private function printProgressForSuccess(): void
    {
        $this->printProgress('.');
    }

    private function printProgressForSkipped(): void
    {
        $this->printProgressWithColor('fg-cyan, bold', 'S');
    }

    private function printProgressForIncomplete(): void
    {
        $this->printProgressWithColor('fg-yellow, bold', 'I');
    }

    private function printProgressForRisky(): void
    {
        $this->printProgressWithColor('fg-yellow, bold', 'R');
    }

    private function printProgressForWarning(): void
    {
        $this->printProgressWithColor('fg-yellow, bold', 'W');
    }

    private function printProgressForFailure(): void
    {
        $this->printProgressWithColor('bg-red, fg-white', 'F');
    }

    private function printProgressForError(): void
    {
        $this->printProgressWithColor('fg-red, bold', 'E');
    }

    private function printProgressWithColor(string $color, string $progress): void
    {
        $progress = $this->colorizeTextBox($color, $progress);

        $this->printProgress($progress);
    }

    private function printProgress(string $progress): void
    {
        $this->print($progress);

        $this->column++;
        $this->numberOfTestsRun++;

        if ($this->column === $this->maxColumn || $this->numberOfTestsRun === $this->numberOfTests) {
            if ($this->numberOfTestsRun === $this->numberOfTests) {
                $this->print(str_repeat(' ', $this->maxColumn - $this->column));
            }

            $this->print(
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
                $this->print("\n");
            }
        }
    }

    private function printHeader(TestResult $result): void
    {
        if (count($result) > 0) {
            $this->print(PHP_EOL . PHP_EOL . (new ResourceUsageFormatter)->resourceUsage($this->timer->stop()) . PHP_EOL . PHP_EOL);
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
            $this->print("\n--\n\n");
        }

        $this->print(
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
        $this->print(
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

        $this->print((string) $e);

        if ($defect->thrownException() instanceof RiskyTest) {
            $test = $defect->failedTest();

            assert($test instanceof TestCase);

            /** @noinspection PhpUnhandledExceptionInspection */
            $reflector = new ReflectionMethod($test::class, $test->getName(false));

            $this->print(
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
                $this->print("\nCaused by\n" . $e);
            }
        }
    }

    private function printFooter(TestResult $result): void
    {
        if (count($result) === 0) {
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
            if ($this->displayDetailsOnIncompleteTests || $this->displayDetailsOnSkippedTests || !$result->allHarmless()) {
                $this->print("\n");
            }

            $this->printWithColor(
                $color,
                'OK, but incomplete, skipped, or risky tests!'
            );
        } else {
            $this->print("\n");

            if ($result->errorCount()) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'ERRORS!'
                );
            } elseif ($result->failureCount()) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'FAILURES!'
                );
            } elseif ($result->warningCount()) {
                $this->printWithColor(
                    $color,
                    'WARNINGS!'
                );
            }
        }

        $this->printCountString(count($result), 'Tests', $color, true);
        $this->printCountString($this->numberOfAssertions, 'Assertions', $color, true);
        $this->printCountString($result->errorCount(), 'Errors', $color);
        $this->printCountString($result->failureCount(), 'Failures', $color);
        $this->printCountString($result->warningCount(), 'Warnings', $color);
        $this->printCountString($result->skippedCount(), 'Skipped', $color);
        $this->printCountString($result->notImplementedCount(), 'Incomplete', $color);
        $this->printCountString($result->riskyCount(), 'Risky', $color);
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
        $this->print($this->colorizeTextBox($color, $buffer));

        if ($lf) {
            $this->print(PHP_EOL);
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
