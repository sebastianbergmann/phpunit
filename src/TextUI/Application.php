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
use function class_exists;
use function explode;
use function function_exists;
use function is_file;
use function is_readable;
use function method_exists;
use function printf;
use function realpath;
use function sprintf;
use function str_contains;
use function trim;
use function unlink;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Logging\EventLogger;
use PHPUnit\Logging\JUnit\JunitXmlLogger;
use PHPUnit\Logging\TeamCity\TeamCityLogger;
use PHPUnit\Logging\TestDox\HtmlRenderer as TestDoxHtmlRenderer;
use PHPUnit\Logging\TestDox\PlainTextRenderer as TestDoxTextRenderer;
use PHPUnit\Logging\TestDox\TestResultCollector as TestDoxResultCollector;
use PHPUnit\Runner\Baseline\CannotLoadBaselineException;
use PHPUnit\Runner\Baseline\Generator as BaselineGenerator;
use PHPUnit\Runner\Baseline\Reader;
use PHPUnit\Runner\Baseline\Writer;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\DeprecationCollector\Facade as DeprecationCollector;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\Extension\ExtensionBootstrapper;
use PHPUnit\Runner\Extension\Facade as ExtensionFacade;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\GarbageCollection\GarbageCollectionHandler;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\Runner\ResultCache\NullResultCache;
use PHPUnit\Runner\ResultCache\ResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheHandler;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Runner\Version;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\CliArguments\Exception as ArgumentsException;
use PHPUnit\TextUI\CliArguments\XmlConfigurationFileFinder;
use PHPUnit\TextUI\Command\AtLeastVersionCommand;
use PHPUnit\TextUI\Command\GenerateConfigurationCommand;
use PHPUnit\TextUI\Command\ListGroupsCommand;
use PHPUnit\TextUI\Command\ListTestFilesCommand;
use PHPUnit\TextUI\Command\ListTestsAsTextCommand;
use PHPUnit\TextUI\Command\ListTestsAsXmlCommand;
use PHPUnit\TextUI\Command\ListTestSuitesCommand;
use PHPUnit\TextUI\Command\MigrateConfigurationCommand;
use PHPUnit\TextUI\Command\Result;
use PHPUnit\TextUI\Command\ShowHelpCommand;
use PHPUnit\TextUI\Command\ShowVersionCommand;
use PHPUnit\TextUI\Command\VersionCheckCommand;
use PHPUnit\TextUI\Command\WarmCodeCoverageCacheCommand;
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
use SebastianBergmann\Timer\Timer;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Application
{
    public function run(array $argv): int
    {
        try {
            EventFacade::emitter()->applicationStarted();

            $cliConfiguration           = $this->buildCliConfiguration($argv);
            $pathToXmlConfigurationFile = (new XmlConfigurationFileFinder)->find($cliConfiguration);

            $this->executeCommandsThatOnlyRequireCliConfiguration($cliConfiguration, $pathToXmlConfigurationFile);

            $xmlConfiguration = $this->loadXmlConfiguration($pathToXmlConfigurationFile);

            $configuration = Registry::init(
                $cliConfiguration,
                $xmlConfiguration,
            );

            (new PhpHandler)->handle($configuration->php());

            if ($configuration->hasBootstrap()) {
                $this->loadBootstrapScript($configuration->bootstrap());
            }

            $this->executeCommandsThatDoNotRequireTheTestSuite($configuration, $cliConfiguration);

            $testSuite = $this->buildTestSuite($configuration);

            $this->executeCommandsThatRequireTheTestSuite($configuration, $cliConfiguration, $testSuite);

            if ($testSuite->isEmpty() && !$configuration->hasCliArguments() && $configuration->testSuite()->isEmpty()) {
                $this->execute(new ShowHelpCommand(Result::FAILURE));
            }

            $pharExtensions                          = null;
            $extensionRequiresCodeCoverageCollection = false;
            $extensionReplacesOutput                 = false;
            $extensionReplacesProgressOutput         = false;
            $extensionReplacesResultOutput           = false;

            if (!$configuration->noExtensions()) {
                if ($configuration->hasPharExtensionDirectory()) {
                    $pharExtensions = (new PharLoader)->loadPharExtensionsInDirectory(
                        $configuration->pharExtensionDirectory(),
                    );
                }

                $bootstrappedExtensions                  = $this->bootstrapExtensions($configuration);
                $extensionRequiresCodeCoverageCollection = $bootstrappedExtensions['requiresCodeCoverageCollection'];
                $extensionReplacesOutput                 = $bootstrappedExtensions['replacesOutput'];
                $extensionReplacesProgressOutput         = $bootstrappedExtensions['replacesProgressOutput'];
                $extensionReplacesResultOutput           = $bootstrappedExtensions['replacesResultOutput'];
            }

            CodeCoverage::instance()->init(
                $configuration,
                CodeCoverageFilterRegistry::instance(),
                $extensionRequiresCodeCoverageCollection,
            );

            $printer = OutputFacade::init(
                $configuration,
                $extensionReplacesProgressOutput,
                $extensionReplacesResultOutput,
            );

            if (!$configuration->debug() && !$extensionReplacesOutput) {
                $this->writeRuntimeInformation($printer, $configuration);
                $this->writePharExtensionInformation($printer, $pharExtensions);
                $this->writeRandomSeedInformation($printer, $configuration);

                $printer->print(PHP_EOL);
            }

            if ($configuration->debug()) {
                EventFacade::instance()->registerTracer(
                    new EventLogger(
                        'php://stdout',
                        false,
                    ),
                );
            }

            $this->registerLogfileWriters($configuration);

            $testDoxResultCollector = $this->testDoxResultCollector($configuration);

            TestResultFacade::init();
            DeprecationCollector::init();

            $resultCache = $this->initializeTestResultCache($configuration);

            if ($configuration->controlGarbageCollector()) {
                new GarbageCollectionHandler(
                    EventFacade::instance(),
                    $configuration->numberOfTestsBeforeGarbageCollection(),
                );
            }

            $baselineGenerator = $this->configureBaseline($configuration);

            $this->configureDeprecationTriggers($configuration);

            EventFacade::instance()->seal();

            $timer = new Timer;
            $timer->start();

            $runner = new TestRunner;

            $runner->run(
                $configuration,
                $resultCache,
                $testSuite,
            );

            $duration = $timer->stop();

            $testDoxResult = null;

            if (isset($testDoxResultCollector)) {
                $testDoxResult = $testDoxResultCollector->testMethodsGroupedByClass();
            }

            if ($testDoxResult !== null &&
                $configuration->hasLogfileTestdoxHtml()) {
                OutputFacade::printerFor($configuration->logfileTestdoxHtml())->print(
                    (new TestDoxHtmlRenderer)->render($testDoxResult),
                );
            }

            if ($testDoxResult !== null &&
                $configuration->hasLogfileTestdoxText()) {
                OutputFacade::printerFor($configuration->logfileTestdoxText())->print(
                    (new TestDoxTextRenderer)->render($testDoxResult),
                );
            }

            $result = TestResultFacade::result();

            if (!$extensionReplacesResultOutput && !$configuration->debug()) {
                OutputFacade::printResult($result, $testDoxResult, $duration);
            }

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

            $shellExitCode = (new ShellExitCodeCalculator)->calculate(
                $configuration->failOnDeprecation(),
                $configuration->failOnEmptyTestSuite(),
                $configuration->failOnIncomplete(),
                $configuration->failOnNotice(),
                $configuration->failOnRisky(),
                $configuration->failOnSkipped(),
                $configuration->failOnWarning(),
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

    private function execute(Command\Command $command): never
    {
        print Version::getVersionString() . PHP_EOL . PHP_EOL;

        $result = $command->execute();

        print $result->output();

        exit($result->shellExitCode());
    }

    private function loadBootstrapScript(string $filename): void
    {
        if (!is_readable($filename)) {
            $this->exitWithErrorMessage(
                sprintf(
                    'Cannot open bootstrap script "%s"',
                    $filename,
                ),
            );
        }

        try {
            include_once $filename;
        } catch (Throwable $t) {
            $message = sprintf(
                'Error in bootstrap script: %s:%s%s%s%s',
                $t::class,
                PHP_EOL,
                $t->getMessage(),
                PHP_EOL,
                $t->getTraceAsString(),
            );

            while ($t = $t->getPrevious()) {
                $message .= sprintf(
                    '%s%sPrevious error: %s:%s%s%s%s',
                    PHP_EOL,
                    PHP_EOL,
                    $t::class,
                    PHP_EOL,
                    $t->getMessage(),
                    PHP_EOL,
                    $t->getTraceAsString(),
                );
            }

            $this->exitWithErrorMessage($message);
        }

        EventFacade::emitter()->testRunnerBootstrapFinished($filename);
    }

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

    private function buildTestSuite(Configuration $configuration): TestSuite
    {
        try {
            return (new TestSuiteBuilder)->build($configuration);
        } catch (Exception $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }
    }

    /**
     * @psalm-return array{requiresCodeCoverageCollection: bool, replacesOutput: bool, replacesProgressOutput: bool, replacesResultOutput: bool}
     */
    private function bootstrapExtensions(Configuration $configuration): array
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

        return [
            'requiresCodeCoverageCollection' => $facade->requiresCodeCoverageCollection(),
            'replacesOutput'                 => $facade->replacesOutput(),
            'replacesProgressOutput'         => $facade->replacesProgressOutput(),
            'replacesResultOutput'           => $facade->replacesResultOutput(),
        ];
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

            $this->execute(new MigrateConfigurationCommand(realpath($configurationFile)));
        }

        if ($cliConfiguration->hasAtLeastVersion()) {
            $this->execute(new AtLeastVersionCommand($cliConfiguration->atLeastVersion()));
        }

        if ($cliConfiguration->version()) {
            $this->execute(new ShowVersionCommand);
        }

        if ($cliConfiguration->checkVersion()) {
            $this->execute(new VersionCheckCommand);
        }

        if ($cliConfiguration->help()) {
            $this->execute(new ShowHelpCommand(Result::SUCCESS));
        }
    }

    private function executeCommandsThatDoNotRequireTheTestSuite(Configuration $configuration, CliConfiguration $cliConfiguration): void
    {
        if ($cliConfiguration->listSuites()) {
            $this->execute(new ListTestSuitesCommand($configuration->testSuite()));
        }

        if ($cliConfiguration->warmCoverageCache()) {
            $this->execute(new WarmCodeCoverageCacheCommand($configuration, CodeCoverageFilterRegistry::instance()));
        }
    }

    private function executeCommandsThatRequireTheTestSuite(Configuration $configuration, CliConfiguration $cliConfiguration, TestSuite $testSuite): void
    {
        if ($cliConfiguration->listGroups()) {
            $this->execute(
                new ListGroupsCommand(
                    $this->filteredTests(
                        $configuration,
                        $testSuite,
                    ),
                ),
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
            );
        }
    }

    private function writeRuntimeInformation(Printer $printer, Configuration $configuration): void
    {
        $printer->print(Version::getVersionString() . PHP_EOL . PHP_EOL);

        $runtime = 'PHP ' . PHP_VERSION;

        if (CodeCoverage::instance()->isActive()) {
            $runtime .= ' with ' . CodeCoverage::instance()->driver()->nameAndVersion();
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
     * @psalm-param ?list<string> $pharExtensions
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

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerLogfileWriters(Configuration $configuration): void
    {
        if ($configuration->hasLogEventsText()) {
            if (is_file($configuration->logEventsText())) {
                unlink($configuration->logEventsText());
            }

            EventFacade::instance()->registerTracer(
                new EventLogger(
                    $configuration->logEventsText(),
                    false,
                ),
            );
        }

        if ($configuration->hasLogEventsVerboseText()) {
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
            new JunitXmlLogger(
                OutputFacade::printerFor($configuration->logfileJunit()),
                EventFacade::instance(),
            );
        }

        if ($configuration->hasLogfileTeamcity()) {
            new TeamCityLogger(
                DefaultPrinter::from(
                    $configuration->logfileTeamcity(),
                ),
                EventFacade::instance(),
            );
        }
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function testDoxResultCollector(Configuration $configuration): ?TestDoxResultCollector
    {
        if ($configuration->hasLogfileTestdoxHtml() ||
            $configuration->hasLogfileTestdoxText() ||
            $configuration->outputIsTestDox()) {
            return new TestDoxResultCollector(EventFacade::instance());
        }

        return null;
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function initializeTestResultCache(Configuration $configuration): ResultCache
    {
        if ($configuration->cacheResult()) {
            $cache = new DefaultResultCache($configuration->testResultCacheFile());

            new ResultCacheHandler($cache, EventFacade::instance());

            return $cache;
        }

        return new NullResultCache;
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function configureBaseline(Configuration $configuration): ?BaselineGenerator
    {
        if ($configuration->hasGenerateBaseline()) {
            return new BaselineGenerator(
                EventFacade::instance(),
                $configuration->source(),
            );
        }

        if ($configuration->source()->useBaseline()) {
            /** @psalm-suppress MissingThrowsDocblock */
            $baselineFile = $configuration->source()->baseline();
            $baseline     = null;

            try {
                $baseline = (new Reader)->read($baselineFile);
            } catch (CannotLoadBaselineException $e) {
                EventFacade::emitter()->testRunnerTriggeredWarning($e->getMessage());
            }

            if ($baseline !== null) {
                ErrorHandler::instance()->useBaseline($baseline);
            }
        }

        return null;
    }

    /**
     * @codeCoverageIgnore
     */
    private function exitWithCrashMessage(Throwable $t): never
    {
        $message = $t->getMessage();

        if (empty(trim($message))) {
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

        if ($t->getPrevious()) {
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
        } while ($t = $t->getPrevious());

        exit(Result::CRASH);
    }

    private function exitWithErrorMessage(string $message): never
    {
        print Version::getVersionString() . PHP_EOL . PHP_EOL . $message . PHP_EOL;

        exit(Result::EXCEPTION);
    }

    /**
     * @psalm-return list<TestCase|PhptTestCase>
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

        foreach ($configuration->source()->deprecationTriggers()['functions'] as $function) {
            if (!function_exists($function)) {
                EventFacade::emitter()->testRunnerTriggeredWarning(
                    sprintf(
                        'Function %s cannot be configured as a deprecation trigger because it is not declared',
                        $function,
                    ),
                );

                continue;
            }

            $deprecationTriggers['functions'][] = $function;
        }

        foreach ($configuration->source()->deprecationTriggers()['methods'] as $method) {
            if (!str_contains($method, '::')) {
                EventFacade::emitter()->testRunnerTriggeredWarning(
                    sprintf(
                        '%s cannot be configured as a deprecation trigger because it is not in ClassName::methodName format',
                        $method,
                    ),
                );

                continue;
            }

            [$className, $methodName] = explode('::', $method);

            if (!class_exists($className) || !method_exists($className, $methodName)) {
                EventFacade::emitter()->testRunnerTriggeredWarning(
                    sprintf(
                        'Method %s::%s cannot be configured as a deprecation trigger because it is not declared',
                        $className,
                        $methodName,
                    ),
                );

                continue;
            }

            $deprecationTriggers['methods'][] = [
                'className'  => $className,
                'methodName' => $methodName,
            ];
        }

        ErrorHandler::instance()->useDeprecationTriggers($deprecationTriggers);
    }
}
