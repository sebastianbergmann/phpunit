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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ShellExitCodeCalculator
{
    private const SUCCESS_EXIT = 0;

    private const FAILURE_EXIT = 1;

    private const EXCEPTION_EXIT = 2;

    public function calculate(Configuration $configuration, TestResult $result): int
    {
        $returnCode = self::FAILURE_EXIT;

        if ($result->wasSuccessful()) {
            $returnCode = self::SUCCESS_EXIT;
        }

        if ($configuration->failOnEmptyTestSuite() && $result->numberOfTests() === 0) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($result->wasSuccessfulIgnoringPhpunitWarnings()) {
            if ($configuration->failOnRisky() && $result->hasTestConsideredRiskyEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($configuration->failOnWarning() && $result->hasWarningEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($configuration->failOnIncomplete() && $result->hasTestMarkedIncompleteEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($configuration->failOnSkipped() && $result->hasTestSkippedEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }
        }

        if ($result->hasTestErroredEvents()) {
            $returnCode = self::EXCEPTION_EXIT;
        }

        return $returnCode;
    }
}
