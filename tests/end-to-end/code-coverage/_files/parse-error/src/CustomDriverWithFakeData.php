<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\CodeCoverageParseError;

use function realpath;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

final class CustomDriverWithFakeData extends Driver
{
    public function name(): string
    {
        return 'CustomDriverWithFakeData';
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
        return RawCodeCoverageData::fromXdebugWithoutPathCoverage(
            [
                realpath(__DIR__ . '/Foo.php') => [
                    16 => Driver::LINE_EXECUTED,
                ],
                realpath(__DIR__ . '/CannotBeParsed.php') => [
                    2 => Driver::LINE_EXECUTED,
                    3 => Driver::LINE_EXECUTED,
                ],
            ],
        );
    }
}
