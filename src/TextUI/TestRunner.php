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

use const PHP_VERSION;
use function is_file;
use function mt_srand;
use function sprintf;
use function unlink;
use PHPUnit\Event;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Logging\EventLogger;
use PHPUnit\Logging\JUnit\JunitXmlLogger;
use PHPUnit\Logging\TeamCity\TeamCityLogger;
use PHPUnit\Logging\TestDox\HtmlRenderer as TestDoxHtmlRenderer;
use PHPUnit\Logging\TestDox\PlainTextRenderer as TestDoxTextRenderer;
use PHPUnit\Logging\TestDox\TestResultCollector as TestDoxResultCollector;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\Runner\ResultCache\NullResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheHandler;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Runner\Version;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\CodeCoverageReportNotConfiguredException;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\FilterNotConfiguredException;
use PHPUnit\TextUI\Configuration\LoggingNotConfiguredException;
use PHPUnit\TextUI\Configuration\NoBootstrapException;
use PHPUnit\TextUI\Configuration\NoConfigurationFileException;
use PHPUnit\TextUI\Configuration\NoCoverageCacheDirectoryException;
use PHPUnit\TextUI\Configuration\NoCustomCssFileException;
use PHPUnit\TextUI\Configuration\NoPharExtensionDirectoryException;
use PHPUnit\TextUI\Output\Default\ResultPrinter as DefaultResultPrinter;
use PHPUnit\TextUI\Output\DefaultPrinter;
use PHPUnit\TextUI\Output\Facade as OutputFacade;
use PHPUnit\TextUI\Output\NullPrinter;
use PHPUnit\TextUI\Output\Printer;
use PHPUnit\Util\DirectoryDoesNotExistException;
use PHPUnit\Util\InvalidSocketException;
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
    private Printer $printer;
    private bool $messagePrinted = false;

    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\Util\Exception
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
    public function run(Configuration $configuration, TestSuite $suite): TestResult
    {
        Event\Facade::emitter()->testRunnerStarted();

        CodeCoverageFilterRegistry::init($configuration);

        if ($configuration->loadPharExtensions() &&
            $configuration->hasPharExtensionDirectory()) {
            $pharExtensions = (new PharLoader)->loadPharExtensionsInDirectory(
                $configuration->pharExtensionDirectory()
            );
        }

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

        OutputFacade::init($configuration);

        $this->printer = OutputFacade::printer();

        TestResultFacade::init();

        if ($configuration->hasLogEventsText()) {
            if (is_file($configuration->logEventsText())) {
                unlink($configuration->logEventsText());
            }

            Event\Facade::registerTracer(
                new EventLogger(
                    $configuration->logEventsText(),
                    false
                )
            );
        }

        if ($configuration->hasLogEventsVerboseText()) {
            if (is_file($configuration->logEventsVerboseText())) {
                unlink($configuration->logEventsVerboseText());
            }

            Event\Facade::registerTracer(
                new EventLogger(
                    $configuration->logEventsVerboseText(),
                    true
                )
            );
        }

        if ($configuration->hasLogfileJunit()) {
            new JunitXmlLogger(
                $this->printerFor($configuration->logfileJunit()),
            );
        }

        if ($configuration->hasLogfileTeamcity()) {
            new TeamCityLogger(
                DefaultPrinter::from(
                    $configuration->logfileTeamcity()
                )
            );
        }

        if ($configuration->hasLogfileTestdoxHtml() ||
            $configuration->hasLogfileTestdoxText() ||
            $configuration->outputIsTestDox()) {
            $testDoxResultCollector = new TestDoxResultCollector;
        }

        Event\Facade::seal();

        $this->printer->print(Version::getVersionString() . "\n");

        if ($configuration->hasLogfileText()) {
            $textLogger = new DefaultResultPrinter(
                DefaultPrinter::from($configuration->logfileText()),
                true,
                true,
                true,
                true,
                true,
                true,
                false,
            );
        }

        CodeCoverage::init($configuration);

        $this->writeRuntimeInformation($configuration);

        if (isset($pharExtensions)) {
            $this->writePharExtensionInformation($pharExtensions);
        }

        if ($configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED) {
            $this->writeMessage(
                'Random Seed',
                (string) $configuration->randomOrderSeed()
            );
        }

        $this->printer->print("\n");

        (new TestSuiteFilterProcessor)->process($configuration, $suite);

        Event\Facade::emitter()->testRunnerExecutionStarted(
            Event\TestSuite\TestSuite::fromTestSuite($suite)
        );

        $suite->run();

        Event\Facade::emitter()->testRunnerExecutionFinished();

        $result = TestResultFacade::result();

        $testDoxResult = null;

        if (isset($testDoxResultCollector)) {
            $testDoxResult = $testDoxResultCollector->testMethodsGroupedByClass();
        }

        OutputFacade::printResult($result, $testDoxResult);

        if (isset($textLogger)) {
            $textLogger->flush();
        }

        if ($testDoxResult !== null &&
            $configuration->hasLogfileTestdoxHtml()) {
            $this->printerFor($configuration->logfileTestdoxHtml())->print(
                (new TestDoxHtmlRenderer)->render($testDoxResult)
            );
        }

        if ($testDoxResult !== null &&
            $configuration->hasLogfileTestdoxText()) {
            $this->printerFor($configuration->logfileTestdoxText())->print(
                (new TestDoxTextRenderer)->render($testDoxResult)
            );
        }

        CodeCoverage::generateReports($this->printer, $configuration);

        Event\Facade::emitter()->testRunnerFinished();

        return $result;
    }

    private function writeMessage(string $type, string $message): void
    {
        if (!$this->messagePrinted) {
            $this->printer->print("\n");
        }

        $this->printer->print(
            sprintf(
                "%-15s%s\n",
                $type . ':',
                $message
            )
        );

        $this->messagePrinted = true;
    }

    /**
     * @throws DirectoryDoesNotExistException
     * @throws InvalidSocketException
     */
    private function printerFor(string $target): Printer
    {
        if ($target === 'php://stdout') {
            if (!$this->printer instanceof NullPrinter) {
                return $this->printer;
            }

            return DefaultPrinter::standardOutput();
        }

        return DefaultPrinter::from($target);
    }

    private function writeRuntimeInformation(Configuration $configuration): void
    {
        $runtime = 'PHP ' . PHP_VERSION;

        if (CodeCoverage::isActive()) {
            $runtime .= ' with ' . CodeCoverage::driver()->nameAndVersion();
        }

        $this->writeMessage('Runtime', $runtime);

        if ($configuration->hasConfigurationFile()) {
            $this->writeMessage(
                'Configuration',
                $configuration->configurationFile()
            );
        }
    }

    /**
     * @psalm-param array{loadedExtensions: list<string>, notLoadedExtensions: list<string>} $pharExtensions
     */
    private function writePharExtensionInformation(array $pharExtensions): void
    {
        foreach ($pharExtensions['loadedExtensions'] as $extension) {
            $this->writeMessage(
                'Extension',
                $extension
            );
        }

        foreach ($pharExtensions['notLoadedExtensions'] as $extension) {
            $this->writeMessage(
                'Extension',
                $extension
            );
        }
    }
}
