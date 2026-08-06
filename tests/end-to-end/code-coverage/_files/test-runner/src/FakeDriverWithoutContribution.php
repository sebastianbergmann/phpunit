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
 * Reports that no line of code was executed.
 */
final class FakeDriverWithoutContribution extends Driver
{
    public function name(): string
    {
        return 'FakeDriverWithoutContribution';
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
                realpath(__DIR__ . '/Covered.php') => [16 => Driver::LINE_NOT_EXECUTED],
            ],
        );
    }
}
