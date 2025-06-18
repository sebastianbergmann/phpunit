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
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ShellExitCodeCalculator
{
    private const int SUCCESS_EXIT   = 0;
    private const int FAILURE_EXIT   = 1;
    private const int EXCEPTION_EXIT = 2;

    public function calculate(Configuration $configuration, TestResult $result): int
    {
        $failOnDeprecation        = $configuration->failOnDeprecation() || $configuration->failOnAllIssues();
        $failOnPhpunitDeprecation = $configuration->failOnPhpunitDeprecation() || $configuration->failOnAllIssues();
        $failOnPhpunitNotice      = $configuration->failOnPhpunitNotice() || $configuration->failOnAllIssues();
        $failOnEmptyTestSuite     = $configuration->failOnEmptyTestSuite() || $configuration->failOnAllIssues();
        $failOnIncomplete         = $configuration->failOnIncomplete() || $configuration->failOnAllIssues();
        $failOnNotice             = $configuration->failOnNotice() || $configuration->failOnAllIssues();
        $failOnRisky              = $configuration->failOnRisky() || $configuration->failOnAllIssues();
        $failOnSkipped            = $configuration->failOnSkipped() || $configuration->failOnAllIssues();
        $failOnWarning            = $configuration->failOnWarning() || $configuration->failOnAllIssues();

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

            if ($failOnPhpunitNotice && $result->hasPhpunitNotices()) {
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
