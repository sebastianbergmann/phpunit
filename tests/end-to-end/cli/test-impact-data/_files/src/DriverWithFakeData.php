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

use function realpath;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * Reports that every test executed a line of Calculator.php and a line of
 * Rounder.php, so that what is recorded does not depend on a code coverage
 * driver being available where these tests run.
 */
final class DriverWithFakeData extends Driver
{
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
    }

    public function stop(): RawCodeCoverageData
    {
        return RawCodeCoverageData::fromLineCoverage(
            [
                realpath(__DIR__ . '/Calculator.php') => [16 => Driver::LINE_EXECUTED],
                realpath(__DIR__ . '/Rounder.php')    => [16 => Driver::LINE_EXECUTED],
            ],
        );
    }
}
