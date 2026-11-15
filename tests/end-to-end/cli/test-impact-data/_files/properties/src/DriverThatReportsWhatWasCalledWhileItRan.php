<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData\Properties;

use function realpath;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * Reports that Doubler::double(), Tripler::triple(), and Negator::negate()
 * were executed when they were called while the driver ran, so that which
 * example of a property executed which source file can be told without a code
 * coverage driver being available where these tests run.
 */
final class DriverThatReportsWhatWasCalledWhileItRan extends Driver
{
    public function name(): string
    {
        return 'DriverThatReportsWhatWasCalledWhileItRan';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function start(): void
    {
        Doubler::$executed = false;
        Tripler::$executed = false;
        Negator::$executed = false;
    }

    public function stop(): RawCodeCoverageData
    {
        return RawCodeCoverageData::fromLineCoverage(
            [
                realpath(__DIR__ . '/Doubler.php') => [18 => $this->status(Doubler::$executed)],
                realpath(__DIR__ . '/Tripler.php') => [18 => $this->status(Tripler::$executed)],
                realpath(__DIR__ . '/Negator.php') => [18 => $this->status(Negator::$executed)],
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
