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

use const PHP_EOL;
use const PHP_VERSION;
use const SIGINT;
use function array_reverse;
use function assert;
use function class_exists;
use function count;
use function defined;
use function dirname;
use function explode;
use function function_exists;
use function getmypid;
use function is_array;
use function is_file;
use function is_string;
use function method_exists;
use function pcntl_async_signals;
use function pcntl_signal;
use function printf;
use function realpath;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function trim;
use function unlink;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Logging\EventLogger;
use PHPUnit\Logging\JUnit\JunitXmlLogger;
use PHPUnit\Logging\OpenTestReporting\CannotOpenUriForWritingException;
use PHPUnit\Logging\OpenTestReporting\OtrXmlLogger;
use PHPUnit\Logging\TeamCity\TeamCityLogger;
use PHPUnit\Logging\TestDox\HtmlRenderer as TestDoxHtmlRenderer;
use PHPUnit\Logging\TestDox\PlainTextRenderer as TestDoxTextRenderer;
use PHPUnit\Logging\TestDox\TestResultCollector as TestDoxResultCollector;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Runner\Baseline\CannotLoadBaselineException;
use PHPUnit\Runner\Baseline\Generator as BaselineGenerator;
use PHPUnit\Runner\Baseline\Reader;
use PHPUnit\Runner\Baseline\Writer;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\CodeCoverageInitializationStatus;
use PHPUnit\Runner\DeprecationCollector\Facade as DeprecationCollector;
use PHPUnit\Runner\DeprecationFilter;
use PHPUnit\Runner\DirectoryDoesNotExistException;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\Exception as RunnerException;
use PHPUnit\Runner\Extension\ExtensionBootstrapper;
use PHPUnit\Runner\Extension\ExtensionCapabilities;
use PHPUnit\Runner\Extension\ExtensionFacade;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\GarbageCollection\GarbageCollectionHandler;
use PHPUnit\Runner\IssueTriggerResolver\Resolver;
use PHPUnit\Runner\PhpConfiguration\PhpConfigurationChecker;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Runner\TestImpactAnalysis\Assumptions;
use PHPUnit\Runner\TestImpactAnalysis\DefaultTestImpactData;
use PHPUnit\Runner\TestImpactAnalysis\Provenance;
use PHPUnit\Runner\TestImpactAnalysis\Selection;
use PHPUnit\Runner\TestImpactAnalysis\Selector;
use PHPUnit\Runner\TestImpactAnalysis\TestImpactData;
use PHPUnit\Runner\TestImpactAnalysis\TestImpactDataFile;
use PHPUnit\Runner\TestImpactAnalysis\TestImpactDataFromCoverageTargets;
use PHPUnit\Runner\TestIndex\DefaultTestFileSkipper;
use PHPUnit\Runner\TestIndex\GroupPruner;
use PHPUnit\Runner\TestIndex\NameFilterPruner;
use PHPUnit\Runner\TestIndex\NullTestFileSkipper;
use PHPUnit\Runner\TestIndex\TestFileSkipper;
use PHPUnit\Runner\TestIndex\TestIndex;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;
use PHPUnit\Runner\TestRunHistory\NullTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryHandler;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Runner\Version;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\CliArguments\Exception as ArgumentsException;
use PHPUnit\TextUI\CliArguments\XmlConfigurationFileFinder;
use PHPUnit\TextUI\Command\AtLeastVersionCommand;
use PHPUnit\TextUI\Command\CheckPhpConfigurationCommand;
use PHPUnit\TextUI\Command\GenerateConfigurationCommand;
use PHPUnit\TextUI\Command\ListGroupsCommand;
use PHPUnit\TextUI\Command\ListTestFilesCommand;
use PHPUnit\TextUI\Command\ListTestIdsCommand;
use PHPUnit\TextUI\Command\ListTestsAsTextCommand;
use PHPUnit\TextUI\Command\ListTestsAsXmlCommand;
use PHPUnit\TextUI\Command\ListTestsThatDependOnCommand;
use PHPUnit\TextUI\Command\ListTestSuitesCommand;
use PHPUnit\TextUI\Command\MigrateConfigurationCommand;
use PHPUnit\TextUI\Command\Result;
use PHPUnit\TextUI\Command\ShowHelpCommand;
use PHPUnit\TextUI\Command\ShowVersionCommand;
use PHPUnit\TextUI\Command\ValidateConfigurationCommand;
use PHPUnit\TextUI\Command\VersionCheckCommand;
use PHPUnit\TextUI\Command\WarmCodeCoverageCacheCommand;
use PHPUnit\TextUI\Configuration\BootstrapLoader;
use PHPUnit\TextUI\Configuration\BootstrapScriptDoesNotExistException;
use PHPUnit\TextUI\Configuration\BootstrapScriptException;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\PhpHandler;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\Configuration\TestSuiteBuilder;
use PHPUnit\TextUI\Output\DefaultPrinter;
use PHPUnit\TextUI\Output\Facade as OutputFacade;
use PHPUnit\TextUI\Output\Printer;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use PHPUnit\Util\DifferBuilder;
use PHPUnit\Util\Http\PhpDownloader;
use SebastianBergmann\Timer\Timer;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Application
{
    /**
     * @param list<string> $argv
     */
    public function run(array $argv): int
    {
        try {
            EventFacade::emitter()->applicationStarted();

            $cliConfiguration           = $this->buildCliConfiguration($argv);
            $pathToXmlConfigurationFile = (new XmlConfigurationFileFinder)->find($cliConfiguration);

            $this->executeCommandsThatOnlyRequireCliConfiguration($cliConfiguration, $pathToXmlConfigurationFile);

            // the commands above end the process; preloading is therefore only
            // worthwhile once it is known that tests are going to be run
            $this->preload();

            $xmlConfiguration = $this->loadXmlConfiguration($pathToXmlConfigurationFile);

            $configuration = Registry::init(
                $cliConfiguration,
                $xmlConfiguration,
            );

            DifferBuilder::configureComparatorFactory();

            (new PhpHandler)->handle($configuration->php());

            try {
                (new BootstrapLoader)->handle($configuration);
            } catch (BootstrapScriptDoesNotExistException|BootstrapScriptException $e) {
                $this->exitWithErrorMessage($e->getMessage());
            }

            $this->executeCommandsThatDoNotRequireTheTestSuite($configuration, $cliConfiguration);

            $pharExtensions        = null;
            $extensionCapabilities = ExtensionCapabilities::none();

            if (!$configuration->noExtensions()) {
                if ($configuration->hasPharExtensionDirectory()) {
                    $pharExtensions = (new PharLoader)->loadPharExtensionsInDirectory(
                        $configuration->pharExtensionDirectory(),
                    );
                }

                $extensionCapabilities = $this->bootstrapExtensions($configuration);
            }

            $printer = OutputFacade::init(
                $configuration,
                $extensionCapabilities,
            );

            if ($configuration->debug()) {
                EventFacade::instance()->registerTracer(
                    new EventLogger(
                        'php://stdout',
                        $configuration->withTelemetry(),
                    ),
                );
            }

            TestResultFacade::init();
            DeprecationCollector::init();

            $this->registerLogfileWriters($configuration);

            $testDoxResultCollector = $this->testDoxResultCollector($configuration);

            $testRunHistory = $this->initializeTestRunHistory($configuration);

            if ($configuration->controlGarbageCollector()) {
                new GarbageCollectionHandler(
                    EventFacade::instance(),
                    $configuration->numberOfTestsBeforeGarbageCollection(),
                );
            }

            $baselineGenerator = $this->configureBaseline($configuration);

            $this->checkPhpConfiguration($configuration);

            EventFacade::instance()->seal();

            $this->configureDeprecationTriggers($configuration);
            $this->configureIssueTriggerResolvers($configuration);
            $this->configureDeprecationFilters($configuration);

            ErrorHandler::instance()->registerForNonTestCaseContext();

            $testSuite = $this->buildTestSuite($configuration, $cliConfiguration);

            if ($configuration->hasTestIdFilterFile() && !is_file($configuration->testIdFilterFile())) {
                $this->exitWithErrorMessage(
                    sprintf(
                        'Test ID filter file "%s" not found',
                        $configuration->testIdFilterFile(),
                    ),
                );
            }

            ErrorHandler::instance()->restoreForNonTestCaseContext();

            $this->executeCommandsThatRequireTheTestSuite($configuration, $cliConfiguration, $testSuite);

            /*
             * The help is only shown when no tests were selected at all. Tests
             * that were selected but did not end up in the test suite are not
             * the same thing: naming a file that contains no test, or a test
             * file that does not have to be loaded, is not a usage error.
             */
            if ($testSuite->isEmpty() && !$configuration->hasCliArguments() && !$configuration->hasTestFilesFile() && $configuration->testSuite()->isEmpty()) {
                $this->execute(new ShowHelpCommand(Result::FAILURE));
            }

            $this->warnAboutTestImpactDataThatCannotBeRecorded($configuration);

            $testImpactData = $this->deriveTestImpactDataFromCoverageTargets($configuration, $testSuite);

            $coverageInitializationStatus = CodeCoverage::instance()->init(
                $configuration,
                CodeCoverageFilterRegistry::instance(),
                $extensionCapabilities->requiresCodeCoverageCollection(),
            );

            $selection = $this->selectTests($configuration, $cliConfiguration, $testSuite, $testRunHistory);

            if (!$configuration->debug() && !$extensionCapabilities->replacesOutput()) {
                $this->writeRuntimeInformation($printer, $configuration);
                $this->writePharExtensionInformation($printer, $pharExtensions);
                $this->writeRandomSeedInformation($printer, $configuration);
                $this->writeTestSelectionInformation($printer, $selection);

                $printer->print(PHP_EOL);
            }

            $this->registerInterruptHandler();

            $timer = new Timer;
            $timer->start();

            if ($coverageInitializationStatus === CodeCoverageInitializationStatus::NOT_REQUESTED ||
                $coverageInitializationStatus === CodeCoverageInitializationStatus::SUCCEEDED) {
                $runner = new TestRunner;

                $selectedTests = null;

                if ($selection !== null && !$selection->isEverything()) {
                    $selectedTests = $selection->tests();
                }

                $runner->run(
                    $configuration,
                    $testRunHistory,
                    $testSuite,
                    $selectedTests,
                );
            }

            $duration = $timer->stop();

            $this->persistTestImpactData($configuration, $testImpactData);

            $testDoxResult = null;

            if (isset($testDoxResultCollector)) {
                $testDoxResult = $testDoxResultCollector->testMethodsGroupedByClass();
            }

            if ($testDoxResult !== null &&
                $configuration->hasLogfileTestdoxHtml()) {
                try {
                    OutputFacade::printerFor($configuration->logfileTestdoxHtml())->print(
                        (new TestDoxHtmlRenderer)->render($testDoxResult),
                    );
                } catch (DirectoryDoesNotExistException|InvalidSocketException $e) {
                    EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                        sprintf(
                            'Cannot log test results in TestDox HTML format to "%s": %s',
                            $configuration->logfileTestdoxHtml(),
                            $e->getMessage(),
                        ),
                    );
                }
            }

            if ($testDoxResult !== null &&
                $configuration->hasLogfileTestdoxText()) {
                try {
                    OutputFacade::printerFor($configuration->logfileTestdoxText())->print(
                        (new TestDoxTextRenderer)->render($testDoxResult),
                    );
                } catch (DirectoryDoesNotExistException|InvalidSocketException $e) {
                    EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                        sprintf(
                            'Cannot log test results in TestDox plain text format to "%s": %s',
                            $configuration->logfileTestdoxText(),
                            $e->getMessage(),
                        ),
                    );
                }
            }

            CodeCoverage::instance()->warnAboutFilesThatCouldNotBeParsed();

            $result = TestResultFacade::result();

            if (TestResultFacade::wasInterrupted()) {
                if (!$extensionCapabilities->replacesResultOutput() && !$configuration->debug()) {
                    $printer->print(PHP_EOL . PHP_EOL);
                }

                $printer->print('Test execution was interrupted by a signal.');

                if ($extensionCapabilities->replacesResultOutput() || $configuration->debug()) {
                    $printer->print(PHP_EOL);
                }
            }

            if (!$extensionCapabilities->replacesResultOutput() && !$configuration->debug()) {
                OutputFacade::printResult(
                    $result,
                    $testDoxResult,
                    $duration,
                    $configuration->hasSpecificDeprecationToStopOn(),
                );
            }

            if (!TestResultFacade::wasInterrupted()) {
                CodeCoverage::instance()->generateReports($printer, $configuration);

                if (isset($baselineGenerator)) {
                    (new Writer)->write(
                        $configuration->generateBaseline(),
                        $baselineGenerator->baseline(),
                    );

                    $printer->print(
                        sprintf(
                            PHP_EOL . 'Baseline written to %s.' . PHP_EOL,
                            realpath($configuration->generateBaseline()),
                        ),
                    );
                }
            }

            $shellExitCode = (new ShellExitCodeCalculator)->calculate(
                $configuration,
                $result,
            );

            EventFacade::emitter()->applicationFinished($shellExitCode);

            return $shellExitCode;
            // @codeCoverageIgnoreStart
        } catch (Throwable $t) {
            $this->exitWithCrashMessage($t);
        }
        // @codeCoverageIgnoreEnd
    }

    private function execute(Command\Command $command, bool $requiresResultCollectedFromEvents = false): never
    {
        $errored = false;

        if ($requiresResultCollectedFromEvents) {
            try {
                TestResultFacade::init();
                EventFacade::instance()->seal();

                $resultCollectedFromEvents = TestResultFacade::result();

                $errored = $resultCollectedFromEvents->hasTestTriggeredPhpunitErrorEvents();
                // @codeCoverageIgnoreStart
            } catch (EventFacadeIsSealedException|UnknownSubscriberTypeException) {
            }
            // @codeCoverageIgnoreEnd
        }

        print Version::getVersionString() . PHP_EOL . PHP_EOL;

        if (!$errored) {
            $result = $command->execute();

            print $result->output();

            exit($result->shellExitCode());
        }

        assert(isset($resultCollectedFromEvents));

        print 'There were errors:' . PHP_EOL;

        foreach ($resultCollectedFromEvents->testTriggeredPhpunitErrorEvents() as $events) {
            foreach ($events as $event) {
                print PHP_EOL . trim($event->message()) . PHP_EOL;
            }
        }

        exit(Result::EXCEPTION);
    }

    /**
     * @param list<string> $argv
     */
    private function buildCliConfiguration(array $argv): CliConfiguration
    {
        try {
            $cliConfiguration = (new Builder)->fromParameters($argv);
        } catch (ArgumentsException $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }

        return $cliConfiguration;
    }

    private function loadXmlConfiguration(false|string $configurationFile): XmlConfiguration
    {
        if ($configurationFile === false) {
            return DefaultConfiguration::create();
        }

        try {
            return (new Loader)->load($configurationFile);
        } catch (Throwable $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }
    }

    private function buildTestSuite(Configuration $configuration, CliConfiguration $cliConfiguration): TestSuite
    {
        try {
            return new TestSuiteBuilder($this->initializeTestIndex($configuration, $cliConfiguration))->build($configuration);
        } catch (Exception $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }
    }

    private function bootstrapExtensions(Configuration $configuration): ExtensionCapabilities
    {
        $facade = new ExtensionFacade;

        $extensionBootstrapper = new ExtensionBootstrapper(
            $configuration,
            $facade,
        );

        foreach ($configuration->extensionBootstrappers() as $bootstrapper) {
            $extensionBootstrapper->bootstrap(
                $bootstrapper['className'],
                $bootstrapper['parameters'],
            );
        }

        return $facade->capabilities();
    }

    private function executeCommandsThatOnlyRequireCliConfiguration(CliConfiguration $cliConfiguration, false|string $configurationFile): void
    {
        if ($cliConfiguration->generateConfiguration()) {
            $this->execute(new GenerateConfigurationCommand);
        }

        if ($cliConfiguration->migrateConfiguration()) {
            if ($configurationFile === false) {
                $this->exitWithErrorMessage('No configuration file found to migrate');
            }

            $resolved = realpath($configurationFile);

            // @codeCoverageIgnoreStart
            if ($resolved === false) {
                $this->exitWithErrorMessage('Configuration file cannot be migrated');
            }
            // @codeCoverageIgnoreEnd

            $this->execute(new MigrateConfigurationCommand($resolved));
        }

        if ($cliConfiguration->validateConfiguration()) {
            if ($configurationFile === false) {
                $this->exitWithErrorMessage('No configuration file found to validate');
            }

            $resolved = realpath($configurationFile);

            // @codeCoverageIgnoreStart
            if ($resolved === false) {
                $this->exitWithErrorMessage('Configuration file cannot be validated');
            }
            // @codeCoverageIgnoreEnd

            $this->execute(new ValidateConfigurationCommand($resolved));
        }

        if ($cliConfiguration->hasAtLeastVersion()) {
            $this->execute(new AtLeastVersionCommand($cliConfiguration->atLeastVersion()));
        }

        if ($cliConfiguration->version()) {
            $this->execute(new ShowVersionCommand);
        }

        if ($cliConfiguration->checkPhpConfiguration()) {
            $this->execute(new CheckPhpConfigurationCommand);
        }

        if ($cliConfiguration->checkVersion()) {
            $this->execute(new VersionCheckCommand(new PhpDownloader, Version::majorVersionNumber(), Version::id()));
        }

        if ($cliConfiguration->help()) {
            $this->execute(new ShowHelpCommand(Result::SUCCESS));
        }
    }

    private function executeCommandsThatDoNotRequireTheTestSuite(Configuration $configuration, CliConfiguration $cliConfiguration): void
    {
        if ($cliConfiguration->warmCoverageCache()) {
            $this->execute(new WarmCodeCoverageCacheCommand($configuration, CodeCoverageFilterRegistry::instance()));
        }

        if ($cliConfiguration->hasListTestsThatDependOn()) {
            if (!$configuration->hasCacheDirectory()) {
                $this->exitWithErrorMessage('Cannot list tests that executed a source file because no cache directory is configured');
            }

            $this->execute(
                new ListTestsThatDependOnCommand(
                    new TestImpactDataFile(
                        $configuration->cacheDirectory(),
                        $this->assumptionsOf($configuration),
                    ),
                    $cliConfiguration->listTestsThatDependOn(),
                ),
            );
        }
    }

    private function executeCommandsThatRequireTheTestSuite(Configuration $configuration, CliConfiguration $cliConfiguration, TestSuite $testSuite): void
    {
        if ($cliConfiguration->listSuites()) {
            $this->execute(new ListTestSuitesCommand($testSuite));
        }

        if ($cliConfiguration->listGroups()) {
            $this->execute(
                new ListGroupsCommand(
                    $this->filteredTests(
                        $configuration,
                        $testSuite,
                    ),
                ),
                true,
            );
        }

        if ($cliConfiguration->listTestIds()) {
            $this->execute(
                new ListTestIdsCommand(
                    $this->filteredTests(
                        $configuration,
                        $testSuite,
                    ),
                ),
                true,
            );
        }

        if ($cliConfiguration->listTests()) {
            $this->execute(
                new ListTestsAsTextCommand(
                    $this->filteredTests(
                        $configuration,
                        $testSuite,
                    ),
                ),
                true,
            );
        }

        if ($cliConfiguration->hasListTestsXml()) {
            $this->execute(
                new ListTestsAsXmlCommand(
                    $this->filteredTests(
                        $configuration,
                        $testSuite,
                    ),
                    $cliConfiguration->listTestsXml(),
                ),
                true,
            );
        }

        if ($cliConfiguration->listTestFiles()) {
            $this->execute(
                new ListTestFilesCommand(
                    $this->filteredTests(
                        $configuration,
                        $testSuite,
                    ),
                ),
                true,
            );
        }
    }

    private function writeRuntimeInformation(Printer $printer, Configuration $configuration): void
    {
        $printer->print(Version::getVersionString() . PHP_EOL . PHP_EOL);

        $runtime = 'PHP ' . PHP_VERSION;

        if (CodeCoverage::instance()->isActive()) {
            $runtime .= ' with ' . CodeCoverage::instance()->driverNameAndVersion();
        }

        $this->writeMessage($printer, 'Runtime', $runtime);

        if ($configuration->hasConfigurationFile()) {
            $this->writeMessage(
                $printer,
                'Configuration',
                $configuration->configurationFile(),
            );
        }
    }

    /**
     * @param ?list<string> $pharExtensions
     */
    private function writePharExtensionInformation(Printer $printer, ?array $pharExtensions): void
    {
        if ($pharExtensions === null) {
            return;
        }

        foreach ($pharExtensions as $extension) {
            $this->writeMessage(
                $printer,
                'Extension',
                $extension,
            );
        }
    }

    private function writeMessage(Printer $printer, string $type, string $message): void
    {
        $printer->print(
            sprintf(
                "%-15s%s\n",
                $type . ':',
                $message,
            ),
        );
    }

    private function writeRandomSeedInformation(Printer $printer, Configuration $configuration): void
    {
        if ($configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED) {
            $this->writeMessage(
                $printer,
                'Random Seed',
                (string) $configuration->randomOrderSeed(),
            );
        }
    }

    private function registerLogfileWriters(Configuration $configuration): void
    {
        if ($configuration->hasLogEventsText()) {
            if (is_file($configuration->logEventsText())) {
                unlink($configuration->logEventsText());
            }

            EventFacade::instance()->registerTracer(
                new EventLogger(
                    $configuration->logEventsText(),
                    $configuration->withTelemetry(),
                ),
            );
        }

        if ($configuration->hasLogEventsVerboseText()) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitDeprecation(
                'The "--log-events-verbose-text <file>" CLI option is deprecated and will be removed in PHPUnit 14. Use "--log-events-text <file> --with-telemetry" instead.',
            );

            if (is_file($configuration->logEventsVerboseText())) {
                unlink($configuration->logEventsVerboseText());
            }

            EventFacade::instance()->registerTracer(
                new EventLogger(
                    $configuration->logEventsVerboseText(),
                    true,
                ),
            );
        }

        if ($configuration->hasLogfileJunit()) {
            try {
                new JunitXmlLogger(
                    OutputFacade::printerFor($configuration->logfileJunit()),
                    EventFacade::instance(),
                );
            } catch (DirectoryDoesNotExistException|InvalidSocketException $e) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Cannot log test results in JUnit XML format to "%s": %s',
                        $configuration->logfileJunit(),
                        $e->getMessage(),
                    ),
                );
            }
        }

        if ($configuration->hasLogfileOtr()) {
            try {
                new OtrXmlLogger(
                    EventFacade::instance(),
                    $configuration->logfileOtr(),
                    $configuration->includeGitInformationInOtrLogfile(),
                    $configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED ? $configuration->randomOrderSeed() : null,
                );
            } catch (CannotOpenUriForWritingException $e) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Cannot log test results in Open Test Reporting XML format to "%s": %s',
                        $configuration->logfileOtr(),
                        $e->getMessage(),
                    ),
                );
            }
        }

        if ($configuration->hasLogfileTeamcity()) {
            try {
                new TeamCityLogger(
                    DefaultPrinter::from(
                        $configuration->logfileTeamcity(),
                    ),
                    EventFacade::instance(),
                );
            } catch (DirectoryDoesNotExistException|InvalidSocketException $e) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Cannot log test results in TeamCity format to "%s": %s',
                        $configuration->logfileTeamcity(),
                        $e->getMessage(),
                    ),
                );
            }
        }
    }

    private function testDoxResultCollector(Configuration $configuration): ?TestDoxResultCollector
    {
        if ($configuration->hasLogfileTestdoxHtml() ||
            $configuration->hasLogfileTestdoxText() ||
            $configuration->outputIsTestDox()) {
            return new TestDoxResultCollector(
                EventFacade::instance(),
                new IssueFilter($configuration->source()),
            );
        }

        return null;
    }

    /**
     * What each test executed is only worth recording when there is somewhere
     * to keep it.
     */
    private function warnAboutTestImpactDataThatCannotBeRecorded(Configuration $configuration): void
    {
        if (!$configuration->recordTestImpactData() || $configuration->hasCacheDirectory()) {
            return;
        }

        EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
            'Cannot record test impact data because no cache directory is configured',
        );
    }

    /**
     * What each test depends on is worked out from the code coverage targets it
     * declares before the tests are run: it does not depend on running them,
     * and it is worked out for every test that was selected for this run, and
     * not only for the tests that end up being executed.
     */
    private function deriveTestImpactDataFromCoverageTargets(Configuration $configuration, TestSuite $testSuite): ?TestImpactData
    {
        if (!$configuration->deriveTestImpactDataFromCoverageTargets() || !$configuration->hasCacheDirectory()) {
            return null;
        }

        if (!$configuration->strictCoverage()) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                'Test impact data derived from code coverage targets is only as complete as those targets are, ' .
                'and they are not checked because tests that execute code they do not declare are not considered risky',
            );
        }

        CodeCoverageFilterRegistry::instance()->init($configuration, true);

        $staticAnalysisCacheDirectory = null;

        if ($configuration->hasCoverageCacheDirectory()) {
            $staticAnalysisCacheDirectory = $configuration->coverageCacheDirectory();
        }

        $testImpactData = new DefaultTestImpactData;

        TestImpactDataFromCoverageTargets::using(
            CodeCoverageFilterRegistry::instance()->get(),
            $staticAnalysisCacheDirectory,
            !$configuration->disableCodeCoverageIgnore(),
            $configuration->ignoreDeprecatedCodeUnitsFromCodeCoverage(),
        )->record($testSuite->collect(), $testImpactData);

        return $testImpactData;
    }

    /**
     * Which tests can be affected by what changed, or null when the tests are
     * not selected by what changed.
     */
    private function selectTests(Configuration $configuration, CliConfiguration $cliConfiguration, TestSuite $testSuite, TestRunHistory $testRunHistory): ?Selection
    {
        if (!$cliConfiguration->onlyImpacted()) {
            return null;
        }

        if (!$configuration->hasCacheDirectory()) {
            $this->exitWithErrorMessage('Cannot run only the tests that are affected by what changed because no cache directory is configured');
        }

        if (!$configuration->recordTestRunHistory()) {
            $this->exitWithErrorMessage('Cannot run only the tests that are affected by what changed because the test run history is not recorded');
        }

        CodeCoverageFilterRegistry::instance()->init($configuration, true);

        $testRunHistory->load();

        return new Selector(
            new TestImpactDataFile($configuration->cacheDirectory(), $this->assumptionsOf($configuration)),
            $testRunHistory,
        )->select($testSuite->collect(), $this->sourceFiles());
    }

    private function writeTestSelectionInformation(Printer $printer, ?Selection $selection): void
    {
        if ($selection === null) {
            return;
        }

        if ($selection->isEverything()) {
            $printer->print(
                sprintf(
                    'Impact:        every test is run: %s%s',
                    $selection->reason(),
                    PHP_EOL,
                ),
            );

            return;
        }

        $printer->print(
            sprintf(
                'Impact:        %s; %d test%s not run%s',
                $selection->reason(),
                $selection->numberOfTestsThatAreNotRun(),
                $selection->numberOfTestsThatAreNotRun() === 1 ? ' is' : 's are',
                PHP_EOL,
            ),
        );
    }

    /**
     * @return list<non-empty-string>
     */
    private function sourceFiles(): array
    {
        $sourceFiles = [];

        foreach (CodeCoverageFilterRegistry::instance()->get()->files() as $file) {
            $sourceFiles[] = $file;
        }

        return $sourceFiles;
    }

    private function assumptionsOf(Configuration $configuration): Assumptions
    {
        $configurationFile = null;

        if ($configuration->hasConfigurationFile()) {
            $configurationFile = $configuration->configurationFile();
        }

        return Assumptions::from($configurationFile, $configuration->source());
    }

    private function persistTestImpactData(Configuration $configuration, ?TestImpactData $testImpactData): void
    {
        if ($testImpactData !== null) {
            $this->persist($configuration, $testImpactData, Provenance::CoverageTargets);

            return;
        }

        if (!CodeCoverage::instance()->isRecordingTestImpactData()) {
            return;
        }

        $this->persist($configuration, CodeCoverage::instance()->testImpactData(), Provenance::ObservedExecution);
    }

    private function persist(Configuration $configuration, TestImpactData $testImpactData, Provenance $provenance): void
    {
        try {
            new TestImpactDataFile($configuration->cacheDirectory(), $this->assumptionsOf($configuration))->persist(
                $testImpactData,
                $provenance,
                $this->sourceFiles(),
            );
        } catch (RunnerException $e) {
            $message = $e->getMessage();

            if ($message === '') {
                $message = 'Cannot persist test impact data'; // @codeCoverageIgnore
            }

            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning($message);
        }
    }

    /**
     * The index is only usable when there is somewhere to keep it, and it can
     * only save work when tests are selected by group: it answers whether a
     * test file can contribute a test to the run, which is a question only a
     * selection by group can answer without loading the file.
     */
    private function initializeTestIndex(Configuration $configuration, CliConfiguration $cliConfiguration): TestFileSkipper
    {
        if (!$configuration->cacheTestIndex()) {
            return new NullTestFileSkipper;
        }

        /*
         * --list-suites reports how many tests each test suite has, and does so
         * for every test the suite has: it ignores the options that select
         * tests. Pruning test files by those very options would make it report
         * a different number of tests once the index exists.
         */
        if ($cliConfiguration->listSuites()) {
            return new NullTestFileSkipper;
        }

        if (!$configuration->hasCacheDirectory()) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                'Cannot cache the test index because no cache directory is configured',
            );

            return new NullTestFileSkipper;
        }

        $index = new TestIndex($configuration->cacheDirectory());

        $index->load();

        if ($configuration->hasFilter()) {
            $nameFilterPruner = NameFilterPruner::fromFilter($configuration->filter());
        } else {
            $nameFilterPruner = NameFilterPruner::withoutFilter();
        }

        if ($configuration->hasExcludeGroups()) {
            $excludedGroups = $configuration->excludeGroups();
        } else {
            $excludedGroups = [];
        }

        return new DefaultTestFileSkipper(
            EventFacade::instance(),
            $index,
            new GroupPruner(
                $this->includedGroups($configuration),
                $excludedGroups,
            ),
            $nameFilterPruner,
        );
    }

    /**
     * The groups a test can be in for its test file to be worth loading.
     *
     * TestSuiteFilterProcessor selects by these same groups, but it adds a
     * filter of its own for --group, for --covers, for --uses, and for
     * --requires-php-extension: a test has to be selected by every one of the
     * options that were used. The pruner has them all in one list and asks only
     * whether a test is selected by any of them, so it keeps files that the
     * filters go on to take every test from.
     *
     * A value of --group that names several groups is one entry of that list
     * and keeps requiring all of them, which is what the filter for --group
     * requires as well. The list is therefore no less precise for that option
     * than the filter is, and still less precise than the filters are taken
     * together.
     *
     * That is the direction in which the index has to be wrong: leaving work
     * for the filters costs no more than the time it takes, while pruning a
     * file that has a test the filters would select would change which tests
     * are run.
     *
     * @return list<non-empty-string>
     */
    private function includedGroups(Configuration $configuration): array
    {
        $groups = [];

        if ($configuration->hasGroups()) {
            $groups = $configuration->groups();
        }

        if ($configuration->hasTestsCovering()) {
            foreach ($configuration->testsCovering() as $name) {
                $groups[] = Groups::virtualGroupForCovers($name);
            }
        }

        if ($configuration->hasTestsUsing()) {
            foreach ($configuration->testsUsing() as $name) {
                $groups[] = Groups::virtualGroupForUses($name);
            }
        }

        if ($configuration->hasTestsRequiringPhpExtension()) {
            foreach ($configuration->testsRequiringPhpExtension() as $name) {
                $groups[] = Groups::virtualGroupForRequiredPhpExtension($name);
            }
        }

        return $groups;
    }

    private function initializeTestRunHistory(Configuration $configuration): TestRunHistory
    {
        if ($configuration->recordTestRunHistory()) {
            $testRunHistory = new DefaultTestRunHistory($configuration->testRunHistoryFile());

            new TestRunHistoryHandler(
                $testRunHistory,
                EventFacade::instance(),
                $this->testRunHistoryMayBePruned($configuration),
            );

            return $testRunHistory;
        }

        if ($configuration->executionOrderDefects() === TestSuiteSorter::ORDER_DEFECTS_FIRST) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                'Tests cannot be ordered by defects because recording of the test run history is disabled',
            );
        }

        if ($configuration->executionOrder() === TestSuiteSorter::ORDER_DURATION_ASCENDING ||
            $configuration->executionOrder() === TestSuiteSorter::ORDER_DURATION_DESCENDING) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                'Tests cannot be ordered by duration because recording of the test run history is disabled',
            );
        }

        return new NullTestRunHistory;
    }

    /**
     * Pruning drops all test run history entries that the current test run
     * did not touch, so it is only safe when the current test run executes
     * every test that exists: no test selection or filtering of any kind may
     * be configured.
     */
    private function testRunHistoryMayBePruned(Configuration $configuration): bool
    {
        if ($configuration->hasCliArguments() || $configuration->hasTestFilesFile()) {
            return false;
        }

        if ($configuration->hasFilter() || $configuration->hasExcludeFilter()) {
            return false;
        }

        if ($configuration->hasTestIdFilter() || $configuration->hasTestIdFilterFile()) {
            return false;
        }

        if ($configuration->hasGroups() || $configuration->hasExcludeGroups()) {
            return false;
        }

        if ($configuration->hasTestsCovering() || $configuration->hasTestsUsing() || $configuration->hasTestsRequiringPhpExtension()) {
            return false;
        }

        if ($configuration->includeTestSuites() !== [] || $configuration->excludeTestSuites() !== []) {
            return false;
        }

        // @codeCoverageIgnoreStart
        if ($configuration->hasDefaultTestSuite() && count($configuration->testSuite()) > 1) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        return true;
    }

    private function configureBaseline(Configuration $configuration): ?BaselineGenerator
    {
        if ($configuration->hasGenerateBaseline()) {
            return new BaselineGenerator(
                EventFacade::instance(),
                $configuration->source(),
            );
        }

        if ($configuration->source()->useBaseline()) {
            $baselineFile = $configuration->source()->baseline();
            $baseline     = null;

            try {
                $baseline = (new Reader)->read($baselineFile);
            } catch (CannotLoadBaselineException $e) {
                $message = $e->getMessage();

                // @codeCoverageIgnoreStart
                if ($message === '') {
                    $message = 'Cannot load baseline';
                }
                // @codeCoverageIgnoreEnd

                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning($message);
            }

            if ($baseline !== null) {
                ErrorHandler::instance()->useBaseline($baseline);
            }
        }

        return null;
    }

    private function checkPhpConfiguration(Configuration $configuration): void
    {
        if (!$configuration->warnWhenPhpIsNotConfiguredForDevelopment()) {
            return;
        }

        foreach ((new PhpConfigurationChecker)->check() as $result) {
            if ($result->isOk()) {
                continue;
            }

            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'PHP is not configured for development: %s should be %s, but is %s',
                    $result->name(),
                    $result->valueForConfiguration(),
                    $result->actualValue(),
                ),
            );
        }
    }

    /**
     * @codeCoverageIgnore
     */
    private function registerInterruptHandler(): void
    {
        if (!function_exists('pcntl_async_signals')) {
            return;
        }

        $pid = getmypid();

        pcntl_async_signals(true);

        pcntl_signal(SIGINT, static function () use ($pid): void
        {
            if (getmypid() !== $pid) {
                return;
            }

            if (TestResultFacade::wasInterrupted()) {
                exit(2);
            }

            TestResultFacade::interrupt();
        });
    }

    private function exitWithCrashMessage(Throwable $t): never
    {
        $message = $t->getMessage();

        if (trim($message) === '') {
            $message = '(no message)';
        }

        printf(
            '%s%sAn error occurred inside PHPUnit.%s%sMessage:  %s',
            PHP_EOL,
            PHP_EOL,
            PHP_EOL,
            PHP_EOL,
            $message,
        );

        $first = true;

        if ($t->getPrevious() !== null) {
            $t = $t->getPrevious();
        }

        do {
            printf(
                '%s%s: %s:%d%s%s%s%s',
                PHP_EOL,
                $first ? 'Location' : 'Caused by',
                $t->getFile(),
                $t->getLine(),
                PHP_EOL,
                PHP_EOL,
                $t->getTraceAsString(),
                PHP_EOL,
            );

            $first = false;
        } while (($t = $t->getPrevious()) !== null);

        exit(Result::CRASH);
    }

    private function exitWithErrorMessage(string $message): never
    {
        print Version::getVersionString() . PHP_EOL . PHP_EOL . $message . PHP_EOL;

        exit(Result::EXCEPTION);
    }

    /**
     * @return list<PhptTestCase|TestCase>
     */
    private function filteredTests(Configuration $configuration, TestSuite $suite): array
    {
        (new TestSuiteFilterProcessor)->process($configuration, $suite);

        return $suite->collect();
    }

    private function configureDeprecationTriggers(Configuration $configuration): void
    {
        $deprecationTriggers = [
            'functions' => [],
            'methods'   => [],
        ];

        $ignoreUndefinedTriggers = $configuration->source()->deprecationTriggers()['ignoreUndefinedTriggers'] ?? false;

        foreach ($configuration->source()->deprecationTriggers()['functions'] as $function) {
            if (!function_exists($function)) {
                if (!$ignoreUndefinedTriggers) {
                    EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                        sprintf(
                            'Function %s cannot be configured as a deprecation trigger because it is not declared',
                            $function,
                        ),
                    );
                }

                continue;
            }

            $deprecationTriggers['functions'][] = $function;
        }

        foreach ($configuration->source()->deprecationTriggers()['methods'] as $method) {
            $parts = explode('::', $method, 2);

            if (count($parts) !== 2) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        '%s cannot be configured as a deprecation trigger because it is not in ClassName::methodName format',
                        $method,
                    ),
                );

                continue;
            }

            [$className, $methodName] = $parts;

            if ($methodName === '' || !class_exists($className) || !method_exists($className, $methodName)) {
                if (!$ignoreUndefinedTriggers) {
                    EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                        sprintf(
                            'Method %s::%s cannot be configured as a deprecation trigger because it is not declared',
                            $className,
                            $methodName,
                        ),
                    );
                }

                continue;
            }

            $deprecationTriggers['methods'][] = [
                'className'  => $className,
                'methodName' => $methodName,
            ];
        }

        if ($deprecationTriggers !== ['functions' => [], 'methods' => []]) {
            ErrorHandler::instance()->useDeprecationTriggers($deprecationTriggers);
        }
    }

    private function configureIssueTriggerResolvers(Configuration $configuration): void
    {
        $classNames = $configuration->source()->issueTriggerResolvers();

        foreach (array_reverse($classNames) as $className) {
            if (!class_exists($className)) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Class %s cannot be used as an issue trigger resolver because it does not exist',
                        $className,
                    ),
                );

                continue;
            }

            $resolver = new $className;

            if (!$resolver instanceof Resolver) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Class %s cannot be used as an issue trigger resolver because it does not implement %s',
                        $className,
                        Resolver::class,
                    ),
                );

                continue;
            }

            ErrorHandler::instance()->addIssueTriggerResolver($resolver);
        }
    }

    private function configureDeprecationFilters(Configuration $configuration): void
    {
        foreach ($configuration->source()->deprecationFilters() as $className) {
            if (!class_exists($className)) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Class %s cannot be used as a deprecation filter because it does not exist',
                        $className,
                    ),
                );

                continue;
            }

            $filter = new $className;

            if (!$filter instanceof DeprecationFilter) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Class %s cannot be used as a deprecation filter because it does not implement %s',
                        $className,
                        DeprecationFilter::class,
                    ),
                );

                continue;
            }

            ErrorHandler::instance()->addDeprecationFilter($filter);
        }
    }

    private function preload(): void
    {
        if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
            return;
        }

        $composerInstall = PHPUNIT_COMPOSER_INSTALL;

        // @codeCoverageIgnoreStart
        if (!is_string($composerInstall)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $classMapFile = dirname($composerInstall) . '/composer/autoload_classmap.php';

        // @codeCoverageIgnoreStart
        if (!is_file($classMapFile)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $classMap = require $classMapFile;

        // @codeCoverageIgnoreStart
        if (!is_array($classMap)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        foreach ($classMap as $codeUnitName => $sourceCodeFile) {
            // @codeCoverageIgnoreStart
            if (!is_string($codeUnitName) || !is_string($sourceCodeFile)) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            if (!str_starts_with($codeUnitName, 'PHPUnit\\') &&
                !str_starts_with($codeUnitName, 'SebastianBergmann\\')) {
                continue;
            }

            if (str_contains($sourceCodeFile, '/tests/')) {
                continue;
            }

            require_once $sourceCodeFile;
        }
    }
}
