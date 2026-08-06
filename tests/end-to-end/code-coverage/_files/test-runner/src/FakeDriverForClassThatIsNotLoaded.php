<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestRunner;

use function realpath;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * Reports that Covered::value() and NotLoaded::value() were executed.
 * NotLoaded is not loaded and cannot be autoloaded, which makes the check for
 * unintentionally covered code fail with an exception instead of a result.
 */
final class FakeDriverForClassThatIsNotLoaded extends Driver
{
    public function name(): string
    {
        return 'FakeDriverForClassThatIsNotLoaded';
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
                realpath(__DIR__ . '/Covered.php')   => [16 => Driver::LINE_EXECUTED],
                realpath(__DIR__ . '/NotLoaded.php') => [16 => Driver::LINE_EXECUTED],
            ],
        );
    }
}
