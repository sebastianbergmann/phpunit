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
use PHPUnit\Event\TestSuite\Filtered;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\ResultPrinter\ResultPrinter as ResultPrinterInterface;
use PHPUnit\Util\Color;
use PHPUnit\Util\Printer;

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

    private int $numberOfTestsRun = 0;

    private bool $progressWritten = false;

    public function __construct(string $out, bool $verbose, bool $colors, int $numberOfColumns, bool $reverse)
    {
        parent::__construct($out);

        $this->verbose         = $verbose;
        $this->colors          = $colors;
        $this->numberOfColumns = $numberOfColumns;
        $this->reverse         = $reverse;

        $this->registerSubscribers();
    }

    public function printResult(TestResult $result): void
    {
    }

    public function testSuiteFiltered(Filtered $event): void
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

    public function testFinished(): void
    {
        $this->writeProgress('.');

        $this->progressWritten = false;
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
        Facade::registerSubscriber(new TestSuiteFilteredSubscriber($this));
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
