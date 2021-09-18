<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use function printf;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Registry;
use SebastianBergmann\CodeCoverage\StaticAnalysis\CacheWarmer;
use SebastianBergmann\Timer\Timer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class WarmCodeCoverageCacheCommand implements Command
{
    public function execute(): Result
    {
        $configuration = Registry::get();

        if (!$configuration->hasCoverageCacheDirectory()) {
            return Result::from(
                'Cache for static analysis has not been configured' . PHP_EOL,
                false
            );
        }

        if (!CodeCoverageFilterRegistry::configured()) {
            return Result::from(
                'Filter for code coverage has not been configured' . PHP_EOL,
                false
            );
        }

        $timer = new Timer;
        $timer->start();

        print 'Warming cache for static analysis ... ';

        (new CacheWarmer)->warmCache(
            $configuration->coverageCacheDirectory(),
            !$configuration->disableCodeCoverageIgnore(),
            $configuration->ignoreDeprecatedCodeUnitsFromCodeCoverage(),
            CodeCoverageFilterRegistry::get()
        );

        printf(
            '[%s]%s',
            $timer->stop()->asString(),
            \PHP_EOL
        );

        return Result::from();
    }
}
