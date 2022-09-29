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
use function file_put_contents;
use function htmlspecialchars;
use function is_file;
use function mt_srand;
use function range;
use function sprintf;
use function unlink;
use PHPUnit\Event;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Logging\EventLogger;
use PHPUnit\Logging\JUnit\JunitXmlLogger;
use PHPUnit\Logging\TeamCity\TeamCityLogger;
use PHPUnit\Logging\TestDox\HtmlRenderer as TestDoxHtmlRenderer;
use PHPUnit\Logging\TestDox\PlainTextRenderer as TestDoxTextRenderer;
use PHPUnit\Logging\TestDox\TestMethodCollector;
use PHPUnit\Logging\TestDox\XmlRenderer as TestDoxXmlRenderer;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\Runner\ResultCache\NullResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheHandler;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Runner\Version;
use PHPUnit\TestRunner\TestResult\Facade;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\Output\Default\ProgressPrinter\ProgressPrinter as DefaultProgressPrinter;
use PHPUnit\TextUI\Output\Default\ResultPrinter as DefaultResultPrinter;
use PHPUnit\TextUI\Output\SummaryPrinter;
use PHPUnit\TextUI\Output\TestDox\ResultPrinter as TestDoxResultPrinter;
use PHPUnit\Util\DefaultPrinter;
use PHPUnit\Util\NullPrinter;
use PHPUnit\Util\Printer;
use PHPUnit\Util\Xml\SchemaDetector;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Cobertura as CoberturaReport;
use SebastianBergmann\CodeCoverage\Report\Crap4j as Crap4jReport;
use SebastianBergmann\CodeCoverage\Report\Html\Colors;
use SebastianBergmann\CodeCoverage\Report\Html\CustomCssFile;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Thresholds;
use SebastianBergmann\CodeCoverage\Report\Xml\Facade as XmlReport;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    private Configuration $configuration;
    private Printer $printer;
    private bool $messagePrinted = false;
    private ?Timer $timer        = null;

    public function __construct()
    {
        $this->configuration = Registry::get();
    }

    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws Exception
     * @throws XmlConfiguration\Exception
     */
    public function run(TestSuite $suite): TestResult
    {
        if ($this->configuration->hasConfigurationFile()) {
            $GLOBALS['__PHPUNIT_CONFIGURATION_FILE'] = $this->configuration->configurationFile();
        }

        if ($this->configuration->loadPharExtensions() &&
            $this->configuration->hasPharExtensionDirectory()) {
            $pharExtensions = (new PharLoader)->loadPharExtensionsInDirectory(
                $this->configuration->pharExtensionDirectory()
            );
        }

        if ($this->configuration->hasBootstrap()) {
            $GLOBALS['__PHPUNIT_BOOTSTRAP'] = $this->configuration->bootstrap();
        }

        if ($this->configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED) {
            mt_srand($this->configuration->randomOrderSeed());
        }

        if ($this->configuration->cacheResult()) {
            $cache = new DefaultResultCache($this->configuration->testResultCacheFile());

            new ResultCacheHandler($cache);
        }

        if ($this->configuration->executionOrder() !== TestSuiteSorter::ORDER_DEFAULT ||
            $this->configuration->executionOrderDefects() !== TestSuiteSorter::ORDER_DEFAULT ||
            $this->configuration->resolveDependencies()) {
            $cache = $cache ?? new NullResultCache;

            $cache->load();

            $sorter = new TestSuiteSorter($cache);

            $sorter->reorderTestsInSuite(
                $suite,
                $this->configuration->executionOrder(),
                $this->configuration->resolveDependencies(),
                $this->configuration->executionOrderDefects()
            );

            Event\Facade::emitter()->testSuiteSorted(
                $this->configuration->executionOrder(),
                $this->configuration->executionOrderDefects(),
                $this->configuration->resolveDependencies()
            );

            unset($sorter);
        }

        if ($this->configuration->hasRepeat()) {
            $_suite = TestSuite::empty();

            /* @noinspection PhpUnusedLocalVariableInspection */
            foreach (range(1, $this->configuration->repeat()) as $step) {
                $_suite->addTest($suite);
            }

            $suite = $_suite;

            unset($_suite);
        }

        $this->printer = new NullPrinter;

        if ($this->useDefaultProgressPrinter() ||
            $this->useDefaultResultPrinter() ||
            $this->configuration->outputIsTestDox()) {
            if ($this->configuration->outputToStandardErrorStream()) {
                $this->printer = DefaultPrinter::standardError();
            } else {
                $this->printer = DefaultPrinter::standardOutput();
            }

            if ($this->useDefaultProgressPrinter()) {
                $progressPrinter = new DefaultProgressPrinter(
                    $this->printer,
                    $this->configuration->colors(),
                    $this->configuration->columns()
                );
            }
        }

        if ($this->useDefaultResultPrinter()) {
            $resultPrinter = new DefaultResultPrinter(
                $this->printer,
                $this->configuration->displayDetailsOnIncompleteTests(),
                $this->configuration->displayDetailsOnSkippedTests(),
                $this->configuration->displayDetailsOnTestsThatTriggerDeprecations(),
                $this->configuration->displayDetailsOnTestsThatTriggerErrors(),
                $this->configuration->displayDetailsOnTestsThatTriggerNotices(),
                $this->configuration->displayDetailsOnTestsThatTriggerWarnings(),
                $this->configuration->reverseDefectList()
            );

            $summaryPrinter = new SummaryPrinter(
                $this->printer,
                $this->configuration->colors(),
            );
        }

        Facade::init();

        if ($this->configuration->hasLogEventsText()) {
            if (is_file($this->configuration->logEventsText())) {
                unlink($this->configuration->logEventsText());
            }

            Event\Facade::registerTracer(
                new EventLogger(
                    $this->configuration->logEventsText(),
                    false
                )
            );
        }

        if ($this->configuration->hasLogEventsVerboseText()) {
            if (is_file($this->configuration->logEventsVerboseText())) {
                unlink($this->configuration->logEventsVerboseText());
            }

            Event\Facade::registerTracer(
                new EventLogger(
                    $this->configuration->logEventsVerboseText(),
                    true
                )
            );
        }

        if ($this->configuration->hasLogfileJunit()) {
            $junitXmlLogger = new JunitXmlLogger(
                $this->configuration->reportUselessTests()
            );
        }

        if ($this->configuration->hasLogfileTeamcity()) {
            $teamCityLogger = new TeamCityLogger(
                DefaultPrinter::from(
                    $this->configuration->logfileTeamcity()
                )
            );
        }

        if ($this->configuration->outputIsTeamCity()) {
            $teamCityOutput = new TeamCityLogger(
                DefaultPrinter::standardOutput()
            );
        }

        if ($this->configuration->hasLogfileTestdoxHtml() ||
            $this->configuration->hasLogfileTestdoxText() ||
            $this->configuration->hasLogfileTestdoxXml() ||
            $this->configuration->outputIsTestDox()) {
            $testDoxCollector = new TestMethodCollector;

            if ($this->configuration->outputIsTestDox()) {
                $summaryPrinter = new SummaryPrinter(
                    $this->printer,
                    $this->configuration->colors(),
                );
            }
        }

        Event\Facade::seal();

        $this->write(Version::getVersionString() . "\n");

        if ($this->configuration->hasLogfileText()) {
            $textLogger = new DefaultResultPrinter(
                DefaultPrinter::from($this->configuration->logfileText()),
                true,
                true,
                true,
                true,
                true,
                true,
                false,
            );
        }

        if ($this->configuration->hasCoverageReport()) {
            if ($this->configuration->pathCoverage()) {
                CodeCoverage::activate(CodeCoverageFilterRegistry::get(), true);
            } else {
                CodeCoverage::activate(CodeCoverageFilterRegistry::get(), false);
            }

            if ($this->configuration->hasCoverageCacheDirectory()) {
                CodeCoverage::instance()->cacheStaticAnalysis($this->configuration->coverageCacheDirectory());
            }

            CodeCoverage::instance()->excludeSubclassesOfThisClassFromUnintentionallyCoveredCodeCheck(Comparator::class);

            if ($this->configuration->strictCoverage()) {
                CodeCoverage::instance()->enableCheckForUnintentionallyCoveredCode();
            }

            if ($this->configuration->ignoreDeprecatedCodeUnitsFromCodeCoverage()) {
                CodeCoverage::instance()->ignoreDeprecatedCode();
            } else {
                CodeCoverage::instance()->doNotIgnoreDeprecatedCode();
            }

            if ($this->configuration->disableCodeCoverageIgnore()) {
                CodeCoverage::instance()->disableAnnotationsForIgnoringCode();
            } else {
                CodeCoverage::instance()->enableAnnotationsForIgnoringCode();
            }

            if ($this->configuration->includeUncoveredFiles()) {
                CodeCoverage::instance()->includeUncoveredFiles();
            } else {
                CodeCoverage::instance()->excludeUncoveredFiles();
            }

            if (CodeCoverageFilterRegistry::get()->isEmpty()) {
                if (!CodeCoverageFilterRegistry::configured()) {
                    Event\Facade::emitter()->testRunnerTriggeredWarning(
                        'No filter is configured, code coverage will not be processed'
                    );
                } else {
                    Event\Facade::emitter()->testRunnerTriggeredWarning(
                        'Incorrect filter configuration, code coverage will not be processed'
                    );
                }

                CodeCoverage::deactivate();
            }
        }

        if (PHP_SAPI === 'phpdbg') {
            $this->writeMessage('Runtime', 'PHPDBG ' . PHP_VERSION);
        } else {
            $runtime = 'PHP ' . PHP_VERSION;

            if (CodeCoverage::isActive()) {
                $runtime .= ' with ' . CodeCoverage::driver()->nameAndVersion();
            }

            $this->writeMessage('Runtime', $runtime);
        }

        if ($this->configuration->hasConfigurationFile()) {
            $this->writeMessage(
                'Configuration',
                $this->configuration->configurationFile()
            );
        }

        if (isset($pharExtensions)) {
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

        if ($this->configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED) {
            $this->writeMessage(
                'Random Seed',
                (string) $this->configuration->randomOrderSeed()
            );
        }

        if ($this->configuration->tooFewColumnsRequested()) {
            Event\Facade::emitter()->testRunnerTriggeredWarning(
                'Less than 16 columns requested, number of columns set to 16'
            );
        }

        if ($this->configuration->hasXmlValidationErrors()) {
            if ((new SchemaDetector)->detect($this->configuration->configurationFile())->detected()) {
                Event\Facade::emitter()->testRunnerTriggeredWarning(
                    'Your XML configuration validates against a deprecated schema. Migrate your XML configuration using "--migrate-configuration"!'
                );
            } else {
                Event\Facade::emitter()->testRunnerTriggeredWarning(
                    "Test results may not be as expected because the XML configuration file did not pass validation:\n" .
                    $this->configuration->xmlValidationErrors()
                );
            }
        }

        $this->write("\n");

        if ($this->configuration->enforceTimeLimit() && !(new Invoker)->canInvokeWithTimeout()) {
            Event\Facade::emitter()->testRunnerTriggeredWarning(
                'The pcntl extension is required for enforcing time limits'
            );
        }

        $this->processSuiteFilters($suite);

        Event\Facade::emitter()->testRunnerExecutionStarted(
            Event\TestSuite\TestSuite::fromTestSuite($suite)
        );

        $suite->run();

        Event\Facade::emitter()->testRunnerExecutionFinished();

        $result = Facade::result();

        if ($result->numberOfTestsRun() > 0) {
            if (isset($progressPrinter)) {
                $this->printer->print(PHP_EOL . PHP_EOL);
            }

            $this->printer->print((new ResourceUsageFormatter)->resourceUsageSinceStartOfRequest() . PHP_EOL . PHP_EOL);
        }

        if (isset($resultPrinter, $summaryPrinter)) {
            $resultPrinter->print($result);
            $summaryPrinter->print($result);
        }

        if (isset($junitXmlLogger)) {
            file_put_contents(
                $this->configuration->logfileJunit(),
                $junitXmlLogger->flush()
            );
        }

        if (isset($teamCityLogger)) {
            $teamCityLogger->flush();
        }

        if (isset($teamCityOutput)) {
            $teamCityOutput->flush();
        }

        if (isset($textLogger)) {
            $textLogger->flush();
        }

        if (isset($testDoxCollector, $summaryPrinter) &&
             $this->configuration->outputIsTestDox()) {
            (new TestDoxResultPrinter($this->printer, $this->configuration->colors()))->print(
                $testDoxCollector->testMethodsGroupedByClass()
            );

            $summaryPrinter->print($result);
        }

        if (isset($testDoxCollector) &&
            $this->configuration->hasLogfileTestdoxHtml()) {
            $this->printerFor($this->configuration->logfileTestdoxHtml())->print(
                (new TestDoxHtmlRenderer)->render(
                    $testDoxCollector->testMethodsGroupedByClass()
                )
            );
        }

        if (isset($testDoxCollector) &&
            $this->configuration->hasLogfileTestdoxText()) {
            $this->printerFor($this->configuration->logfileTestdoxText())->print(
                (new TestDoxTextRenderer)->render(
                    $testDoxCollector->testMethodsGroupedByClass()
                )
            );
        }

        if (isset($testDoxCollector) &&
            $this->configuration->hasLogfileTestdoxXml()) {
            $this->printerFor($this->configuration->logfileTestdoxXml())->print(
                (new TestDoxXmlRenderer)->render(
                    $testDoxCollector->testMethodsGroupedByClass()
                )
            );
        }

        if (CodeCoverage::isActive()) {
            if ($this->configuration->hasCoverageClover()) {
                $this->codeCoverageGenerationStart('Clover XML');

                try {
                    $writer = new CloverReport;
                    $writer->process(CodeCoverage::instance(), $this->configuration->coverageClover());

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if ($this->configuration->hasCoverageCobertura()) {
                $this->codeCoverageGenerationStart('Cobertura XML');

                try {
                    $writer = new CoberturaReport;
                    $writer->process(CodeCoverage::instance(), $this->configuration->coverageCobertura());

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if ($this->configuration->hasCoverageCrap4j()) {
                $this->codeCoverageGenerationStart('Crap4J XML');

                try {
                    $writer = new Crap4jReport($this->configuration->coverageCrap4jThreshold());
                    $writer->process(CodeCoverage::instance(), $this->configuration->coverageCrap4j());

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if ($this->configuration->hasCoverageHtml()) {
                $this->codeCoverageGenerationStart('HTML');

                try {
                    $customCssFile = CustomCssFile::default();

                    if ($this->configuration->hasCoverageHtmlCustomCssFile()) {
                        $customCssFile = CustomCssFile::from($this->configuration->coverageHtmlCustomCssFile());
                    }

                    $writer = new HtmlReport(
                        sprintf(
                            ' and <a href="https://phpunit.de/">PHPUnit %s</a>',
                            Version::id()
                        ),
                        Colors::from(
                            $this->configuration->coverageHtmlColorSuccessLow(),
                            $this->configuration->coverageHtmlColorSuccessMedium(),
                            $this->configuration->coverageHtmlColorSuccessHigh(),
                            $this->configuration->coverageHtmlColorWarning(),
                            $this->configuration->coverageHtmlColorDanger(),
                        ),
                        Thresholds::from(
                            $this->configuration->coverageHtmlLowUpperBound(),
                            $this->configuration->coverageHtmlHighLowerBound()
                        ),
                        $customCssFile
                    );

                    $writer->process(CodeCoverage::instance(), $this->configuration->coverageHtml());

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if ($this->configuration->hasCoveragePhp()) {
                $this->codeCoverageGenerationStart('PHP');

                try {
                    $writer = new PhpReport;
                    $writer->process(CodeCoverage::instance(), $this->configuration->coveragePhp());

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }

            if ($this->configuration->hasCoverageText()) {
                $processor = new TextReport(
                    Thresholds::default(),
                    $this->configuration->coverageTextShowUncoveredFiles(),
                    $this->configuration->coverageTextShowOnlySummary()
                );

                $this->printerFor($this->configuration->coverageText())->print(
                    $processor->process(CodeCoverage::instance(), $this->configuration->colors())
                );
            }

            if ($this->configuration->hasCoverageXml()) {
                $this->codeCoverageGenerationStart('PHPUnit XML');

                try {
                    $writer = new XmlReport(Version::id());
                    $writer->process(CodeCoverage::instance(), $this->configuration->coverageXml());

                    $this->codeCoverageGenerationSucceeded();

                    unset($writer);
                } catch (CodeCoverageException $e) {
                    $this->codeCoverageGenerationFailed($e);
                }
            }
        }

        return $result;
    }

    private function write(string $buffer): void
    {
        if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
            $buffer = htmlspecialchars($buffer);
        }

        $this->printer->print($buffer);
    }

    private function processSuiteFilters(TestSuite $suite): void
    {
        if (!$this->configuration->hasFilter() &&
            !$this->configuration->hasGroups() &&
            !$this->configuration->hasExcludeGroups() &&
            !$this->configuration->hasTestsCovering() &&
            !$this->configuration->hasTestsUsing()) {
            return;
        }

        $filterFactory = new Factory;

        if ($this->configuration->hasExcludeGroups()) {
            $filterFactory->addExcludeGroupFilter(
                $this->configuration->excludeGroups()
            );
        }

        if ($this->configuration->hasGroups()) {
            $filterFactory->addIncludeGroupFilter(
                $this->configuration->groups()
            );
        }

        if ($this->configuration->hasTestsCovering()) {
            $filterFactory->addIncludeGroupFilter(
                array_map(
                    static function (string $name): string
                    {
                        return '__phpunit_covers_' . $name;
                    },
                    $this->configuration->testsCovering()
                )
            );
        }

        if ($this->configuration->hasTestsUsing()) {
            $filterFactory->addIncludeGroupFilter(
                array_map(
                    static function (string $name): string
                    {
                        return '__phpunit_uses_' . $name;
                    },
                    $this->configuration->testsUsing()
                )
            );
        }

        if ($this->configuration->hasFilter()) {
            $filterFactory->addNameFilter(
                $this->configuration->filter()
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

    private function codeCoverageGenerationStart(string $format): void
    {
        $this->write(
            sprintf(
                "\nGenerating code coverage report in %s format ... ",
                $format
            )
        );

        $this->timer()->start();
    }

    private function codeCoverageGenerationSucceeded(): void
    {
        $this->write(
            sprintf(
                "done [%s]\n",
                $this->timer()->stop()->asString()
            )
        );
    }

    private function codeCoverageGenerationFailed(CodeCoverageException $e): void
    {
        $this->write(
            sprintf(
                "failed [%s]\n%s\n",
                $this->timer()->stop()->asString(),
                $e->getMessage()
            )
        );
    }

    private function timer(): Timer
    {
        if ($this->timer === null) {
            $this->timer = new Timer;
        }

        return $this->timer;
    }

    private function printerFor(string $target): Printer
    {
        if ($target === 'php://stdout') {
            return $this->printer;
        }

        return DefaultPrinter::from($target);
    }

    private function useDefaultProgressPrinter(): bool
    {
        if ($this->configuration->noOutput()) {
            return false;
        }

        if ($this->configuration->noProgress()) {
            return false;
        }

        if ($this->configuration->outputIsTeamCity()) {
            return false;
        }

        return true;
    }

    private function useDefaultResultPrinter(): bool
    {
        if ($this->configuration->noOutput()) {
            return false;
        }

        if ($this->configuration->noResults()) {
            return false;
        }

        if ($this->configuration->outputIsTeamCity()) {
            return false;
        }

        if ($this->configuration->outputIsTestDox()) {
            return false;
        }

        return true;
    }
}
