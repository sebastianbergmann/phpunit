<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use PHPUnit\TestRunner\TestResult\TestResult;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ShellExitCodeCalculator
{
    private const SUCCESS_EXIT   = 0;
    private const FAILURE_EXIT   = 1;
    private const EXCEPTION_EXIT = 2;

    public function calculate(bool $failOnDeprecation, bool $failOnEmptyTestSuite, bool $failOnIncomplete, bool $failOnNotice, bool $failOnRisky, bool $failOnSkipped, bool $failOnWarning, TestResult $result): int
    {
        $returnCode = self::FAILURE_EXIT;

        if ($result->wasSuccessful()) {
            $returnCode = self::SUCCESS_EXIT;
        }

        if ($failOnEmptyTestSuite && $result->numberOfTests() === 0) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($result->wasSuccessfulIgnoringPhpunitWarnings()) {
            if ($failOnDeprecation && ($result->hasTestTriggeredDeprecationEvents() || $result->hasTestTriggeredPhpDeprecationEvents() || $result->hasTestTriggeredPhpunitDeprecationEvents())) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnIncomplete && $result->hasTestMarkedIncompleteEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnNotice && ($result->hasTestTriggeredNoticeEvents() || $result->hasTestTriggeredPhpNoticeEvents())) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnRisky && $result->hasTestConsideredRiskyEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnSkipped && $result->hasTestSkippedEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnWarning && $result->hasWarningEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }
        }

        if ($result->hasTestErroredEvents() || $result->hasTestTriggeredPhpunitErrorEvents()) {
            $returnCode = self::EXCEPTION_EXIT;
        }

        return $returnCode;
    }
}
