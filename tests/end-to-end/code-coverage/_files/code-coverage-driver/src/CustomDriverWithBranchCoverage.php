<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\CodeCoverageDriver;

use function realpath;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * A driver that can collect branch coverage but not path coverage.
 */
final class CustomDriverWithBranchCoverage extends Driver
{
    public function name(): string
    {
        return 'CustomDriverWithBranchCoverage';
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
        $file = realpath(__DIR__ . '/Foo.php');

        return RawCodeCoverageData::fromLineAndBranchCoverage(
            [
                $file => [
                    16 => Driver::LINE_EXECUTED,
                ],
            ],
            [
                $file => [
                    'PHPUnit\TestFixture\CodeCoverageDriver\Foo->value' => [
                        'branches' => [
                            0 => [
                                'op_start'   => 0,
                                'op_end'     => 1,
                                'line_start' => 14,
                                'line_end'   => 16,
                                'hit'        => Driver::BRANCH_HIT,
                                'out'        => [],
                                'out_hit'    => [],
                            ],
                        ],
                        'paths' => [],
                    ],
                ],
            ],
        );
    }

    protected function canCollectBranchCoverage(): bool
    {
        return true;
    }
}
