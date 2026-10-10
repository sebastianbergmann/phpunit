<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredOutsideOfTheLifecycleOfAnExample;

use const E_USER_WARNING;
use function trigger_error;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

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
        trigger_error('warning triggered when the collection of code coverage data is started', E_USER_WARNING);
    }

    public function stop(): RawCodeCoverageData
    {
        return RawCodeCoverageData::fromLineCoverage([]);
    }
}
