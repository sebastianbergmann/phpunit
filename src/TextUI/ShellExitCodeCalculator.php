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

    /**
     * @param bool $noTestCanBeAffectedByWhatChanged whether test impact analysis selected no test at all
     */
    public function calculate(Configuration $configuration, TestResult $result, bool $noTestCanBeAffectedByWhatChanged = false): int
    {
        $failOnDeprecation        = false;
        $failOnPhpunitDeprecation = false;
        $failOnPhpunitNotice      = false;
        $failOnPhpunitWarning     = false;
        $failOnEmptyTestSuite     = false;
        $failOnIncomplete         = false;
        $failOnNotice             = false;
        $failOnRisky              = false;
        $failOnSkipped            = false;
        $failOnWarning            = false;

        if ($configuration->failOnAllIssues()) {
            $failOnDeprecation        = true;
            $failOnPhpunitDeprecation = true;
            $failOnPhpunitNotice      = true;
            $failOnPhpunitWarning     = true;
            $failOnEmptyTestSuite     = true;
            $failOnIncomplete         = true;
            $failOnNotice             = true;
            $failOnRisky              = true;
            $failOnSkipped            = true;
            $failOnWarning            = true;
        }

        if ($configuration->failOnDeprecation()) {
            $failOnDeprecation = true;
        }

        if ($configuration->doNotFailOnDeprecation()) {
            $failOnDeprecation = false;
        }

        $failOnSelfDeprecation     = $failOnDeprecation;
        $failOnDirectDeprecation   = $failOnDeprecation;
        $failOnIndirectDeprecation = $failOnDeprecation;

        if ($configuration->failOnSelfDeprecation()) {
            $failOnSelfDeprecation = true;
        }

        if ($configuration->doNotFailOnSelfDeprecation()) {
            $failOnSelfDeprecation = false;
        }

        if ($configuration->failOnDirectDeprecation()) {
            $failOnDirectDeprecation = true;
        }

        if ($configuration->doNotFailOnDirectDeprecation()) {
            $failOnDirectDeprecation = false;
        }

        if ($configuration->failOnIndirectDeprecation()) {
            $failOnIndirectDeprecation = true;
        }

        if ($configuration->doNotFailOnIndirectDeprecation()) {
            $failOnIndirectDeprecation = false;
        }

        if ($configuration->failOnPhpunitDeprecation()) {
            $failOnPhpunitDeprecation = true;
        }

        if ($configuration->doNotFailOnPhpunitDeprecation()) {
            $failOnPhpunitDeprecation = false;
        }

        if ($configuration->failOnPhpunitNotice()) {
            $failOnPhpunitNotice = true;
        }

        if ($configuration->doNotFailOnPhpunitNotice()) {
            $failOnPhpunitNotice = false;
        }

        if ($configuration->failOnPhpunitWarning()) {
            $failOnPhpunitWarning = true;
        }

        if ($configuration->doNotFailOnPhpunitWarning()) {
            $failOnPhpunitWarning = false;
        }

        if ($configuration->failOnEmptyTestSuite()) {
            $failOnEmptyTestSuite = true;
        }

        if ($configuration->doNotFailOnEmptyTestSuite()) {
            $failOnEmptyTestSuite = false;
        }

        /*
         * A test run that was asked for the tests that can be affected by what
         * changed, and that found that no test can be, ran no test because
         * there was nothing to run. That is the answer such a test run is for,
         * and not an empty test suite: the tests are there, and what changed
         * cannot affect them.
         */
        if ($noTestCanBeAffectedByWhatChanged) {
            $failOnEmptyTestSuite = false;
        }

        if ($configuration->failOnIncomplete()) {
            $failOnIncomplete = true;
        }

        if ($configuration->doNotFailOnIncomplete()) {
            $failOnIncomplete = false;
        }

        if ($configuration->failOnNotice()) {
            $failOnNotice = true;
        }

        if ($configuration->doNotFailOnNotice()) {
            $failOnNotice = false;
        }

        if ($configuration->failOnRisky()) {
            $failOnRisky = true;
        }

        if ($configuration->doNotFailOnRisky()) {
            $failOnRisky = false;
        }

        if ($configuration->failOnSkipped()) {
            $failOnSkipped = true;
        }

        if ($configuration->doNotFailOnSkipped()) {
            $failOnSkipped = false;
        }

        if ($configuration->failOnWarning()) {
            $failOnWarning = true;
        }

        if ($configuration->doNotFailOnWarning()) {
            $failOnWarning = false;
        }

        $returnCode = self::FAILURE_EXIT;

        if ($result->wasSuccessful()) {
            $returnCode = self::SUCCESS_EXIT;
        }

        if ($failOnEmptyTestSuite && !$result->hasTests()) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($failOnSelfDeprecation && $result->hasSelfDeprecations()) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($failOnDirectDeprecation && $result->hasDirectDeprecations()) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($failOnIndirectDeprecation && $result->hasIndirectDeprecations()) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($failOnDeprecation && $result->hasDeprecationsWithUnknownTrigger()) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($failOnPhpunitDeprecation && $result->hasPhpunitDeprecations()) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($failOnPhpunitNotice && $result->hasPhpunitNotices()) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($failOnPhpunitWarning && $result->hasPhpunitWarnings()) {
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

        if ($result->hasErrors()) {
            $returnCode = self::EXCEPTION_EXIT;
        }

        return $returnCode;
    }
}
