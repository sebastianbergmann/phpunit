<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\CoverageOfExamplesWithoutAssertions;

use function realpath;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * Reports that Calculator::double() and Doubler::double() were executed when
 * they were called while code coverage data was collected.
 *
 * Faking the code coverage data keeps the test runner in the child process
 * from starting and stopping a real code coverage driver, which would end the
 * collection of code coverage data for the PHPT test itself.
 */
final class FakeDriver extends Driver
{
    public function name(): string
    {
        return 'FakeDriver';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function start(): void
    {
        Calculator::$executed = false;
        Doubler::$executed    = false;
    }

    public function stop(): RawCodeCoverageData
    {
        return RawCodeCoverageData::fromLineCoverage(
            [
                realpath(__DIR__ . '/Calculator.php') => [18 => $this->status(Calculator::$executed)],
                realpath(__DIR__ . '/Doubler.php')    => [18 => $this->status(Doubler::$executed)],
            ],
        );
    }

    private function status(bool $executed): int
    {
        if ($executed) {
            return Driver::LINE_EXECUTED;
        }

        return Driver::LINE_NOT_EXECUTED;
    }
}
