<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use function mt_srand;
use PHPUnit\Event;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\Runner\ResultCache\NullResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheHandler;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\Configuration\CodeCoverageReportNotConfiguredException;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\FilterNotConfiguredException;
use PHPUnit\TextUI\Configuration\LoggingNotConfiguredException;
use PHPUnit\TextUI\Configuration\NoBootstrapException;
use PHPUnit\TextUI\Configuration\NoConfigurationFileException;
use PHPUnit\TextUI\Configuration\NoCoverageCacheDirectoryException;
use PHPUnit\TextUI\Configuration\NoCustomCssFileException;
use PHPUnit\TextUI\Configuration\NoPharExtensionDirectoryException;
use SebastianBergmann\CodeCoverage\Driver\PcovNotAvailableException;
use SebastianBergmann\CodeCoverage\Driver\XdebugNotAvailableException;
use SebastianBergmann\CodeCoverage\Driver\XdebugNotEnabledException;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\NoCodeCoverageDriverAvailableException;
use SebastianBergmann\CodeCoverage\NoCodeCoverageDriverWithPathCoverageSupportAvailableException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Timer\TimeSinceStartOfRequestNotAvailableException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws CodeCoverageReportNotConfiguredException
     * @throws Event\EventFacadeIsSealedException
     * @throws Event\RuntimeException
     * @throws Event\UnknownSubscriberTypeException
     * @throws Exception
     * @throws FilterNotConfiguredException
     * @throws InvalidArgumentException
     * @throws LoggingNotConfiguredException
     * @throws NoBootstrapException
     * @throws NoCodeCoverageDriverAvailableException
     * @throws NoCodeCoverageDriverWithPathCoverageSupportAvailableException
     * @throws NoConfigurationFileException
     * @throws NoCoverageCacheDirectoryException
     * @throws NoCustomCssFileException
     * @throws NoPharExtensionDirectoryException
     * @throws NoPreviousThrowableException
     * @throws PcovNotAvailableException
     * @throws TimeSinceStartOfRequestNotAvailableException
     * @throws UnintentionallyCoveredCodeException
     * @throws XdebugNotAvailableException
     * @throws XdebugNotEnabledException
     * @throws XmlConfiguration\Exception
     */
    public function run(Configuration $configuration, TestSuite $suite): void
    {
        Event\Facade::emitter()->testRunnerStarted();

        if ($configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED) {
            mt_srand($configuration->randomOrderSeed());
        }

        if ($configuration->cacheResult()) {
            $cache = new DefaultResultCache($configuration->testResultCacheFile());

            new ResultCacheHandler($cache);
        }

        if ($configuration->executionOrder() !== TestSuiteSorter::ORDER_DEFAULT ||
            $configuration->executionOrderDefects() !== TestSuiteSorter::ORDER_DEFAULT ||
            $configuration->resolveDependencies()) {
            $cache = $cache ?? new NullResultCache;

            $cache->load();

            (new TestSuiteSorter($cache))->reorderTestsInSuite(
                $suite,
                $configuration->executionOrder(),
                $configuration->resolveDependencies(),
                $configuration->executionOrderDefects()
            );

            Event\Facade::emitter()->testSuiteSorted(
                $configuration->executionOrder(),
                $configuration->executionOrderDefects(),
                $configuration->resolveDependencies()
            );
        }

        Event\Facade::seal();

        (new TestSuiteFilterProcessor)->process($configuration, $suite);

        Event\Facade::emitter()->testRunnerExecutionStarted(
            Event\TestSuite\TestSuite::fromTestSuite($suite)
        );

        $suite->run();

        Event\Facade::emitter()->testRunnerExecutionFinished();
        Event\Facade::emitter()->testRunnerFinished();
    }
}
