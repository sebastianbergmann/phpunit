<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\CodeCoverageDriverNotAvailableInChildProcess;

use function getenv;
use function getmypid;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Driver\Granularity;
use SebastianBergmann\CodeCoverage\NoSupportedDriverAvailableException;

final class DriverThatIsOnlyAvailableInMainProcess extends Driver
{
    public function __construct()
    {
        if (getenv('PHPUNIT_TEST_MAIN_PROCESS_ID') !== (string) getmypid()) {
            throw new NoSupportedDriverAvailableException(Granularity::Line);
        }
    }

    public function name(): string
    {
        return 'DriverThatIsOnlyAvailableInMainProcess';
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
        return RawCodeCoverageData::fromLineCoverage([]);
    }
}
