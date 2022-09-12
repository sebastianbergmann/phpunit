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

use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\Util\Color;
use PHPUnit\Util\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class ResultPrinter
{
    private Printer $printer;
    private bool $colors;
    private bool $countPrinted = false;

    public function __construct(Printer $printer, bool $colors)
    {
        $this->printer = $printer;
        $this->colors  = $colors;
    }

    public function flush(): void
    {
        $this->printer->flush();
    }

    abstract public function printResult(TestResult $result): void;

    protected function printer(): Printer
    {
        return $this->printer;
    }

    protected function colors(): bool
    {
        return $this->colors;
    }

    protected function printFooter(TestResult $result): void
    {
        if ($result->numberOfTestsRun() === 0) {
            $this->printWithColor(
                'fg-black, bg-yellow',
                'No tests executed!'
            );

            return;
        }

        if ($result->wasSuccessfulAndNoTestHasIssues() &&
            !$result->hasTestSkippedEvents()) {
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
            if (!$result->hasTestsWithIssues()) {
                $this->printWithColor(
                    $color,
                    'OK, but some tests were skipped!'
                );
            } else {
                $this->printWithColor(
                    $color,
                    'OK, but some tests have issues!'
                );
            }
        } else {
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
            } elseif ($result->hasWarningEvents()) {
                $this->printWithColor(
                    $color,
                    'WARNINGS!'
                );
            } elseif ($result->hasDeprecationEvents()) {
                $this->printWithColor(
                    $color,
                    'DEPRECATIONS!'
                );
            } elseif ($result->hasNoticeEvents()) {
                $this->printWithColor(
                    $color,
                    'NOTICES!'
                );
            }
        }

        $this->printCountString($result->numberOfTestsRun(), 'Tests', $color, true);
        $this->printCountString($result->numberOfAssertions(), 'Assertions', $color, true);
        $this->printCountString($result->numberOfTestErroredEvents() + $result->numberOfTestsWithTestTriggeredErrorEvents(), 'Errors', $color);
        $this->printCountString($result->numberOfTestFailedEvents(), 'Failures', $color);
        $this->printCountString($result->numberOfWarningEvents(), 'Warnings', $color);
        $this->printCountString($result->numberOfDeprecationEvents(), 'Deprecations', $color);
        $this->printCountString($result->numberOfNoticeEvents(), 'Notices', $color);
        $this->printCountString($result->numberOfTestSkippedEvents(), 'Skipped', $color);
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
                    $count
                ),
                false
            );

            $this->countPrinted = true;
        }
    }

    private function printWithColor(string $color, string $buffer, bool $lf = true): void
    {
        if ($this->colors()) {
            $buffer = Color::colorizeTextBox($color, $buffer);
        }

        $this->printer()->print($buffer);

        if ($lf) {
            $this->printer()->print(PHP_EOL);
        }
    }
}
