<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Granularity;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @codeCoverageIgnore
 */
final readonly class CodeCoverageBootstrapper
{
    /**
     * @param ?non-empty-string $codeCoverageCacheDirectory
     */
    public static function bootstrap(?string $codeCoverageCacheDirectory, bool $branchCoverage, bool $pathCoverage): CodeCoverage
    {
        $filter = new Filter;

        $granularity = Granularity::Line;

        if ($branchCoverage) {
            $granularity = Granularity::LineAndBranch;
        }

        if ($pathCoverage) {
            $granularity = Granularity::LineBranchAndPath;
        }

        $coverage = new CodeCoverage(
            (new Selector)->select($filter, $granularity),
            $filter,
        );

        if ($codeCoverageCacheDirectory !== null) {
            $coverage->cacheStaticAnalysis($codeCoverageCacheDirectory);
        }

        return $coverage;
    }
}
