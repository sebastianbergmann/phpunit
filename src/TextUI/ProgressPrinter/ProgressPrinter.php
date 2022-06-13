<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\ProgressPrinter;

use function str_contains;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Util\Color;
use PHPUnit\Util\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ProgressPrinter
{
    private Printer $printer;
    private bool $colors;
    private int $numberOfColumns;
    private int $column = 0;
    private int $numberOfTests;
    private int $numberOfTestsWidth;
    private int $maxColumn;
    private int $numberOfTestsRun = 0;
    private ?TestStatus $status   = null;
    private bool $prepared        = false;

    public function __construct(Printer $printer, bool $colors, int $numberOfColumns)
    {
        $this->printer = $printer;

        $this->colors          = $colors;
        $this->numberOfColumns = $numberOfColumns;

        $this->registerSubscribers();
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

    public function testMarkedIncomplete(): void
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

    public function testFinished(): void
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
        Facade::registerSubscriber(new TestMarkedIncompleteSubscriber($this));
        Facade::registerSubscriber(new TestSkippedSubscriber($this));
        Facade::registerSubscriber(new BeforeTestClassMethodErroredSubscriber($this));
    }

    private function updateTestStatus(TestStatus $status): void
    {
        if ($this->status !== null &&
            $this->status->isMoreImportantThan($status)) {
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
        if ($this->colors) {
            $progress = Color::colorizeTextBox($color, $progress);
        }

        $this->printProgress($progress);
    }

    private function printProgress(string $progress): void
    {
        $this->printer->print($progress);

        $this->column++;
        $this->numberOfTestsRun++;

        if ($this->column === $this->maxColumn || $this->numberOfTestsRun === $this->numberOfTests) {
            if ($this->numberOfTestsRun === $this->numberOfTests) {
                $this->printer->print(str_repeat(' ', $this->maxColumn - $this->column));
            }

            $this->printer->print(
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
                $this->printer->print("\n");
            }
        }
    }
}
