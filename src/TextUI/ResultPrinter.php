<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\TextUI;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Util\InvalidArgumentHelper;
use PHPUnit\Util\Printer;
use SebastianBergmann\Environment\Console;
use SebastianBergmann\Timer\Timer;

/**
 * Prints the result of a TextUI TestRunner run.
 */
class ResultPrinter extends Printer implements TestListener
{
    public const EVENT_TEST_START      = 0;
    public const EVENT_TEST_END        = 1;
    public const EVENT_TESTSUITE_START = 2;
    public const EVENT_TESTSUITE_END   = 3;

    public const COLOR_NEVER   = 'never';
    public const COLOR_AUTO    = 'auto';
    public const COLOR_ALWAYS  = 'always';
    public const COLOR_DEFAULT = self::COLOR_NEVER;

    /**
     * @var int
     */
    protected $column = 0;

    /**
     * @var int
     */
    protected $maxColumn;

    /**
     * @var bool
     */
    protected $lastTestFailed = false;

    /**
     * @var int
     */
    protected $numAssertions = 0;

    /**
     * @var int
     */
    protected $numTests = -1;

    /**
     * @var int
     */
    protected $numTestsRun = 0;

    /**
     * @var int
     */
    protected $numTestsWidth;

    /**
     * @var bool
     */
    protected $colors = false;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var bool
     */
    protected $verbose = false;

    /**
     * @var array
     */
    private static $ansiCodes = [
        'bold'       => 1,
        'fg-black'   => 30,
        'fg-red'     => 31,
        'fg-green'   => 32,
        'fg-yellow'  => 33,
        'fg-blue'    => 34,
        'fg-magenta' => 35,
        'fg-cyan'    => 36,
        'fg-white'   => 37,
        'bg-black'   => 40,
        'bg-red'     => 41,
        'bg-green'   => 42,
        'bg-yellow'  => 43,
        'bg-blue'    => 44,
        'bg-magenta' => 45,
        'bg-cyan'    => 46,
        'bg-white'   => 47
    ];

    /**
     * @var int
     */
    private $numberOfColumns;

    /**
     * @var bool
     */
    private $reverse;

    /**
     * @var bool
     */
    private $defectListPrinted = false;

    /**
     * Constructor.
     *
     * @param mixed      $out
     * @param bool       $verbose
     * @param string     $colors
     * @param bool       $debug
     * @param int|string $numberOfColumns
     * @param bool       $reverse
     *
     * @throws Exception
     */
    public function __construct($out = null, bool $verbose = false, $colors = self::COLOR_DEFAULT, bool $debug = false, $numberOfColumns = 80, bool $reverse = false)
    {
        parent::__construct($out);

        $availableColors = [self::COLOR_NEVER, self::COLOR_AUTO, self::COLOR_ALWAYS];

        if (!\in_array($colors, $availableColors)) {
            throw InvalidArgumentHelper::factory(
                3,
                \vsprintf('value from "%s", "%s" or "%s"', $availableColors)
            );
        }

        if (!\is_int($numberOfColumns) && $numberOfColumns !== 'max') {
            throw InvalidArgumentHelper::factory(5, 'integer or "max"');
        }

        $console            = new Console;
        $maxNumberOfColumns = $console->getNumberOfColumns();

        if ($numberOfColumns === 'max' || ($numberOfColumns !== 80 && $numberOfColumns > $maxNumberOfColumns)) {
            $numberOfColumns = $maxNumberOfColumns;
        }

        $this->numberOfColumns = $numberOfColumns;
        $this->verbose         = $verbose;
        $this->debug           = $debug;
        $this->reverse         = $reverse;

        if ($colors === self::COLOR_AUTO && $console->hasColorSupport()) {
            $this->colors = true;
        } else {
            $this->colors = (self::COLOR_ALWAYS === $colors);
        }
    }

    /**
     * @param TestResult $result
     */
    public function printResult(TestResult $result): void
    {
        $this->printHeader();
        $this->printErrors($result);
        $this->printWarnings($result);
        $this->printFailures($result);
        $this->printRisky($result);

        if ($this->verbose) {
            $this->printIncompletes($result);
            $this->printSkipped($result);
        }

        $this->printFooter($result);
    }

    public function printWaitPrompt(): void
    {
        $this->write("\n<RETURN> to continue\n");
    }

