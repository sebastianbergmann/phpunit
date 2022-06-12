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
    private bool $defectListPrinted = false;

    public function __construct(Printer $printer, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $colors, bool $reverse)
    {
        $this->printer = $printer;

        $this->displayDetailsOnIncompleteTests = $displayDetailsOnIncompleteTests;
        $this->displayDetailsOnSkippedTests    = $displayDetailsOnSkippedTests;
        $this->colors                          = $colors;
        $this->reverse                         = $reverse;
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
            if ($this->displayDetailsOnIncompleteTests || $this->displayDetailsOnSkippedTests || $result->hasRiskyTests()) {
                $this->printer->print("\n");
            }

            $this->printWithColor(
                $color,
                'OK, but incomplete, skipped, or risky tests!'
            );
        } else {
            $this->printer->print("\n");

            if ($result->hasErroredTests()) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'ERRORS!'
                );
            } elseif ($result->hasFailedTests()) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'FAILURES!'
                );
            } elseif ($result->hasTestsWithWarnings()) {
                $this->printWithColor(
                    $color,
                    'WARNINGS!'
                );
            }
        }

        $this->printCountString($result->numberOfTestsRun(), 'Tests', $color, true);
        $this->printCountString($result->numberOfAssertions(), 'Assertions', $color, true);
        $this->printCountString($result->numberOfErroredTests(), 'Errors', $color);
        $this->printCountString($result->numberOfFailedTests(), 'Failures', $color);
        $this->printCountString($result->numberOfTestsWithWarnings(), 'Warnings', $color);
        $this->printCountString($result->numberOfSkippedTests(), 'Skipped', $color);
        $this->printCountString($result->numberOfIncompleteTests(), 'Incomplete', $color);
        $this->printCountString($result->numberOfRiskyTests(), 'Risky', $color);
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
        if ($this->colors) {
            $buffer = Color::colorizeTextBox($color, $buffer);
        }

        $this->printer->print($buffer);

        if ($lf) {
            $this->printer->print(PHP_EOL);
        }
    }
}
