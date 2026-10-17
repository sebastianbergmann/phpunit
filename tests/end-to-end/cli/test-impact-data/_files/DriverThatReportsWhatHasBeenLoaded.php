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

use function class_exists;
use function realpath;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * Reports that every test executed a line of Doubler.php, and a line of
 * Tripler.php once that class has been loaded, so that a source file that is
 * first loaded by the test that covers nothing can be told apart from one
 * every test executes. No code coverage driver has to be available where
 * these tests run.
 */
final class DriverThatReportsWhatHasBeenLoaded extends Driver
{
    public function name(): string
    {
        return 'DriverThatReportsWhatHasBeenLoaded';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function start(): void
    {
    }

    public function stop(): RawCodeCoverageData
    {
        $lineCoverage = [
            realpath(__DIR__ . '/lazily-loaded-source/Doubler.php') => [16 => Driver::LINE_EXECUTED],
        ];

        if (class_exists(Tripler::class, false)) {
            $lineCoverage[realpath(__DIR__ . '/lazily-loaded-source/Tripler.php')] = [16 => Driver::LINE_EXECUTED];
        }

        return RawCodeCoverageData::fromLineCoverage($lineCoverage);
    }
}