    /**
     * An error occurred.
     *
     * @param Test       $test
     * @param \Throwable $t
     * @param float      $time
     */
    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->writeProgressWithColor('fg-red, bold', 'E');
        $this->lastTestFailed = true;
    }

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->writeProgressWithColor('bg-red, fg-white', 'F');
        $this->lastTestFailed = true;
    }

    /**
     * A warning occurred.
     *
     * @param Test    $test
     * @param Warning $e
     * @param float   $time
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->writeProgressWithColor('fg-yellow, bold', 'W');
        $this->lastTestFailed = true;
    }

    /**
     * Incomplete test.
     *
     * @param Test       $test
     * @param \Throwable $t
     * @param float      $time
     */
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        $this->writeProgressWithColor('fg-yellow, bold', 'I');
        $this->lastTestFailed = true;
    }

    /**
     * Risky test.
     *
     * @param Test       $test
     * @param \Throwable $t
     * @param float      $time
     */
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        $this->writeProgressWithColor('fg-yellow, bold', 'R');
        $this->lastTestFailed = true;
    }

    /**
     * Skipped test.
     *
     * @param Test       $test
     * @param \Throwable $t
     * @param float      $time
     */
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        $this->writeProgressWithColor('fg-cyan, bold', 'S');
        $this->lastTestFailed = true;
    }

    /**
     * A testsuite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        if ($this->numTests == -1) {
            $this->numTests      = \count($suite);
            $this->numTestsWidth = \strlen((string) $this->numTests);
            $this->maxColumn     = $this->numberOfColumns - \strlen('  /  (XXX%)') - (2 * $this->numTestsWidth);
        }
    }

    /**
     * A testsuite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
    }

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test): void
    {
        if ($this->debug) {
            $this->write(
                \sprintf(
                    "Test '%s' started\n",
                    \PHPUnit\Util\Test::describeAsString($test)
                )
            );
        }
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, float $time): void
    {
        if ($this->debug) {
            $this->write(
                \sprintf(
                    "Test '%s' ended\n",
                    \PHPUnit\Util\Test::describeAsString($test)
                )
            );
        }

        if (!$this->lastTestFailed) {
            $this->writeProgress('.');
        }

        if ($test instanceof TestCase) {
            $this->numAssertions += $test->getNumAssertions();
        } elseif ($test instanceof PhptTestCase) {
            $this->numAssertions++;
        }

        $this->lastTestFailed = false;

        if ($test instanceof TestCase) {
            if (!$test->hasExpectationOnOutput()) {
                $this->write($test->getActualOutput());
            }
        }
    }

    /**
     * @param array  $defects
     * @param string $type
     */
    protected function printDefects(array $defects, $type): void
    {
        $count = \count($defects);

        if ($count == 0) {
            return;
        }

        if ($this->defectListPrinted) {
            $this->write("\n--\n\n");
        }

        $this->write(
            \sprintf(
                "There %s %d %s%s:\n",
                ($count == 1) ? 'was' : 'were',
                $count,
                $type,
                ($count == 1) ? '' : 's'
            )
        );

        $i = 1;

        if ($this->reverse) {
            $defects = \array_reverse($defects);
        }

        foreach ($defects as $defect) {
            $this->printDefect($defect, $i++);
        }

        $this->defectListPrinted = true;
    }

    /**
     * @param TestFailure $defect
     * @param int         $count
     */
    protected function printDefect(TestFailure $defect, $count): void
    {
        $this->printDefectHeader($defect, $count);
        $this->printDefectTrace($defect);
    }

    /**
     * @param TestFailure $defect
     * @param int         $count
     */
    protected function printDefectHeader(TestFailure $defect, $count): void
    {
        $this->write(
            \sprintf(
                "\n%d) %s\n",
                $count,
                $defect->getTestName()
            )
        );
    }

    /**
     * @param TestFailure $defect
     */
    protected function printDefectTrace(TestFailure $defect): void
    {
        $e = $defect->thrownException();
        $this->write((string) $e);

        while ($e = $e->getPrevious()) {
            $this->write("\nCaused by\n" . $e);
        }
    }

    /**
     * @param TestResult $result
     */
    protected function printErrors(TestResult $result): void
    {
        $this->printDefects($result->errors(), 'error');
    }

    /**
     * @param TestResult $result
     */
    protected function printFailures(TestResult $result): void
    {
        $this->printDefects($result->failures(), 'failure');
    }

    /**
     * @param TestResult $result
     */
    protected function printWarnings(TestResult $result): void
    {
        $this->printDefects($result->warnings(), 'warning');
    }

    /**
     * @param TestResult $result
     */
    protected function printIncompletes(TestResult $result): void
    {
        $this->printDefects($result->notImplemented(), 'incomplete test');
    }

    /**
     * @param TestResult $result
     */
    protected function printRisky(TestResult $result): void
    {
        $this->printDefects($result->risky(), 'risky test');
    }

    /**
     * @param TestResult $result
     */
    protected function printSkipped(TestResult $result): void
    {
        $this->printDefects($result->skipped(), 'skipped test');
    }

    protected function printHeader(): void
    {
        $this->write("\n\n" . Timer::resourceUsage() . "\n\n");
    }

    /**
     * @param TestResult $result
     */
    protected function printFooter(TestResult $result): void
    {
        if (\count($result) === 0) {
            $this->writeWithColor(
                'fg-black, bg-yellow',
                'No tests executed!'
            );

            return;
        }

        if ($result->wasSuccessful() &&
            $result->allHarmless() &&
            $result->allCompletelyImplemented() &&
            $result->noneSkipped()) {
            $this->writeWithColor(
                'fg-black, bg-green',
                \sprintf(
                    'OK (%d test%s, %d assertion%s)',
                    \count($result),
                    (\count($result) == 1) ? '' : 's',
                    $this->numAssertions,
                    ($this->numAssertions == 1) ? '' : 's'
                )
            );
        } else {
            if ($result->wasSuccessful()) {
                $color = 'fg-black, bg-yellow';

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

            $this->writeCountString(\count($result), 'Tests', $color, true);
            $this->writeCountString($this->numAssertions, 'Assertions', $color, true);
            $this->writeCountString($result->errorCount(), 'Errors', $color);
            $this->writeCountString($result->failureCount(), 'Failures', $color);
            $this->writeCountString($result->warningCount(), 'Warnings', $color);
            $this->writeCountString($result->skippedCount(), 'Skipped', $color);
            $this->writeCountString($result->notImplementedCount(), 'Incomplete', $color);
            $this->writeCountString($result->riskyCount(), 'Risky', $color);
            $this->writeWithColor($color, '.');
        }
    }

    /**
     * @param string $progress
     */
    protected function writeProgress($progress): void
    {
        if ($this->debug) {
            return;
        }

        $this->write($progress);
        $this->column++;
        $this->numTestsRun++;

        if ($this->column == $this->maxColumn || $this->numTestsRun == $this->numTests) {
            if ($this->numTestsRun == $this->numTests) {
                $this->write(\str_repeat(' ', $this->maxColumn - $this->column));
            }

            $this->write(
                \sprintf(
                    ' %' . $this->numTestsWidth . 'd / %' .
                    $this->numTestsWidth . 'd (%3s%%)',
                    $this->numTestsRun,
                    $this->numTests,
                    \floor(($this->numTestsRun / $this->numTests) * 100)
                )
            );

            if ($this->column == $this->maxColumn) {
                $this->writeNewLine();
            }
        }
    }

    protected function writeNewLine(): void
    {
        $this->column = 0;
        $this->write("\n");
    }

    /**
     * Formats a buffer with a specified ANSI color sequence if colors are
     * enabled.
     *
     * @param string $color
     * @param string $buffer
     *
     * @return string
     */
    protected function formatWithColor($color, $buffer): string
    {
        if (!$this->colors) {
            return $buffer;
        }

        $codes   = \array_map('\trim', \explode(',', $color));
        $lines   = \explode("\n", $buffer);
        $padding = \max(\array_map('\strlen', $lines));
        $styles  = [];

        foreach ($codes as $code) {
            $styles[] = self::$ansiCodes[$code];
        }

        $style = \sprintf("\x1b[%sm", \implode(';', $styles));

        $styledLines = [];

        foreach ($lines as $line) {
            $styledLines[] = $style . \str_pad($line, $padding) . "\x1b[0m";
        }

        return \implode("\n", $styledLines);
    }

    /**
     * Writes a buffer out with a color sequence if colors are enabled.
     *
     * @param string $color
     * @param string $buffer
     * @param bool   $lf
     */
    protected function writeWithColor($color, $buffer, $lf = true): void
    {
        $this->write($this->formatWithColor($color, $buffer));

        if ($lf) {
            $this->write("\n");
        }
    }

    /**
     * Writes progress with a color sequence if colors are enabled.
     *
     * @param string $color
     * @param string $buffer
     */
    protected function writeProgressWithColor($color, $buffer): void
    {
        $buffer = $this->formatWithColor($color, $buffer);
        $this->writeProgress($buffer);
    }

    /**
     * @param int    $count
     * @param string $name
     * @param string $color
     * @param bool   $always
     */
    private function writeCountString($count, $name, $color, $always = false): void
    {
        static $first = true;

        if ($always || $count > 0) {
            $this->writeWithColor(
                $color,
                \sprintf(
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
}
