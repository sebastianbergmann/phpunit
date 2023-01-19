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

use const PHP_SAPI;
use const PHP_VERSION;
use function array_map;
use function htmlspecialchars;
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
use PHPUnit\Logging\TestDox\XmlRenderer as TestDoxXmlRenderer;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\Filter\Factory;
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
use PHPUnit\TextUI\Output\Default\ProgressPrinter\ProgressPrinter as DefaultProgressPrinter;
use PHPUnit\TextUI\Output\Default\ResultPrinter as DefaultResultPrinter;
use PHPUnit\TextUI\Output\SummaryPrinter;
use PHPUnit\TextUI\Output\TestDox\ResultPrinter as TestDoxResultPrinter;
use PHPUnit\Util\DefaultPrinter;
use PHPUnit\Util\DirectoryDoesNotExistException;
use PHPUnit\Util\InvalidSocketException;
use PHPUnit\Util\NullPrinter;
use PHPUnit\Util\Printer;
use SebastianBergmann\CodeCoverage\Driver\PcovNotAvailableException;
use SebastianBergmann\CodeCoverage\Driver\XdebugNotAvailableException;
use SebastianBergmann\CodeCoverage\Driver\XdebugNotEnabledException;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\NoCodeCoverageDriverAvailableException;
use SebastianBergmann\CodeCoverage\NoCodeCoverageDriverWithPathCoverageSupportAvailableException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Timer\NoActiveTimerException;
use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;
use SebastianBergmann\Timer\TimeSinceStartOfRequestNotAvailableException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    private Printer $printer;
    private bool $messagePrinted = false;
    private ?Timer $timer        = null;

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
     * @throws NoActiveTimerException
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

        if ($configuration->hasCoverageReport()) {
            CodeCoverageFilterRegistry::init($configuration);
        }

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

        $this->printer = new NullPrinter;

        if ($this->useDefaultProgressPrinter($configuration) ||
            $this->useDefaultResultPrinter($configuration) ||
            $configuration->outputIsTestDox()) {
            if ($configuration->outputToStandardErrorStream()) {
                $this->printer = DefaultPrinter::standardError();
            } else {
                $this->printer = DefaultPrinter::standardOutput();
            }

            if ($this->useDefaultProgressPrinter($configuration)) {
                new DefaultProgressPrinter(
                    $this->printer,
                    $configuration->colors(),
                    $configuration->columns()
                );
            }
        }

        if ($this->useDefaultResultPrinter($configuration)) {
            $resultPrinter = new DefaultResultPrinter(
                $this->printer,
                $configuration->displayDetailsOnIncompleteTests(),
                $configuration->displayDetailsOnSkippedTests(),
                $configuration->displayDetailsOnTestsThatTriggerDeprecations(),
                $configuration->displayDetailsOnTestsThatTriggerErrors(),
                $configuration->displayDetailsOnTestsThatTriggerNotices(),
                $configuration->displayDetailsOnTestsThatTriggerWarnings(),
                $configuration->reverseDefectList()
            );

            $summaryPrinter = new SummaryPrinter(
                $this->printer,
                $configuration->colors(),
            );
        }

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

        if ($configuration->outputIsTeamCity()) {
            new TeamCityLogger(
                DefaultPrinter::standardOutput()
            );
        }

        if ($configuration->hasLogfileTestdoxHtml() ||
            $configuration->hasLogfileTestdoxText() ||
            $configuration->hasLogfileTestdoxXml() ||
            $configuration->outputIsTestDox()) {
            $testDoxResultCollector = new TestDoxResultCollector;

            if ($configuration->outputIsTestDox()) {
                $summaryPrinter = new SummaryPrinter(
                    $this->printer,
                    $configuration->colors(),
                );
            }
        }

        Event\Facade::seal();

        $this->write(Version::getVersionString() . "\n");

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

        $this->write("\n");

        $this->processSuiteFilters($configuration, $suite);

        Event\Facade::emitter()->testRunnerExecutionStarted(
            Event\TestSuite\TestSuite::fromTestSuite($suite)
        );

        $suite->run();

        Event\Facade::emitter()->testRunnerExecutionFinished();

        $result = TestResultFacade::result();

        if ($result->numberOfTestsRun() > 0) {
            if ($this->useDefaultProgressPrinter($configuration)) {
                $this->printer->print(PHP_EOL . PHP_EOL);
            }

            $this->printer->print((new ResourceUsageFormatter)->resourceUsageSinceStartOfRequest() . PHP_EOL . PHP_EOL);
        }

        if (isset($resultPrinter, $summaryPrinter)) {
            $resultPrinter->print($result);
            $summaryPrinter->print($result);
        }

        if (isset($textLogger)) {
            $textLogger->flush();
        }

        if (isset($testDoxResultCollector, $summaryPrinter) &&
             $configuration->outputIsTestDox()) {
            (new TestDoxResultPrinter($this->printer, $configuration->colors()))->print(
                $testDoxResultCollector->testMethodsGroupedByClass()
            );

            $summaryPrinter->print($result);
        }

        if (isset($testDoxResultCollector) &&
            $configuration->hasLogfileTestdoxHtml()) {
            $this->printerFor($configuration->logfileTestdoxHtml())->print(
                (new TestDoxHtmlRenderer)->render(
                    $testDoxResultCollector->testMethodsGroupedByClass()
                )
            );
        }

        if (isset($testDoxResultCollector) &&
            $configuration->hasLogfileTestdoxText()) {
            $this->printerFor($configuration->logfileTestdoxText())->print(
                (new TestDoxTextRenderer)->render(
                    $testDoxResultCollector->testMethodsGroupedByClass()
                )
            );
        }

        if (isset($testDoxResultCollector) &&
            $configuration->hasLogfileTestdoxXml()) {
            $this->printerFor($configuration->logfileTestdoxXml())->print(
                (new TestDoxXmlRenderer)->render(
                    $testDoxResultCollector->testMethodsGroupedByClass()
                )
            );
        }

        CodeCoverage::generateReports($this->printer, $configuration);

        Event\Facade::emitter()->testRunnerFinished();

        return $result;
    }

    private function write(string $buffer): void
    {
        if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
            $buffer = htmlspecialchars($buffer);
        }

        $this->printer->print($buffer);
    }

    /**
     * @throws Event\RuntimeException
     * @throws FilterNotConfiguredException
     */
    private function processSuiteFilters(Configuration $configuration, TestSuite $suite): void
    {
        if (!$configuration->hasFilter() &&
            !$configuration->hasGroups() &&
            !$configuration->hasExcludeGroups() &&
            !$configuration->hasTestsCovering() &&
            !$configuration->hasTestsUsing()) {
            return;
        }

        $filterFactory = new Factory;

        if ($configuration->hasExcludeGroups()) {
            $filterFactory->addExcludeGroupFilter(
                $configuration->excludeGroups()
            );
        }

        if ($configuration->hasGroups()) {
            $filterFactory->addIncludeGroupFilter(
                $configuration->groups()
            );
        }

        if ($configuration->hasTestsCovering()) {
            $filterFactory->addIncludeGroupFilter(
                array_map(
                    static fn (string $name): string => '__phpunit_covers_' . $name,
                    $configuration->testsCovering()
                )
            );
        }

        if ($configuration->hasTestsUsing()) {
            $filterFactory->addIncludeGroupFilter(
                array_map(
                    static fn (string $name): string => '__phpunit_uses_' . $name,
                    $configuration->testsUsing()
                )
            );
        }

        if ($configuration->hasFilter()) {
            $filterFactory->addNameFilter(
                $configuration->filter()
            );
        }

        $suite->injectFilter($filterFactory);

        Event\Facade::emitter()->testSuiteFiltered(
            Event\TestSuite\TestSuite::fromTestSuite($suite)
        );
    }

    private function writeMessage(string $type, string $message): void
    {
        if (!$this->messagePrinted) {
            $this->write("\n");
        }

        $this->write(
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

    private function useDefaultProgressPrinter(Configuration $configuration): bool
    {
        if ($configuration->noOutput()) {
            return false;
        }

        if ($configuration->noProgress()) {
            return false;
        }

        if ($configuration->outputIsTeamCity()) {
            return false;
        }

        return true;
    }

    private function useDefaultResultPrinter(Configuration $configuration): bool
    {
        if ($configuration->noOutput()) {
            return false;
        }

        if ($configuration->noResults()) {
            return false;
        }

        if ($configuration->outputIsTeamCity()) {
            return false;
        }

        if ($configuration->outputIsTestDox()) {
            return false;
        }

        return true;
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
