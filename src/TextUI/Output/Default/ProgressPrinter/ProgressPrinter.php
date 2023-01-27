<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Default\ProgressPrinter;

use function floor;
use function sprintf;
use function str_contains;
use function str_repeat;
use function strlen;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\TextUI\Output\Printer;
use PHPUnit\Util\Color;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ProgressPrinter
{
    private readonly Printer $printer;
    private readonly bool $colors;
    private readonly int $numberOfColumns;
    private int $column             = 0;
    private int $numberOfTests      = 0;
    private int $numberOfTestsWidth = 0;
    private int $maxColumn          = 0;
    private int $numberOfTestsRun   = 0;
    private ?TestStatus $status     = null;
    private bool $prepared          = false;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct(Printer $printer, bool $colors, int $numberOfColumns)
    {
        $this->printer         = $printer;
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

    public function testTriggeredNotice(): void
    {
        $this->updateTestStatus(TestStatus::notice());
    }

    public function testTriggeredDeprecation(): void
    {
        $this->updateTestStatus(TestStatus::deprecation());
    }

    public function testConsideredRisky(): void
    {
        $this->updateTestStatus(TestStatus::risky());
    }

    public function testTriggeredWarning(): void
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
        } elseif ($this->status->isNotice()) {
            $this->printProgressForNotice();
        } elseif ($this->status->isDeprecation()) {
            $this->printProgressForDeprecation();
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
        Facade::registerSubscribers(
            new BeforeTestClassMethodErroredSubscriber($this),
            new TestConsideredRiskySubscriber($this),
            new TestErroredSubscriber($this),
            new TestFailedSubscriber($this),
            new TestFinishedSubscriber($this),
            new TestMarkedIncompleteSubscriber($this),
            new TestPreparedSubscriber($this),
            new TestRunnerExecutionStartedSubscriber($this),
            new TestSkippedSubscriber($this),
            new TestTriggeredDeprecationSubscriber($this),
            new TestTriggeredNoticeSubscriber($this),
            new TestTriggeredPhpDeprecationSubscriber($this),
            new TestTriggeredPhpNoticeSubscriber($this),
            new TestTriggeredPhpunitDeprecationSubscriber($this),
            new TestTriggeredPhpunitWarningSubscriber($this),
            new TestTriggeredPhpWarningSubscriber($this),
            new TestTriggeredWarningSubscriber($this),
        );
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

    private function printProgressForNotice(): void
    {
        $this->printProgressWithColor('fg-yellow, bold', 'N');
    }

    private function printProgressForDeprecation(): void
    {
        $this->printProgressWithColor('fg-yellow, bold', 'D');
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
