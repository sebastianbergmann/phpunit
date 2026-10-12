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
use SebastianBergmann\CodeCoverage\Exception;
use SebastianBergmann\CodeCoverage\Filter;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @codeCoverageIgnore
 */
final readonly class CodeCoverageBootstrapper
{
    /**
     * Returns null when no code coverage driver is available. The main process
     * then emits a warning because no code coverage data has been collected.
     *
     * @param ?non-empty-string $codeCoverageCacheDirectory
     */
    public static function bootstrap(?string $codeCoverageCacheDirectory, bool $branchCoverage, bool $pathCoverage): ?CodeCoverage
    {
        $filter = new Filter;

        $granularity = Granularity::Line;

        if ($branchCoverage) {
            $granularity = Granularity::LineAndBranch;
        }

        if ($pathCoverage) {
            $granularity = Granularity::LineBranchAndPath;
        }

        try {
            $driver = (new Selector)->select($filter, $granularity);
        } catch (Exception) {
            return null;
        }

        $coverage = new CodeCoverage($driver, $filter);

        if ($codeCoverageCacheDirectory !== null) {
            $coverage->cacheStaticAnalysis($codeCoverageCacheDirectory);
        }

        return $coverage;
    }
}
