<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactAnalysis;

use function array_diff;
use function count;
use function file;
use function get_included_files;
use function range;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * Reports every line of a file as executed while the driver ran when the file
 * was loaded while the driver ran, so that when something was executed can be
 * told without a code coverage driver being available where the tests that use
 * it run. Which of these lines are executable is left to the static analysis
 * that filters what a real driver reports, which is what makes a file that
 * only declares a class, and has no executable line outside of its methods,
 * one that was loaded but not executed.
 *
 * It is found by the autoloader of PHPUnit, and is therefore available before
 * the bootstrap script of a test suite is loaded.
 */
final class DriverThatReportsWhatWasLoadedWhileItRan extends Driver
{
    /**
     * @var list<string>
     */
    private array $loadedBefore = [];

    public function name(): string
    {
        return 'DriverThatReportsWhatWasLoadedWhileItRan';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function start(): void
    {
        $this->loadedBefore = get_included_files();
    }

    public function stop(): RawCodeCoverageData
    {
        $lineCoverage = [];

        foreach (array_diff(get_included_files(), $this->loadedBefore) as $file) {
            $lines = file($file);

            if ($lines === false || $lines === []) {
                continue;
            }

            foreach (range(1, count($lines)) as $line) {
                $lineCoverage[$file][$line] = Driver::LINE_EXECUTED;
            }
        }

        return RawCodeCoverageData::fromLineCoverage($lineCoverage);
    }
}
