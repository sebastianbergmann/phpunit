<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData;

use const DEBUG_BACKTRACE_IGNORE_ARGS;
use function debug_backtrace;
use function realpath;
use PHPUnit\Runner\TestImpactAnalysis\ExecutionOutsideOfTests;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * Reports that every test executed a line of Calculator.php and a line of
 * Rounder.php, so that what is recorded does not depend on a code coverage
 * driver being available where these tests run.
 *
 * Code coverage is also collected while no test runs, to record what is
 * executed outside of the tests. Nothing in this directory is executed then:
 * the bootstrap script only loads the classes, which executes none of their
 * lines.
 */
final class DriverWithFakeData extends Driver
{
    private bool $startedOutsideOfTests = false;

    public function name(): string
    {
        return 'DriverWithFakeData';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function start(): void
    {
        $this->startedOutsideOfTests = false;

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            if (isset($frame['class']) && $frame['class'] === ExecutionOutsideOfTests::class) {
                $this->startedOutsideOfTests = true;

                break;
            }
        }
    }

    public function stop(): RawCodeCoverageData
    {
        if ($this->startedOutsideOfTests) {
            return RawCodeCoverageData::fromLineCoverage([]);
        }

        return RawCodeCoverageData::fromLineCoverage(
            [
                realpath(__DIR__ . '/Calculator.php') => [16 => Driver::LINE_EXECUTED],
                realpath(__DIR__ . '/Rounder.php')    => [16 => Driver::LINE_EXECUTED],
            ],
        );
    }
}
