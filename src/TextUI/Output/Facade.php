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

use PHPUnit\Logging\TeamCity\TeamCityLogger;
use PHPUnit\Logging\TestDox\TestResultCollection;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Output\Default\ProgressPrinter\ProgressPrinter as DefaultProgressPrinter;
use PHPUnit\TextUI\Output\Default\ResultPrinter as DefaultResultPrinter;
use PHPUnit\TextUI\Output\TestDox\ResultPrinter as TestDoxResultPrinter;
use SebastianBergmann\Timer\ResourceUsageFormatter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?Printer $printer                    = null;
    private static ?DefaultResultPrinter $resultPrinter = null;
    private static ?SummaryPrinter $summaryPrinter      = null;
    private static bool $colors                         = false;
    private static bool $defaultProgressPrinter         = false;

    public static function printer(): Printer
    {
        return self::$printer;
    }

    public static function init(Configuration $configuration): void
    {
        self::$printer = self::createPrinter($configuration);

        if (self::useDefaultProgressPrinter($configuration)) {
            new DefaultProgressPrinter(
                self::$printer,
                $configuration->colors(),
                $configuration->columns()
            );

            self::$defaultProgressPrinter = true;
        }

        if (self::useDefaultResultPrinter($configuration)) {
            self::$resultPrinter = new DefaultResultPrinter(
                self::$printer,
                $configuration->displayDetailsOnIncompleteTests(),
                $configuration->displayDetailsOnSkippedTests(),
                $configuration->displayDetailsOnTestsThatTriggerDeprecations(),
                $configuration->displayDetailsOnTestsThatTriggerErrors(),
                $configuration->displayDetailsOnTestsThatTriggerNotices(),
                $configuration->displayDetailsOnTestsThatTriggerWarnings(),
                $configuration->reverseDefectList()
            );
        }

        if (self::useDefaultResultPrinter($configuration) || $configuration->outputIsTestDox()) {
            self::$summaryPrinter = new SummaryPrinter(
                self::$printer,
                $configuration->colors(),
            );
        }

        if ($configuration->outputIsTeamCity()) {
            new TeamCityLogger(DefaultPrinter::standardOutput());
        }

        self::$colors = $configuration->colors();
    }

    /**
     * @psalm-param ?array<string, TestResultCollection> $testDoxResult
     */
    public static function printResult(TestResult $result, ?array $testDoxResult): void
    {
        if ($result->numberOfTestsRun() > 0) {
            if (self::$defaultProgressPrinter) {
                self::$printer->print(PHP_EOL . PHP_EOL);
            }

            self::$printer->print((new ResourceUsageFormatter)->resourceUsageSinceStartOfRequest() . PHP_EOL . PHP_EOL);
        }

        if (self::$resultPrinter !== null && self::$summaryPrinter !== null) {
            self::$resultPrinter->print($result);
            self::$summaryPrinter->print($result);
        }

        if ($testDoxResult !== null && self::$summaryPrinter !== null) {
            (new TestDoxResultPrinter(self::$printer, self::$colors))->print(
                $testDoxResult
            );

            self::$summaryPrinter->print($result);
        }
    }

    private static function createPrinter(Configuration $configuration): Printer
    {
        if (self::useDefaultProgressPrinter($configuration) ||
            self::useDefaultResultPrinter($configuration) ||
            $configuration->outputIsTestDox()) {
            if ($configuration->outputToStandardErrorStream()) {
                return DefaultPrinter::standardError();
            }

            return DefaultPrinter::standardOutput();
        }

        return new NullPrinter;
    }

    private static function useDefaultProgressPrinter(Configuration $configuration): bool
    {
        if ($configuration->noOutput()) {
            return false;
        }

        if ($configuration->noProgress()) {
            return false;
        }

        if ($configuration->outputIsTeamCity()) {
            return false;
        }

        return true;
    }

    private static function useDefaultResultPrinter(Configuration $configuration): bool
    {
        if ($configuration->noOutput()) {
            return false;
        }

        if ($configuration->noResults()) {
            return false;
        }

        if ($configuration->outputIsTeamCity()) {
            return false;
        }

        if ($configuration->outputIsTestDox()) {
            return false;
        }

        return true;
    }
}
