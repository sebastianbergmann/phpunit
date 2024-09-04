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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ShellExitCodeCalculator
{
    private const SUCCESS_EXIT   = 0;
    private const FAILURE_EXIT   = 1;
    private const EXCEPTION_EXIT = 2;

    public function calculate(bool $failOnDeprecation, bool $failOnPhpunitDeprecation, bool $failOnEmptyTestSuite, bool $failOnIncomplete, bool $failOnNotice, bool $failOnRisky, bool $failOnSkipped, bool $failOnWarning, TestResult $result): int
    {
        $returnCode = self::FAILURE_EXIT;

        if ($result->wasSuccessful()) {
            $returnCode = self::SUCCESS_EXIT;
        }

        if ($failOnEmptyTestSuite && !$result->hasTests()) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($result->wasSuccessfulIgnoringPhpunitWarnings()) {
            if ($failOnDeprecation && $result->hasPhpOrUserDeprecations()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnPhpunitDeprecation && $result->hasPhpunitDeprecations()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnIncomplete && $result->hasIncompleteTests()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnNotice && $result->hasNotices()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnRisky && $result->hasRiskyTests()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnSkipped && $result->hasSkippedTests()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($failOnWarning && $result->hasWarnings()) {
                $returnCode = self::FAILURE_EXIT;
            }
        }

        if ($result->hasErrors()) {
            $returnCode = self::EXCEPTION_EXIT;
        }

        return $returnCode;
    }
}
