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
use const PHP_SAPI;
use const PHP_VERSION;
use function array_map;
use function assert;
use function extension_loaded;
use function file_put_contents;
use function htmlspecialchars;
use function is_file;
use function mt_srand;
use function range;
use function sprintf;
use PHPUnit\Event;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Logging\JUnit\JunitXmlLogger;
use PHPUnit\Logging\TeamCity\TeamCityLogger;
use PHPUnit\Logging\TestDox\CliTestDoxPrinter;
use PHPUnit\Logging\TestDox\HtmlResultPrinter;
use PHPUnit\Logging\TestDox\TextResultPrinter;
use PHPUnit\Logging\TestDox\XmlResultPrinter;
use PHPUnit\Logging\VoidPrinter;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\PlainTextTracer;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\Runner\ResultCache\NullResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheHandler;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\Util\Printer;
use PHPUnit\Util\Xml\SchemaDetector;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Cobertura as CoberturaReport;
use SebastianBergmann\CodeCoverage\Report\Crap4j as Crap4jReport;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Xml\Facade as XmlReport;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Environment\Runtime;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Timer\Timer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    private Configuration $configuration;
    private ?ResultPrinter $printer = null;
    private bool $messagePrinted    = false;
    private ?Timer $timer           = null;

    public function __construct()
    {
        $this->configuration = Registry::get();
    }

    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\TextUI\XmlConfiguration\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws Exception
     */
    public function run(TestSuite $suite): TestResult
    {
        if ($this->configuration->hasConfigurationFile()) {
            $GLOBALS['__PHPUNIT_CONFIGURATION_FILE'] = $this->configuration->configurationFile();
        }

        $warnings = $this->configuration->warnings();

        if ($this->configuration->loadPharExtensions() &&
            $this->configuration->hasPharExtensionDirectory() &&
            extension_loaded('phar')) {
            $pharExtensions = (new PharLoader)->loadPharExtensionsInDirectory(
                $this->configuration->pharExtensionDirectory()
            );
        }

        if ($this->configuration->hasBootstrap()) {
            $GLOBALS['__PHPUNIT_BOOTSTRAP'] = $this->configuration->bootstrap();
        }

        $suite->setBackupGlobals($this->configuration->backupGlobals());
        $suite->setBackupStaticProperties($this->configuration->backupStaticProperties());
        $suite->setBeStrictAboutChangesToGlobalState($this->configuration->beStrictAboutChangesToGlobalState());

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

            $originalExecutionOrder = $sorter->getOriginalExecutionOrder();

            unset($sorter);
        }

        if ($this->configuration->hasRepeat()) {
            $_suite = new TestSuite;

            /* @noinspection PhpUnusedLocalVariableInspection */
            foreach (range(1, $this->configuration->repeat()) as $step) {
                $_suite->addTest($suite);
            }

            $suite = $_suite;

            unset($_suite);
        }

        $result = new TestResult;

        $result->convertDeprecationsToExceptions($this->configuration->convertDeprecationsToExceptions());
        $result->convertErrorsToExceptions($this->configuration->convertErrorsToExceptions());
        $result->convertNoticesToExceptions($this->configuration->convertNoticesToExceptions());
        $result->convertWarningsToExceptions($this->configuration->convertWarningsToExceptions());
        $result->stopOnDefect($this->configuration->stopOnDefect());
        $result->stopOnError($this->configuration->stopOnError());
        $result->stopOnFailure($this->configuration->stopOnFailure());
        $result->stopOnIncomplete($this->configuration->stopOnIncomplete());
        $result->stopOnRisky($this->configuration->stopOnRisky());
        $result->stopOnSkipped($this->configuration->stopOnSkipped());
        $result->stopOnWarning($this->configuration->stopOnWarning());
        $result->registerMockObjectsFromTestArgumentsRecursively($this->configuration->registerMockObjectsFromTestArgumentsRecursively());

        $this->printer = $this->createPrinter();

        if (isset($originalExecutionOrder) && $this->printer instanceof CliTestDoxPrinter) {
            $this->printer->setOriginalExecutionOrder($originalExecutionOrder);
            $this->printer->setShowProgressAnimation(!$this->configuration->noInteraction());
        }

        if ($this->configuration->hasPlainTextTrace()) {
            if (is_file($this->configuration->plainTextTrace())) {
                unlink($this->configuration->plainTextTrace());
            }

            Event\Facade::registerTracer(
                new PlainTextTracer(
                    $this->configuration->plainTextTrace(),
                    $this->configuration->verbose()
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
                $this->configuration->logfileTeamcity()
            );
        }

        if ($this->configuration->outputIsTeamCity()) {
            $teamCityOutput = new TeamCityLogger('php://stdout');
        }

        Event\Facade::seal();

        $this->printer->write(
            Version::getVersionString() . "\n"
        );

        $result->addListener($this->printer);

        if ($this->configuration->hasLogfileText()) {
            $result->addListener(
                new DefaultResultPrinter(
                    $this->configuration->logfileText(),
                    true
                )
            );
        }

        if ($this->configuration->hasLogfileTestdoxHtml()) {
            $result->addListener(
                new HtmlResultPrinter(
                    $this->configuration->logfileTestdoxHtml(),
                    $this->configuration->testdoxGroups(),
                    $this->configuration->testdoxExcludeGroups()
                )
            );
        }

        if ($this->configuration->hasLogfileTestdoxText()) {
            $result->addListener(
                new TextResultPrinter(
                    $this->configuration->logfileTestdoxText(),
                    $this->configuration->testdoxGroups(),
                    $this->configuration->testdoxExcludeGroups()
                )
            );
        }

        if ($this->configuration->hasLogfileTestdoxXml()) {
            $result->addListener(
                new XmlResultPrinter(
                    $this->configuration->logfileTestdoxXml()
                )
            );
        }

        if ($this->configuration->hasCoverageReport()) {
            try {
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
                        $warnings[] = 'No filter is configured, code coverage will not be processed';
                    } else {
                        $warnings[] = 'Incorrect filter configuration, code coverage will not be processed';
                    }

                    CodeCoverage::deactivate();
                }
            } catch (CodeCoverageException $e) {
                $warnings[] = $e->getMessage();
            }
        }

        if ($this->configuration->verbose()) {
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
        }

        if ($this->configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED) {
            $this->writeMessage(
                'Random Seed',
                (string) $this->configuration->randomOrderSeed()
            );
        }

        if ($this->configuration->tooFewColumnsRequested()) {
            $warnings[] = 'Less than 16 columns requested, number of columns set to 16';
        }

        if ((new Runtime)->discardsComments()) {
            $warnings[] = 'opcache.save_comments=0 set; annotations will not work';
        }

        foreach ($warnings as $warning) {
            $this->writeMessage('Warning', $warning);
        }

        if ($this->configuration->hasXmlValidationErrors()) {
            if ((new SchemaDetector)->detect($this->configuration->configurationFile())->detected()) {
                $this->writeMessage('Warning', 'Your XML configuration validates against a deprecated schema.');
                $this->writeMessage('Suggestion', 'Migrate your XML configuration using "--migrate-configuration"!');
            } else {
                $this->write(
                    "\n  Warning - The configuration file did not pass validation!\n  The following problems have been detected:\n"
                );

                $this->write($this->configuration->xmlValidationErrors());

                $this->write("\n  Test results may not be as expected.\n\n");
            }
        }

        $this->printer->write("\n");

        if ($this->configuration->enforceTimeLimit() && !(new Invoker)->canInvokeWithTimeout()) {
            $this->writeMessage('Error', 'PHP extension pcntl is required for enforcing time limits');
        }

        $result->beStrictAboutTestsThatDoNotTestAnything($this->configuration->reportUselessTests());
        $result->beStrictAboutOutputDuringTests($this->configuration->disallowTestOutput());
        $result->enforceTimeLimit($this->configuration->enforceTimeLimit());
        $result->setDefaultTimeLimit($this->configuration->defaultTimeLimit());
        $result->setTimeoutForSmallTests($this->configuration->timeoutForSmallTests());
        $result->setTimeoutForMediumTests($this->configuration->timeoutForMediumTests());
        $result->setTimeoutForLargeTests($this->configuration->timeoutForLargeTests());
        $result->requireCoverageMetadata($this->configuration->requireCoverageMetadata());

        $this->processSuiteFilters($suite);
        $suite->setRunTestInSeparateProcess($this->configuration->processIsolation());

        $testSuiteWarningsPrinted = false;

        foreach ($suite->warnings() as $warning) {
            $this->writeMessage('Warning', $warning);

            $testSuiteWarningsPrinted = true;
        }

        if ($testSuiteWarningsPrinted) {
            $this->write(PHP_EOL);
        }

        $suite->run($result);
        $result->flushListeners();
        $this->printer->printResult($result);

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
                    $writer = new HtmlReport(
                        $this->configuration->coverageHtmlLowUpperBound(),
                        $this->configuration->coverageHtmlHighLowerBound(),
                        sprintf(
                            ' and <a href="https://phpunit.de/">PHPUnit %s</a>',
                            Version::id()
                        )
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
                if ($this->configuration->coverageText() === 'php://stdout') {
                    $outputStream = $this->printer;
                } else {
                    $outputStream = new Printer($this->configuration->coverageText());
                }

                $processor = new TextReport(
                    50,
                    90,
                    $this->configuration->coverageTextShowUncoveredFiles(),
                    $this->configuration->coverageTextShowOnlySummary()
                );

                $outputStream->write(
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

        if ($this->printer !== null) {
            $this->printer->write($buffer);
        } else {
            print $buffer;
        }
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

    private function createPrinter(): ResultPrinter
    {
        $className = VoidPrinter::class;

        if ($this->configuration->outputIsDefault()) {
            $className = DefaultResultPrinter::class;
        } elseif ($this->configuration->outputIsTestDox()) {
            $className = CliTestDoxPrinter::class;
        }

        $object = new $className(
            $this->configuration->outputToStandardErrorStream() ? 'php://stderr' : 'php://stdout',
            $this->configuration->verbose(),
            $this->configuration->colors(),
            $this->configuration->columns(),
            $this->configuration->reverseDefectList()
        );

        assert($object instanceof ResultPrinter);

        return $object;
    }

    private function codeCoverageGenerationStart(string $format): void
    {
        $this->printer->write(
            sprintf(
                "\nGenerating code coverage report in %s format ... ",
                $format
            )
        );

        $this->timer()->start();
    }

    private function codeCoverageGenerationSucceeded(): void
    {
        $this->printer->write(
            sprintf(
                "done [%s]\n",
                $this->timer()->stop()->asString()
            )
        );
    }

    private function codeCoverageGenerationFailed(\Exception $e): void
    {
        $this->printer->write(
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
}
