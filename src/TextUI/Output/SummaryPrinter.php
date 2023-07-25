<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output;

use function sprintf;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\Util\Color;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SummaryPrinter
{
    private readonly Printer $printer;
    private readonly bool $colors;
    private bool $countPrinted = false;

    public function __construct(Printer $printer, bool $colors)
    {
        $this->printer = $printer;
        $this->colors  = $colors;
    }

    public function print(TestResult $result): void
    {
        if ($result->numberOfTestsRun() === 0) {
            $this->printWithColor(
                'fg-black, bg-yellow',
                'No tests executed!',
            );

            return;
        }

        if ($result->wasSuccessfulAndNoTestHasIssues() &&
            !$result->hasTestSuiteSkippedEvents() &&
            !$result->hasTestSkippedEvents()) {
            $this->printWithColor(
                'fg-black, bg-green',
                sprintf(
                    'OK (%d test%s, %d assertion%s)',
                    $result->numberOfTestsRun(),
                    $result->numberOfTestsRun() === 1 ? '' : 's',
                    $result->numberOfAssertions(),
                    $result->numberOfAssertions() === 1 ? '' : 's',
                ),
            );

            return;
        }

        $color = 'fg-black, bg-yellow';

        if ($result->wasSuccessful()) {
            if (!$result->hasTestsWithIssues()) {
                $this->printWithColor(
                    $color,
                    'OK, but some tests were skipped!',
                );
            } else {
                $this->printWithColor(
                    $color,
                    'OK, but there are issues!',
                );
            }
        } else {
            if ($result->hasTestErroredEvents() || $result->hasTestTriggeredPhpunitErrorEvents()) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'ERRORS!',
                );
            } elseif ($result->hasTestFailedEvents()) {
                $color = 'fg-white, bg-red';

                $this->printWithColor(
                    $color,
                    'FAILURES!',
                );
            } elseif ($result->hasWarningEvents()) {
                $this->printWithColor(
                    $color,
                    'WARNINGS!',
                );
            } elseif ($result->hasDeprecationEvents()) {
                $this->printWithColor(
                    $color,
                    'DEPRECATIONS!',
                );
            } elseif ($result->hasNoticeEvents()) {
                $this->printWithColor(
                    $color,
                    'NOTICES!',
                );
            }
        }

        $this->printCountString($result->numberOfTestsRun(), 'Tests', $color, true);
        $this->printCountString($result->numberOfAssertions(), 'Assertions', $color, true);
        $this->printCountString($result->numberOfTestErroredEvents() + $result->numberOfTestsWithTestTriggeredErrorEvents() + $result->numberOfTestsWithTestTriggeredPhpunitErrorEvents(), 'Errors', $color);
        $this->printCountString($result->numberOfTestFailedEvents(), 'Failures', $color);
        $this->printCountString($result->numberOfWarningEvents(), 'Warnings', $color);
        $this->printCountString($result->numberOfDeprecationEvents(), 'Deprecations', $color);
        $this->printCountString($result->numberOfNoticeEvents(), 'Notices', $color);
        $this->printCountString($result->numberOfTestSuiteSkippedEvents() + $result->numberOfTestSkippedEvents(), 'Skipped', $color);
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
                    $count,
                ),
                false,
            );

            $this->countPrinted = true;
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
