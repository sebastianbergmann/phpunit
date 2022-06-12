<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\ResultPrinter;

use const PHP_EOL;
use function implode;
use function max;
use function preg_split;
use function str_contains;
use function str_pad;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Aborted;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\RiskyTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult as LegacyTestResult;
use PHPUnit\TextUI\TestResult\TestResult;
use PHPUnit\Util\Color;
use PHPUnit\Util\Printer;
use ReflectionMethod;
use SebastianBergmann\Timer\ResourceUsageFormatter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter
{
    private Printer $printer;
    private bool $colors;
    private bool $displayDetailsOnIncompleteTests;
    private bool $displayDetailsOnSkippedTests;
    private bool $reverse;
    private int $numberOfTestsRun   = 0;
    private bool $defectListPrinted = false;
    private bool $prepared          = false;

    /**
     * @psalm-var list<Skipped>
     */
    private array $skippedTests = [];

    /**
     * @psalm-var list<Aborted>
     */
    private array $incompleteTests = [];

    /**
     * @psalm-var array<string,list<ConsideredRisky>>
     */
    private array $riskyTests = [];

    /**
     * @psalm-var array<string,list<PassedWithWarning>>
     */
    private array $testsWithWarnings = [];

    /**
     * @psalm-var list<Failed>
     */
    private array $failedTests = [];

    /**
     * @psalm-var list<BeforeFirstTestMethodErrored|Errored>
     */
    private array $erroredTests = [];

    public function __construct(Printer $printer, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $colors, bool $reverse)
    {
        $this->printer = $printer;

        $this->displayDetailsOnIncompleteTests = $displayDetailsOnIncompleteTests;
        $this->displayDetailsOnSkippedTests    = $displayDetailsOnSkippedTests;
        $this->colors                          = $colors;
        $this->reverse                         = $reverse;

        $this->registerSubscribers();
    }

    public function printResult(TestResult $result, LegacyTestResult $legacyResult): void
    {
        $this->printHeader($result);
        $this->printTestsWithErrors($result, $legacyResult);
        $this->printTestsWithWarnings($result, $legacyResult);
        $this->printTestsWithFailedAssertions($result, $legacyResult);
        $this->printRiskyTests($result, $legacyResult);

        if ($this->displayDetailsOnIncompleteTests) {
            $this->printIncompleteTests($result, $legacyResult);
        }

        if ($this->displayDetailsOnSkippedTests) {
            $this->printSkippedTests($result, $legacyResult);
        }

        $this->printFooter($result);
    }

    public function beforeTestClassMethodErrored(BeforeFirstTestMethodErrored $event): void
    {
        $this->erroredTests[] = $event;

        $this->numberOfTestsRun++;
    }

    public function testPrepared(): void
    {
        $this->prepared = true;
    }

    public function testSkipped(Skipped $event): void
    {
        $this->skippedTests[] = $event;

        if (!$this->prepared) {
            $this->numberOfTestsRun++;
        }
    }

    public function testAborted(Aborted $event): void
    {
        $this->incompleteTests[] = $event;
    }

    public function testConsideredRisky(ConsideredRisky $event): void
    {
        if (!isset($this->riskyTests[$event->test()->id()])) {
            $this->riskyTests[$event->test()->id()] = [];
        }

        $this->riskyTests[$event->test()->id()][] = $event;
    }

    public function testPassedWithWarning(PassedWithWarning $event): void
    {
        if (!isset($this->testsWithWarnings[$event->test()->id()])) {
            $this->testsWithWarnings[$event->test()->id()] = [];
        }

        $this->testsWithWarnings[$event->test()->id()][] = $event;
    }

    public function testFailed(Failed $event): void
    {
        $this->failedTests[] = $event;
    }

    public function testErrored(Errored $event): void
    {
        $this->erroredTests[] = $event;

        /*
         * @todo Eliminate this special case
         */
        if (str_contains($event->asString(), 'Test was run in child process and ended unexpectedly')) {
            return;
        }

        if (!$this->prepared) {
            $this->numberOfTestsRun++;
        }
    }

    public function testFinished(Finished $event): void
    {
        $this->numberOfTestsRun++;

        $this->prepared = false;
    }

    public function flush(): void
    {
        $this->printer->flush();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(): void
    {
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

    private function printHeader(TestResult $result): void
    {
        if ($this->numberOfTestsRun > 0) {
            $this->printer->print(PHP_EOL . PHP_EOL . (new ResourceUsageFormatter)->resourceUsageSinceStartOfRequest() . PHP_EOL . PHP_EOL);
        }
    }

    private function printTestsWithErrors(TestResult $result, LegacyTestResult $legacyResult): void
    {
        $this->printDefects($legacyResult->errors(), 'error');
    }

    private function printTestsWithFailedAssertions(TestResult $result, LegacyTestResult $legacyResult): void
    {
        $this->printDefects($legacyResult->failures(), 'failure');
    }

    private function printTestsWithWarnings(TestResult $result, LegacyTestResult $legacyResult): void
    {
        $this->printDefects($legacyResult->warnings(), 'warning');
    }

    private function printRiskyTests(TestResult $result, LegacyTestResult $legacyResult): void
    {
        $this->printDefects($legacyResult->risky(), 'risky test');
    }

    private function printIncompleteTests(TestResult $result, LegacyTestResult $legacyResult): void
    {
        $this->printDefects($legacyResult->notImplemented(), 'incomplete test');
    }

    private function printSkippedTests(TestResult $result, LegacyTestResult $legacyResult): void
    {
        $this->printDefects($legacyResult->skipped(), 'skipped test');
    }

    private function printDefects(array $defects, string $type): void
    {
        $count = count($defects);

        if ($count === 0) {
            return;
        }

        if ($this->defectListPrinted) {
            $this->printer->print("\n--\n\n");
        }

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
        $this->printer->print(
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

        $this->printer->print((string) $e);

        if ($defect->thrownException() instanceof RiskyTest) {
            $test = $defect->failedTest();

            assert($test instanceof TestCase);

            /** @noinspection PhpUnhandledExceptionInspection */
            $reflector = new ReflectionMethod($test::class, $test->getName(false));

            $this->printer->print(
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
                $this->printer->print("\nCaused by\n" . $e);
            }
        }
    }

    private function printFooter(TestResult $result): void
    {
        if ($this->numberOfTestsRun === 0) {
            $this->printWithColor(
                'fg-black, bg-yellow',
                'No tests executed!'
            );

            return;
        }

        if ($this->wasSuccessfulAndNoTestIsRiskyOrSkippedOrIncomplete()) {
            $this->printWithColor(
                'fg-black, bg-green',
                sprintf(
                    'OK (%d test%s, %d assertion%s)',
                    $this->numberOfTestsRun,
                    $this->numberOfTestsRun === 1 ? '' : 's',
                    $result->numberOfAssertions(),
                    $result->numberOfAssertions() === 1 ? '' : 's'
                )
            );

            return;
        }

        $color = 'fg-black, bg-yellow';

        if ($this->wasSuccessful()) {
            if ($this->displayDetailsOnIncompleteTests || $this->displayDetailsOnSkippedTests || !$this->noneRisky()) {
                $this->printer->print("\n");
            }

            $this->printWithColor(
                $color,
                'OK, but incomplete, skipped, or risky tests!'
            );
        } else {
            $this->printer->print("\n");

            if (!empty($this->erroredTests)) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'ERRORS!'
                );
            } elseif (!empty($this->failedTests)) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'FAILURES!'
                );
            } elseif (!empty($this->testsWithWarnings)) {
                $this->printWithColor(
                    $color,
                    'WARNINGS!'
                );
            }
        }

        $this->printCountString($this->numberOfTestsRun, 'Tests', $color, true);
        $this->printCountString($result->numberOfAssertions(), 'Assertions', $color, true);
        $this->printCountString(count($this->erroredTests), 'Errors', $color);
        $this->printCountString(count($this->failedTests), 'Failures', $color);
        $this->printCountString(count($this->testsWithWarnings), 'Warnings', $color);
        $this->printCountString(count($this->skippedTests), 'Skipped', $color);
        $this->printCountString(count($this->incompleteTests), 'Incomplete', $color);
        $this->printCountString(count($this->riskyTests), 'Risky', $color);
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
        $this->printer->print($this->colorizeTextBox($color, $buffer));

        if ($lf) {
            $this->printer->print(PHP_EOL);
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

    private function wasSuccessful(): bool
    {
        return $this->wasSuccessfulIgnoringWarnings() && empty($this->testsWithWarnings);
    }

    private function wasSuccessfulIgnoringWarnings(): bool
    {
        return empty($this->erroredTests) && empty($this->failedTests);
    }

    private function wasSuccessfulAndNoTestIsRiskyOrSkippedOrIncomplete(): bool
    {
        return $this->wasSuccessful() && $this->noneRisky() && $this->noneAborted() && $this->noneSkipped();
    }

    private function noneSkipped(): bool
    {
        return empty($this->skippedTests);
    }

    private function noneAborted(): bool
    {
        return empty($this->incompleteTests);
    }

    private function noneRisky(): bool
    {
        return empty($this->riskyTests);
    }
}
