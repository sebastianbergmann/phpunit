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

use const DIRECTORY_SEPARATOR;
use function array_diff;
use function assert;
use function count;
use function defined;
use function dirname;
use function implode;
use function is_dir;
use function is_file;
use function is_int;
use function is_readable;
use function realpath;
use function substr;
use function time;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Logging\TeamCityLogger;
use PHPUnit\Logging\TestDox\CliTestDoxPrinter;
use PHPUnit\Logging\VoidLogger;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\FilterMapper;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;
use PHPUnit\TextUI\XmlConfiguration\LoadedFromFileConfiguration;
use PHPUnit\Util\Filesystem;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;
use SebastianBergmann\Environment\Console;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use Throwable;

/**
 * CLI options and XML configuration are static within a single PHPUnit process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Configuration
{
    private static ?Configuration $instance = null;

    private ?TestSuite $testSuite;

    private ?string $configurationFile;

    private ?string $bootstrap;

    private bool $cacheResult;

    private ?string $cacheDirectory;

    private ?string $coverageCacheDirectory;

    private bool $pathCoverage;

    private ?string $coverageClover;

    private ?string $coverageCobertura;

    private ?string $coverageCrap4j;

    private int $coverageCrap4jThreshold;

    private ?string $coverageHtml;

    private int $coverageHtmlLowUpperBound;

    private int $coverageHtmlHighLowerBound;

    private ?string $coveragePhp;

    private ?string $coverageText;

    private bool $coverageTextShowUncoveredFiles;

    private bool $coverageTextShowOnlySummary;

    private ?string $coverageXml;

    private string $testResultCacheFile;

    private CodeCoverageFilter $codeCoverageFilter;

    private bool $ignoreDeprecatedCodeUnitsFromCodeCoverage;

    private bool $disableCodeCoverageIgnore;

    private bool $failOnEmptyTestSuite;

    private bool $failOnIncomplete;

    private bool $failOnRisky;

    private bool $failOnSkipped;

    private bool $failOnWarning;

    private bool $outputToStandardErrorStream;

    private int|string $columns;

    private bool $tooFewColumnsRequested;

    private bool $loadPharExtensions;

    private ?string $pharExtensionDirectory;

    private bool $debug;

    private bool $backupGlobals;

    private bool $backupStaticProperties;

    private bool $beStrictAboutChangesToGlobalState;

    private bool $colors;

    private bool $convertDeprecationsToExceptions;

    private bool $convertErrorsToExceptions;

    private bool $convertNoticesToExceptions;

    private bool $convertWarningsToExceptions;

    private bool $processIsolation;

    private bool $stopOnDefect;

    private bool $stopOnError;

    private bool $stopOnFailure;

    private bool $stopOnWarning;

    private bool $stopOnIncomplete;

    private bool $stopOnRisky;

    private bool $stopOnSkipped;

    private bool $enforceTimeLimit;

    private int $defaultTimeLimit;

    private int $timeoutForSmallTests;

    private int $timeoutForMediumTests;

    private int $timeoutForLargeTests;

    private bool $reportUselessTests;

    private bool $strictCoverage;

    private bool $disallowTestOutput;

    private bool $verbose;

    private bool $reverseDefectList;

    private bool $forceCoversAnnotation;

    private bool $registerMockObjectsFromTestArgumentsRecursively;

    private bool $noInteraction;

    private int $executionOrder;

    private int $executionOrderDefects;

    private bool $resolveDependencies;

    private ?string $logfileText;

    private ?string $logfileTeamcity;

    private ?string $logfileJunit;

    private ?string $logfileTestdoxHtml;

    private ?string $logfileTestdoxText;

    private ?string $logfileTestdoxXml;

    private ?string $plainTextTrace;

    private ?array $testsCovering;

    private ?array $testsUsing;

    /**
     * @psalm-var class-string
     */
    private string $printerClassName;

    private int $repeat;

    private ?string $filter;

    private ?array $groups;

    private ?array $excludeGroups;

    private array $testdoxGroups;

    private array $testdoxExcludeGroups;

    private ?string $includePath;

    private int $randomOrderSeed;

    private ?string $xmlValidationErrors;

    /**
     * @psalm-var list<string>
     */
    private array $warnings;

    public static function get(): self
    {
        assert(self::$instance instanceof self);

        return self::$instance;
    }

    /**
     * @throws TestFileNotFoundException
     */
    public static function init(CliConfiguration $cliConfiguration, XmlConfiguration $xmlConfiguration): self
    {
        $warnings = [];

        $bootstrap = null;

        $configurationFile = null;

        if ($xmlConfiguration->wasLoadedFromFile()) {
            assert($xmlConfiguration instanceof LoadedFromFileConfiguration);

            $configurationFile = $xmlConfiguration->filename();
        }

        if ($cliConfiguration->hasBootstrap()) {
            $bootstrap = $cliConfiguration->bootstrap();
        } elseif ($xmlConfiguration->phpunit()->hasBootstrap()) {
            $bootstrap = $xmlConfiguration->phpunit()->bootstrap();
        }

        if ($bootstrap !== null) {
            self::handleBootstrap($bootstrap);
        }

        if ($cliConfiguration->hasArgument()) {
            $argument = realpath($cliConfiguration->argument());

            if (!$argument) {
                throw new TestFileNotFoundException($cliConfiguration->argument());
            }

            $testSuite = self::testSuiteFromPath(
                $argument,
                self::testSuffixes($cliConfiguration)
            );
        } else {
            $includeTestSuite = '';

            if ($cliConfiguration->hasTestSuite()) {
                $includeTestSuite = $cliConfiguration->testSuite();
            } elseif ($xmlConfiguration->phpunit()->hasDefaultTestSuite()) {
                $includeTestSuite = $xmlConfiguration->phpunit()->defaultTestSuite();
            }

            $testSuite = (new TestSuiteMapper)->map(
                $xmlConfiguration->testSuite(),
                $includeTestSuite,
                $cliConfiguration->hasExcludedTestSuite() ? $cliConfiguration->excludedTestSuite() : ''
            );
        }

        if ($cliConfiguration->hasCacheResult()) {
            $cacheResult = $cliConfiguration->cacheResult();
        } else {
            $cacheResult = $xmlConfiguration->phpunit()->cacheResult();
        }

        $cacheDirectory         = null;
        $coverageCacheDirectory = null;

        if ($cliConfiguration->hasCacheDirectory() && Filesystem::createDirectory($cliConfiguration->cacheDirectory())) {
            $cacheDirectory = realpath($cliConfiguration->cacheDirectory());
        } elseif ($xmlConfiguration->phpunit()->hasCacheDirectory() && Filesystem::createDirectory($xmlConfiguration->phpunit()->cacheDirectory())) {
            $cacheDirectory = realpath($xmlConfiguration->phpunit()->cacheDirectory());
        }

        if ($cacheDirectory !== null) {
            $coverageCacheDirectory = $cacheDirectory . DIRECTORY_SEPARATOR . 'code-coverage';
            $testResultCacheFile    = $cacheDirectory . DIRECTORY_SEPARATOR . 'test-results';
        }

        if ($coverageCacheDirectory === null) {
            if ($cliConfiguration->hasCoverageCacheDirectory() && Filesystem::createDirectory($cliConfiguration->coverageCacheDirectory())) {
                $coverageCacheDirectory = realpath($cliConfiguration->coverageCacheDirectory());
            } elseif ($xmlConfiguration->codeCoverage()->hasCacheDirectory()) {
                $coverageCacheDirectory = $xmlConfiguration->codeCoverage()->cacheDirectory()->path();
            }
        }

        if (!isset($testResultCacheFile)) {
            if ($cliConfiguration->hasCacheResultFile()) {
                $testResultCacheFile = $cliConfiguration->cacheResultFile();
            } elseif ($xmlConfiguration->phpunit()->hasCacheResultFile()) {
                $testResultCacheFile = $xmlConfiguration->phpunit()->cacheResultFile();
            } elseif ($xmlConfiguration->wasLoadedFromFile()) {
                $testResultCacheFile = dirname(realpath($xmlConfiguration->filename())) . DIRECTORY_SEPARATOR . '.phpunit.result.cache';
            } else {
                $candidate = realpath($_SERVER['PHP_SELF']);

                if ($candidate) {
                    $testResultCacheFile = dirname($candidate) . DIRECTORY_SEPARATOR . '.phpunit.result.cache';
                } else {
                    $testResultCacheFile = '.phpunit.result.cache';
                }
            }
        }

        $codeCoverageFilter = new CodeCoverageFilter;

        if ($cliConfiguration->hasCoverageFilter()) {
            foreach ($cliConfiguration->coverageFilter() as $directory) {
                $codeCoverageFilter->includeDirectory($directory);
            }
        }

        if ($xmlConfiguration->codeCoverage()->hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()) {
            (new FilterMapper)->map(
                $codeCoverageFilter,
                $xmlConfiguration->codeCoverage()
            );
        }

        if ($cliConfiguration->hasDisableCodeCoverageIgnore()) {
            $disableCodeCoverageIgnore = $cliConfiguration->disableCodeCoverageIgnore();
        } else {
            $disableCodeCoverageIgnore = $xmlConfiguration->codeCoverage()->disableCodeCoverageIgnore();
        }

        if ($cliConfiguration->hasFailOnEmptyTestSuite()) {
            $failOnEmptyTestSuite = $cliConfiguration->failOnEmptyTestSuite();
        } else {
            $failOnEmptyTestSuite = $xmlConfiguration->phpunit()->failOnEmptyTestSuite();
        }

        if ($cliConfiguration->hasFailOnIncomplete()) {
            $failOnIncomplete = $cliConfiguration->failOnIncomplete();
        } else {
            $failOnIncomplete = $xmlConfiguration->phpunit()->failOnIncomplete();
        }

        if ($cliConfiguration->hasFailOnRisky()) {
            $failOnRisky = $cliConfiguration->failOnRisky();
        } else {
            $failOnRisky = $xmlConfiguration->phpunit()->failOnRisky();
        }

        if ($cliConfiguration->hasFailOnSkipped()) {
            $failOnSkipped = $cliConfiguration->failOnSkipped();
        } else {
            $failOnSkipped = $xmlConfiguration->phpunit()->failOnSkipped();
        }

        if ($cliConfiguration->hasFailOnWarning()) {
            $failOnWarning = $cliConfiguration->failOnWarning();
        } else {
            $failOnWarning = $xmlConfiguration->phpunit()->failOnWarning();
        }

        if ($cliConfiguration->hasStderr() && $cliConfiguration->stderr()) {
            $outputToStandardErrorStream = true;
        } else {
            $outputToStandardErrorStream = $xmlConfiguration->phpunit()->stderr();
        }

        $tooFewColumnsRequested = false;

        if ($cliConfiguration->hasColumns()) {
            $columns = $cliConfiguration->columns();
        } else {
            $columns = $xmlConfiguration->phpunit()->columns();
        }

        if (is_int($columns) && $columns < 16) {
            $columns                = 16;
            $tooFewColumnsRequested = true;
        }

        $loadPharExtensions = true;

        if ($cliConfiguration->hasNoExtensions() && $cliConfiguration->noExtensions()) {
            $loadPharExtensions = false;
        }

        $pharExtensionDirectory = null;

        if ($xmlConfiguration->phpunit()->hasExtensionsDirectory()) {
            $pharExtensionDirectory = $xmlConfiguration->phpunit()->extensionsDirectory();
        }

        if ($cliConfiguration->hasPathCoverage() && $cliConfiguration->pathCoverage()) {
            $pathCoverage = $cliConfiguration->pathCoverage();
        } else {
            $pathCoverage = $xmlConfiguration->codeCoverage()->pathCoverage();
        }

        $debug = false;

        if ($cliConfiguration->hasDebug() && $cliConfiguration->debug()) {
            $debug = true;

            if (!defined('PHPUNIT_TESTSUITE')) {
                $warnings[] = 'The --debug option is deprecated';
            }
        }

        $coverageClover                 = null;
        $coverageCobertura              = null;
        $coverageCrap4j                 = null;
        $coverageCrap4jThreshold        = 30;
        $coverageHtml                   = null;
        $coverageHtmlLowUpperBound      = 50;
        $coverageHtmlHighLowerBound     = 90;
        $coveragePhp                    = null;
        $coverageText                   = null;
        $coverageTextShowUncoveredFiles = false;
        $coverageTextShowOnlySummary    = false;
        $coverageXml                    = null;

        if (!($cliConfiguration->hasNoCoverage() && $cliConfiguration->noCoverage())) {
            if ($cliConfiguration->hasCoverageClover()) {
                $coverageClover = $cliConfiguration->coverageClover();
            } elseif ($xmlConfiguration->codeCoverage()->hasClover()) {
                $coverageClover = $xmlConfiguration->codeCoverage()->clover()->target()->path();
            }

            if ($cliConfiguration->hasCoverageCobertura()) {
                $coverageCobertura = $cliConfiguration->coverageCobertura();
            } elseif ($xmlConfiguration->codeCoverage()->hasCobertura()) {
                $coverageCobertura = $xmlConfiguration->codeCoverage()->cobertura()->target()->path();
            }

            if ($xmlConfiguration->codeCoverage()->hasCrap4j()) {
                $coverageCrap4jThreshold = $xmlConfiguration->codeCoverage()->crap4j()->threshold();
            }

            if ($cliConfiguration->hasCoverageCrap4J()) {
                $coverageCrap4j = $cliConfiguration->coverageCrap4J();
            } elseif ($xmlConfiguration->codeCoverage()->hasCrap4j()) {
                $coverageCrap4j = $xmlConfiguration->codeCoverage()->crap4j()->target()->path();
            }

            if ($xmlConfiguration->codeCoverage()->hasHtml()) {
                $coverageHtmlHighLowerBound = $xmlConfiguration->codeCoverage()->html()->highLowerBound();
                $coverageHtmlLowUpperBound  = $xmlConfiguration->codeCoverage()->html()->lowUpperBound();
            }

            if ($cliConfiguration->hasCoverageHtml()) {
                $coverageHtml = $cliConfiguration->coverageHtml();
            } elseif ($xmlConfiguration->codeCoverage()->hasHtml()) {
                $coverageHtml = $xmlConfiguration->codeCoverage()->html()->target()->path();
            }

            if ($cliConfiguration->hasCoveragePhp()) {
                $coveragePhp = $cliConfiguration->coveragePhp();
            } elseif ($xmlConfiguration->codeCoverage()->hasPhp()) {
                $coveragePhp = $xmlConfiguration->codeCoverage()->php()->target()->path();
            }

            if ($xmlConfiguration->codeCoverage()->hasText()) {
                $coverageTextShowUncoveredFiles = $xmlConfiguration->codeCoverage()->text()->showUncoveredFiles();
                $coverageTextShowOnlySummary    = $xmlConfiguration->codeCoverage()->text()->showOnlySummary();
            }

            if ($cliConfiguration->hasCoverageText()) {
                $coverageText = $cliConfiguration->coverageText();
            } elseif ($xmlConfiguration->codeCoverage()->hasText()) {
                $coverageText = $xmlConfiguration->codeCoverage()->text()->target()->path();
            }

            if ($cliConfiguration->hasCoverageXml()) {
                $coverageXml = $cliConfiguration->coverageXml();
            } elseif ($xmlConfiguration->codeCoverage()->hasXml()) {
                $coverageXml = $xmlConfiguration->codeCoverage()->xml()->target()->path();
            }
        }

        if ($cliConfiguration->hasBackupGlobals()) {
            $backupGlobals = $cliConfiguration->backupGlobals();
        } else {
            $backupGlobals = $xmlConfiguration->phpunit()->backupGlobals();
        }

        if ($cliConfiguration->hasBackupStaticProperties()) {
            $backupStaticProperties = $cliConfiguration->backupStaticProperties();
        } else {
            $backupStaticProperties = $xmlConfiguration->phpunit()->backupStaticProperties();
        }

        if ($cliConfiguration->hasBeStrictAboutChangesToGlobalState()) {
            $beStrictAboutChangesToGlobalState = $cliConfiguration->beStrictAboutChangesToGlobalState();
        } else {
            $beStrictAboutChangesToGlobalState = $xmlConfiguration->phpunit()->beStrictAboutChangesToGlobalState();
        }

        $convertDeprecationsToExceptions = $xmlConfiguration->phpunit()->convertDeprecationsToExceptions();
        $convertErrorsToExceptions       = $xmlConfiguration->phpunit()->convertErrorsToExceptions();
        $convertNoticesToExceptions      = $xmlConfiguration->phpunit()->convertNoticesToExceptions();
        $convertWarningsToExceptions     = $xmlConfiguration->phpunit()->convertWarningsToExceptions();

        if ($cliConfiguration->hasProcessIsolation()) {
            $processIsolation = $cliConfiguration->processIsolation();
        } else {
            $processIsolation = $xmlConfiguration->phpunit()->processIsolation();
        }

        if ($cliConfiguration->hasStopOnDefect()) {
            $stopOnDefect = $cliConfiguration->stopOnDefect();
        } else {
            $stopOnDefect = $xmlConfiguration->phpunit()->stopOnDefect();
        }

        if ($cliConfiguration->hasStopOnError()) {
            $stopOnError = $cliConfiguration->stopOnError();
        } else {
            $stopOnError = $xmlConfiguration->phpunit()->stopOnError();
        }

        if ($cliConfiguration->hasStopOnFailure()) {
            $stopOnFailure = $cliConfiguration->stopOnFailure();
        } else {
            $stopOnFailure = $xmlConfiguration->phpunit()->stopOnFailure();
        }

        if ($cliConfiguration->hasStopOnWarning()) {
            $stopOnWarning = $cliConfiguration->stopOnWarning();
        } else {
            $stopOnWarning = $xmlConfiguration->phpunit()->stopOnWarning();
        }

        if ($cliConfiguration->hasStopOnIncomplete()) {
            $stopOnIncomplete = $cliConfiguration->stopOnIncomplete();
        } else {
            $stopOnIncomplete = $xmlConfiguration->phpunit()->stopOnIncomplete();
        }

        if ($cliConfiguration->hasStopOnRisky()) {
            $stopOnRisky = $cliConfiguration->stopOnRisky();
        } else {
            $stopOnRisky = $xmlConfiguration->phpunit()->stopOnRisky();
        }

        if ($cliConfiguration->hasStopOnSkipped()) {
            $stopOnSkipped = $cliConfiguration->stopOnSkipped();
        } else {
            $stopOnSkipped = $xmlConfiguration->phpunit()->stopOnSkipped();
        }

        if ($cliConfiguration->hasEnforceTimeLimit()) {
            $enforceTimeLimit = $cliConfiguration->enforceTimeLimit();
        } else {
            $enforceTimeLimit = $xmlConfiguration->phpunit()->enforceTimeLimit();
        }

        if ($cliConfiguration->hasDefaultTimeLimit()) {
            $defaultTimeLimit = $cliConfiguration->defaultTimeLimit();
        } else {
            $defaultTimeLimit = $xmlConfiguration->phpunit()->defaultTimeLimit();
        }

        $timeoutForSmallTests  = $xmlConfiguration->phpunit()->timeoutForSmallTests();
        $timeoutForMediumTests = $xmlConfiguration->phpunit()->timeoutForMediumTests();
        $timeoutForLargeTests  = $xmlConfiguration->phpunit()->timeoutForLargeTests();

        if ($cliConfiguration->hasReportUselessTests()) {
            $reportUselessTests = $cliConfiguration->reportUselessTests();
        } else {
            $reportUselessTests = $xmlConfiguration->phpunit()->beStrictAboutTestsThatDoNotTestAnything();
        }

        if ($cliConfiguration->hasStrictCoverage()) {
            $strictCoverage = $cliConfiguration->strictCoverage();
        } else {
            $strictCoverage = $xmlConfiguration->phpunit()->beStrictAboutCoversAnnotation();
        }

        if ($cliConfiguration->hasDisallowTestOutput()) {
            $disallowTestOutput = $cliConfiguration->disallowTestOutput();
        } else {
            $disallowTestOutput = $xmlConfiguration->phpunit()->beStrictAboutOutputDuringTests();
        }

        if ($cliConfiguration->hasVerbose()) {
            $verbose = $cliConfiguration->verbose();
        } else {
            $verbose = $xmlConfiguration->phpunit()->verbose();
        }

        if ($cliConfiguration->hasReverseList()) {
            $reverseDefectList = $cliConfiguration->reverseList();
        } else {
            $reverseDefectList = $xmlConfiguration->phpunit()->reverseDefectList();
        }

        $forceCoversAnnotation                           = $xmlConfiguration->phpunit()->forceCoversAnnotation();
        $registerMockObjectsFromTestArgumentsRecursively = $xmlConfiguration->phpunit()->registerMockObjectsFromTestArgumentsRecursively();

        if ($cliConfiguration->hasNoInteraction()) {
            $noInteraction = $cliConfiguration->noInteraction();
        } else {
            $noInteraction = $xmlConfiguration->phpunit()->noInteraction();
        }

        if ($cliConfiguration->hasExecutionOrder()) {
            $executionOrder = $cliConfiguration->executionOrder();
        } else {
            $executionOrder = $xmlConfiguration->phpunit()->executionOrder();
        }

        $executionOrderDefects = TestSuiteSorter::ORDER_DEFAULT;

        if ($cliConfiguration->hasExecutionOrderDefects()) {
            $executionOrderDefects = $cliConfiguration->executionOrderDefects();
        } elseif ($xmlConfiguration->phpunit()->defectsFirst()) {
            $executionOrderDefects = TestSuiteSorter::ORDER_DEFECTS_FIRST;
        }

        if ($cliConfiguration->hasResolveDependencies()) {
            $resolveDependencies = $cliConfiguration->resolveDependencies();
        } else {
            $resolveDependencies = $xmlConfiguration->phpunit()->resolveDependencies();
        }

        $colors          = false;
        $colorsSupported = (new Console)->hasColorSupport();

        if ($cliConfiguration->hasColors()) {
            if ($cliConfiguration->colors() === DefaultResultPrinter::COLOR_ALWAYS) {
                $colors = true;
            } elseif ($cliConfiguration->colors() === DefaultResultPrinter::COLOR_AUTO && $colorsSupported) {
                $colors = true;
            }
        } elseif ($xmlConfiguration->phpunit()->colors() === DefaultResultPrinter::COLOR_ALWAYS) {
            $colors = true;
        } elseif ($xmlConfiguration->phpunit()->colors() === DefaultResultPrinter::COLOR_AUTO && $colorsSupported) {
            $colors = true;
        }

        $logfileText                 = null;
        $logfileTeamcity             = null;
        $logfileJunit                = null;
        $logfileTestdoxHtml          = null;
        $logfileTestdoxText          = null;
        $logfileTestdoxXml           = null;
        $loggingFromXmlConfiguration = true;

        if ($cliConfiguration->hasNoLogging() && $cliConfiguration->noLogging()) {
            $loggingFromXmlConfiguration = false;
        }

        if ($loggingFromXmlConfiguration && $xmlConfiguration->logging()->hasText()) {
            $logfileText = $xmlConfiguration->logging()->text()->target()->path();
        }

        if ($cliConfiguration->hasTeamcityLogfile()) {
            $logfileTeamcity = $cliConfiguration->teamcityLogfile();
        } elseif ($loggingFromXmlConfiguration && $xmlConfiguration->logging()->hasTeamCity()) {
            $logfileTeamcity = $xmlConfiguration->logging()->teamCity()->target()->path();
        }

        if ($cliConfiguration->hasJunitLogfile()) {
            $logfileJunit = $cliConfiguration->junitLogfile();
        } elseif ($loggingFromXmlConfiguration && $xmlConfiguration->logging()->hasJunit()) {
            $logfileJunit = $xmlConfiguration->logging()->junit()->target()->path();
        }

        if ($cliConfiguration->hasTestdoxHtmlFile()) {
            $logfileTestdoxHtml = $cliConfiguration->testdoxHtmlFile();
        } elseif ($loggingFromXmlConfiguration && $xmlConfiguration->logging()->hasTestDoxHtml()) {
            $logfileTestdoxHtml = $xmlConfiguration->logging()->testDoxHtml()->target()->path();
        }

        if ($cliConfiguration->hasTestdoxTextFile()) {
            $logfileTestdoxText = $cliConfiguration->testdoxTextFile();
        } elseif ($loggingFromXmlConfiguration && $xmlConfiguration->logging()->hasTestDoxText()) {
            $logfileTestdoxText = $xmlConfiguration->logging()->testDoxText()->target()->path();
        }

        if ($cliConfiguration->hasTestdoxXmlFile()) {
            $logfileTestdoxXml = $cliConfiguration->testdoxXmlFile();
        } elseif ($loggingFromXmlConfiguration && $xmlConfiguration->logging()->hasTestDoxXml()) {
            $logfileTestdoxXml = $xmlConfiguration->logging()->testDoxXml()->target()->path();
        }

        $plainTextTrace = null;

        if ($cliConfiguration->hasPlainTextTrace()) {
            $plainTextTrace = $cliConfiguration->plainTextTrace();
        }

        $printerClassName = DefaultResultPrinter::class;

        if ($cliConfiguration->hasTeamCityPrinter() && $cliConfiguration->teamCityPrinter()) {
            $printerClassName = TeamCityLogger::class;
        } elseif ($cliConfiguration->hasTestDoxPrinter() && $cliConfiguration->testdoxPrinter()) {
            $printerClassName = CliTestDoxPrinter::class;
        } elseif ($cliConfiguration->hasNoOutput() && $cliConfiguration->noOutput()) {
            $printerClassName = VoidLogger::class;
        }

        $repeat = 0;

        if ($cliConfiguration->hasRepeat()) {
            $repeat = $cliConfiguration->repeat();
        }

        $testsCovering = null;

        if ($cliConfiguration->hasTestsCovering()) {
            $testsCovering = $cliConfiguration->testsCovering();
        }

        $testsUsing = null;

        if ($cliConfiguration->hasTestsUsing()) {
            $testsUsing = $cliConfiguration->testsUsing();
        }

        $filter = null;

        if ($cliConfiguration->hasFilter()) {
            $filter = $cliConfiguration->filter();
        }

        if ($cliConfiguration->hasGroups()) {
            $groups = $cliConfiguration->groups();
        } else {
            $groups = $xmlConfiguration->groups()->include()->asArrayOfStrings();
        }

        if ($cliConfiguration->hasExcludeGroups()) {
            $excludeGroups = $cliConfiguration->excludeGroups();
        } else {
            $excludeGroups = $xmlConfiguration->groups()->exclude()->asArrayOfStrings();
        }

        $excludeGroups = array_diff($excludeGroups, $groups);

        if ($cliConfiguration->hasTestdoxGroups()) {
            $testdoxGroups = $cliConfiguration->testdoxGroups();
        } else {
            $testdoxGroups = $xmlConfiguration->testdoxGroups()->include()->asArrayOfStrings();
        }

        if ($cliConfiguration->hasTestdoxExcludeGroups()) {
            $testdoxExcludeGroups = $cliConfiguration->testdoxExcludeGroups();
        } else {
            $testdoxExcludeGroups = $xmlConfiguration->testdoxGroups()->exclude()->asArrayOfStrings();
        }

        $includePath = null;

        if ($cliConfiguration->hasIncludePath()) {
            $includePath = $cliConfiguration->includePath();
        } elseif (!$xmlConfiguration->php()->includePaths()->isEmpty()) {
            $includePathsAsStrings = [];

            foreach ($xmlConfiguration->php()->includePaths() as $includePath) {
                $includePathsAsStrings[] = $includePath->path();
            }

            $includePath = implode(PATH_SEPARATOR, $includePathsAsStrings);
        }

        if ($cliConfiguration->hasRandomOrderSeed()) {
            $randomOrderSeed = $cliConfiguration->randomOrderSeed();
        } else {
            $randomOrderSeed = time();
        }

        $xmlValidationErrors = null;

        if ($xmlConfiguration->wasLoadedFromFile() && $xmlConfiguration->hasValidationErrors()) {
            $xmlValidationErrors = $xmlConfiguration->validationErrors();
        }

        self::$instance = new self(
            $testSuite,
            $configurationFile,
            $bootstrap,
            $cacheResult,
            $cacheDirectory,
            $coverageCacheDirectory,
            $testResultCacheFile,
            $codeCoverageFilter,
            $coverageClover,
            $coverageCobertura,
            $coverageCrap4j,
            $coverageCrap4jThreshold,
            $coverageHtml,
            $coverageHtmlLowUpperBound,
            $coverageHtmlHighLowerBound,
            $coveragePhp,
            $coverageText,
            $coverageTextShowUncoveredFiles,
            $coverageTextShowOnlySummary,
            $coverageXml,
            $pathCoverage,
            $xmlConfiguration->codeCoverage()->ignoreDeprecatedCodeUnits(),
            $disableCodeCoverageIgnore,
            $failOnEmptyTestSuite,
            $failOnIncomplete,
            $failOnRisky,
            $failOnSkipped,
            $failOnWarning,
            $outputToStandardErrorStream,
            $columns,
            $tooFewColumnsRequested,
            $loadPharExtensions,
            $pharExtensionDirectory,
            $debug,
            $backupGlobals,
            $backupStaticProperties,
            $beStrictAboutChangesToGlobalState,
            $colors,
            $convertDeprecationsToExceptions,
            $convertErrorsToExceptions,
            $convertNoticesToExceptions,
            $convertWarningsToExceptions,
            $processIsolation,
            $stopOnDefect,
            $stopOnError,
            $stopOnFailure,
            $stopOnWarning,
            $stopOnIncomplete,
            $stopOnRisky,
            $stopOnSkipped,
            $enforceTimeLimit,
            $defaultTimeLimit,
            $timeoutForSmallTests,
            $timeoutForMediumTests,
            $timeoutForLargeTests,
            $reportUselessTests,
            $strictCoverage,
            $disallowTestOutput,
            $verbose,
            $reverseDefectList,
            $forceCoversAnnotation,
            $registerMockObjectsFromTestArgumentsRecursively,
            $noInteraction,
            $executionOrder,
            $executionOrderDefects,
            $resolveDependencies,
            $logfileText,
            $logfileTeamcity,
            $logfileJunit,
            $logfileTestdoxHtml,
            $logfileTestdoxText,
            $logfileTestdoxXml,
            $plainTextTrace,
            $printerClassName,
            $repeat,
            $testsCovering,
            $testsUsing,
            $filter,
            $groups,
            $excludeGroups,
            $testdoxGroups,
            $testdoxExcludeGroups,
            $includePath,
            $randomOrderSeed,
            $xmlValidationErrors,
            $warnings
        );

        return self::$instance;
    }

    /**
     * @psalm-param class-string $printerClassName
     */
    private function __construct(?TestSuite $testSuite, ?string $configurationFile, ?string $bootstrap, bool $cacheResult, ?string $cacheDirectory, ?string $coverageCacheDirectory, string $testResultCacheFile, CodeCoverageFilter $codeCoverageFilter, ?string $coverageClover, ?string $coverageCobertura, ?string $coverageCrap4j, int $coverageCrap4jThreshold, ?string $coverageHtml, int $coverageHtmlLowUpperBound, int $coverageHtmlHighLowerBound, ?string $coveragePhp, ?string $coverageText, bool $coverageTextShowUncoveredFiles, bool $coverageTextShowOnlySummary, ?string $coverageXml, bool $pathCoverage, bool $ignoreDeprecatedCodeUnitsFromCodeCoverage, bool $disableCodeCoverageIgnore, bool $failOnEmptyTestSuite, bool $failOnIncomplete, bool $failOnRisky, bool $failOnSkipped, bool $failOnWarning, bool $outputToStandardErrorStream, int|string $columns, bool $tooFewColumnsRequested, bool $loadPharExtensions, ?string $pharExtensionDirectory, bool $debug, bool $backupGlobals, bool $backupStaticProperties, bool $beStrictAboutChangesToGlobalState, bool $colors, bool $convertDeprecationsToExceptions, bool $convertErrorsToExceptions, bool $convertNoticesToExceptions, bool $convertWarningsToExceptions, bool $processIsolation, bool $stopOnDefect, bool $stopOnError, bool $stopOnFailure, bool $stopOnWarning, bool $stopOnIncomplete, bool $stopOnRisky, bool $stopOnSkipped, bool $enforceTimeLimit, int $defaultTimeLimit, int $timeoutForSmallTests, int $timeoutForMediumTests, int $timeoutForLargeTests, bool $reportUselessTests, bool $strictCoverage, bool $disallowTestOutput, bool $verbose, bool $reverseDefectList, bool $forceCoversAnnotation, bool $registerMockObjectsFromTestArgumentsRecursively, bool $noInteraction, int $executionOrder, int $executionOrderDefects, bool $resolveDependencies, ?string $logfileText, ?string $logfileTeamcity, ?string $logfileJunit, ?string $logfileTestdoxHtml, ?string $logfileTestdoxText, ?string $logfileTestdoxXml, ?string $plainTextTrace, string $printerClassName, int $repeat, ?array $testsCovering, ?array $testsUsing, ?string $filter, ?array $groups, ?array $excludeGroups, array $testdoxGroups, array $testdoxExcludeGroups, ?string $includePath, int $randomOrderSeed, ?string $xmlValidationErrors, array $warnings)
    {
        $this->testSuite                                       = $testSuite;
        $this->configurationFile                               = $configurationFile;
        $this->bootstrap                                       = $bootstrap;
        $this->cacheResult                                     = $cacheResult;
        $this->cacheDirectory                                  = $cacheDirectory;
        $this->coverageCacheDirectory                          = $coverageCacheDirectory;
        $this->testResultCacheFile                             = $testResultCacheFile;
        $this->codeCoverageFilter                              = $codeCoverageFilter;
        $this->coverageClover                                  = $coverageClover;
        $this->coverageCobertura                               = $coverageCobertura;
        $this->coverageCrap4j                                  = $coverageCrap4j;
        $this->coverageCrap4jThreshold                         = $coverageCrap4jThreshold;
        $this->coverageHtml                                    = $coverageHtml;
        $this->coverageHtmlLowUpperBound                       = $coverageHtmlLowUpperBound;
        $this->coverageHtmlHighLowerBound                      = $coverageHtmlHighLowerBound;
        $this->coveragePhp                                     = $coveragePhp;
        $this->coverageText                                    = $coverageText;
        $this->coverageTextShowUncoveredFiles                  = $coverageTextShowUncoveredFiles;
        $this->coverageTextShowOnlySummary                     = $coverageTextShowOnlySummary;
        $this->coverageXml                                     = $coverageXml;
        $this->pathCoverage                                    = $pathCoverage;
        $this->ignoreDeprecatedCodeUnitsFromCodeCoverage       = $ignoreDeprecatedCodeUnitsFromCodeCoverage;
        $this->disableCodeCoverageIgnore                       = $disableCodeCoverageIgnore;
        $this->failOnEmptyTestSuite                            = $failOnEmptyTestSuite;
        $this->failOnIncomplete                                = $failOnIncomplete;
        $this->failOnRisky                                     = $failOnRisky;
        $this->failOnSkipped                                   = $failOnSkipped;
        $this->failOnWarning                                   = $failOnWarning;
        $this->outputToStandardErrorStream                     = $outputToStandardErrorStream;
        $this->columns                                         = $columns;
        $this->tooFewColumnsRequested                          = $tooFewColumnsRequested;
        $this->loadPharExtensions                              = $loadPharExtensions;
        $this->pharExtensionDirectory                          = $pharExtensionDirectory;
        $this->debug                                           = $debug;
        $this->backupGlobals                                   = $backupGlobals;
        $this->backupStaticProperties                          = $backupStaticProperties;
        $this->beStrictAboutChangesToGlobalState               = $beStrictAboutChangesToGlobalState;
        $this->colors                                          = $colors;
        $this->convertDeprecationsToExceptions                 = $convertDeprecationsToExceptions;
        $this->convertErrorsToExceptions                       = $convertErrorsToExceptions;
        $this->convertNoticesToExceptions                      = $convertNoticesToExceptions;
        $this->convertWarningsToExceptions                     = $convertWarningsToExceptions;
        $this->processIsolation                                = $processIsolation;
        $this->stopOnDefect                                    = $stopOnDefect;
        $this->stopOnError                                     = $stopOnError;
        $this->stopOnFailure                                   = $stopOnFailure;
        $this->stopOnWarning                                   = $stopOnWarning;
        $this->stopOnIncomplete                                = $stopOnIncomplete;
        $this->stopOnRisky                                     = $stopOnRisky;
        $this->stopOnSkipped                                   = $stopOnSkipped;
        $this->enforceTimeLimit                                = $enforceTimeLimit;
        $this->defaultTimeLimit                                = $defaultTimeLimit;
        $this->timeoutForSmallTests                            = $timeoutForSmallTests;
        $this->timeoutForMediumTests                           = $timeoutForMediumTests;
        $this->timeoutForLargeTests                            = $timeoutForLargeTests;
        $this->reportUselessTests                              = $reportUselessTests;
        $this->strictCoverage                                  = $strictCoverage;
        $this->disallowTestOutput                              = $disallowTestOutput;
        $this->verbose                                         = $verbose;
        $this->reverseDefectList                               = $reverseDefectList;
        $this->forceCoversAnnotation                           = $forceCoversAnnotation;
        $this->registerMockObjectsFromTestArgumentsRecursively = $registerMockObjectsFromTestArgumentsRecursively;
        $this->noInteraction                                   = $noInteraction;
        $this->executionOrder                                  = $executionOrder;
        $this->executionOrderDefects                           = $executionOrderDefects;
        $this->resolveDependencies                             = $resolveDependencies;
        $this->logfileText                                     = $logfileText;
        $this->logfileTeamcity                                 = $logfileTeamcity;
        $this->logfileJunit                                    = $logfileJunit;
        $this->logfileTestdoxHtml                              = $logfileTestdoxHtml;
        $this->logfileTestdoxText                              = $logfileTestdoxText;
        $this->logfileTestdoxXml                               = $logfileTestdoxXml;
        $this->plainTextTrace                                  = $plainTextTrace;
        $this->printerClassName                                = $printerClassName;
        $this->repeat                                          = $repeat;
        $this->testsCovering                                   = $testsCovering;
        $this->testsUsing                                      = $testsUsing;
        $this->filter                                          = $filter;
        $this->groups                                          = $groups;
        $this->excludeGroups                                   = $excludeGroups;
        $this->testdoxGroups                                   = $testdoxGroups;
        $this->testdoxExcludeGroups                            = $testdoxExcludeGroups;
        $this->includePath                                     = $includePath;
        $this->randomOrderSeed                                 = $randomOrderSeed;
        $this->xmlValidationErrors                             = $xmlValidationErrors;
        $this->warnings                                        = $warnings;
    }

    /**
     * @psalm-assert-if-true !null $this->testSuite
     */
    public function hasTestSuite(): bool
    {
        return $this->testSuite !== null && !$this->testSuite()->isEmpty();
    }

    /**
     * @throws NoTestSuiteException
     */
    public function testSuite(): TestSuite
    {
        if ($this->testSuite === null) {
            throw new NoTestSuiteException;
        }

        return $this->testSuite;
    }

    /**
     * @psalm-assert-if-true !null $this->configurationFile
     */
    public function hasConfigurationFile(): bool
    {
        return $this->configurationFile !== null;
    }

    /**
     * @throws NoConfigurationFileException
     */
    public function configurationFile(): string
    {
        if (!$this->hasConfigurationFile()) {
            throw new NoConfigurationFileException;
        }

        return $this->configurationFile;
    }

    /**
     * @psalm-assert-if-true !null $this->bootstrap
     */
    public function hasBootstrap(): bool
    {
        return $this->bootstrap !== null;
    }

    /**
     * @throws NoBootstrapException
     */
    public function bootstrap(): string
    {
        if (!$this->hasBootstrap()) {
            throw new NoBootstrapException;
        }

        return $this->bootstrap;
    }

    public function cacheResult(): bool
    {
        return $this->cacheResult;
    }

    /**
     * @psalm-assert-if-true !null $this->cacheDirectory
     */
    public function hasCacheDirectory(): bool
    {
        return $this->cacheDirectory !== null;
    }

    /**
     * @throws NoCacheDirectoryException
     */
    public function cacheDirectory(): string
    {
        if (!$this->hasCacheDirectory()) {
            throw new NoCacheDirectoryException;
        }

        return $this->cacheDirectory;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageCacheDirectory
     */
    public function hasCoverageCacheDirectory(): bool
    {
        return $this->coverageCacheDirectory !== null;
    }

    /**
     * @throws NoCoverageCacheDirectoryException
     */
    public function coverageCacheDirectory(): string
    {
        if (!$this->hasCoverageCacheDirectory()) {
            throw new NoCoverageCacheDirectoryException;
        }

        return $this->coverageCacheDirectory;
    }

    public function testResultCacheFile(): string
    {
        return $this->testResultCacheFile;
    }

    public function codeCoverageFilter(): CodeCoverageFilter
    {
        return $this->codeCoverageFilter;
    }

    public function ignoreDeprecatedCodeUnitsFromCodeCoverage(): bool
    {
        return $this->ignoreDeprecatedCodeUnitsFromCodeCoverage;
    }

    public function disableCodeCoverageIgnore(): bool
    {
        return $this->disableCodeCoverageIgnore;
    }

    public function pathCoverage(): bool
    {
        return $this->pathCoverage;
    }

    public function hasCoverageReport(): bool
    {
        return $this->hasCoverageClover() ||
               $this->hasCoverageCobertura() ||
               $this->hasCoverageCrap4j() ||
               $this->hasCoverageHtml() ||
               $this->hasCoverageText() ||
               $this->hasCoverageXml();
    }

    /**
     * @psalm-assert-if-true !null $this->coverageClover
     */
    public function hasCoverageClover(): bool
    {
        return $this->coverageClover !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverageClover(): string
    {
        if (!$this->hasCoverageClover()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageClover;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageCobertura
     */
    public function hasCoverageCobertura(): bool
    {
        return $this->coverageCobertura !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverageCobertura(): string
    {
        if (!$this->hasCoverageCobertura()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageCobertura;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageCrap4j
     */
    public function hasCoverageCrap4j(): bool
    {
        return $this->coverageCrap4j !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverageCrap4j(): string
    {
        if (!$this->hasCoverageCrap4j()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageCrap4j;
    }

    public function coverageCrap4jThreshold(): int
    {
        return $this->coverageCrap4jThreshold;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageHtml
     */
    public function hasCoverageHtml(): bool
    {
        return $this->coverageHtml !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverageHtml(): string
    {
        if (!$this->hasCoverageHtml()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageHtml;
    }

    public function coverageHtmlLowUpperBound(): int
    {
        return $this->coverageHtmlLowUpperBound;
    }

    public function coverageHtmlHighLowerBound(): int
    {
        return $this->coverageHtmlHighLowerBound;
    }

    /**
     * @psalm-assert-if-true !null $this->coveragePhp
     */
    public function hasCoveragePhp(): bool
    {
        return $this->coveragePhp !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coveragePhp(): string
    {
        if (!$this->hasCoveragePhp()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coveragePhp;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageText
     */
    public function hasCoverageText(): bool
    {
        return $this->coverageText !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverageText(): string
    {
        if (!$this->hasCoverageText()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageText;
    }

    public function coverageTextShowUncoveredFiles(): bool
    {
        return $this->coverageTextShowUncoveredFiles;
    }

    public function coverageTextShowOnlySummary(): bool
    {
        return $this->coverageTextShowOnlySummary;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageXml
     */
    public function hasCoverageXml(): bool
    {
        return $this->coverageXml !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     */
    public function coverageXml(): string
    {
        if (!$this->hasCoverageXml()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageXml;
    }

    public function failOnEmptyTestSuite(): bool
    {
        return $this->failOnEmptyTestSuite;
    }

    public function failOnIncomplete(): bool
    {
        return $this->failOnIncomplete;
    }

    public function failOnRisky(): bool
    {
        return $this->failOnRisky;
    }

    public function failOnSkipped(): bool
    {
        return $this->failOnSkipped;
    }

    public function failOnWarning(): bool
    {
        return $this->failOnWarning;
    }

    public function outputToStandardErrorStream(): bool
    {
        return $this->outputToStandardErrorStream;
    }

    public function columns(): int|string
    {
        return $this->columns;
    }

    public function tooFewColumnsRequested(): bool
    {
        return $this->tooFewColumnsRequested;
    }

    public function loadPharExtensions(): bool
    {
        return $this->loadPharExtensions;
    }

    /**
     * @psalm-assert-if-true !null $this->pharExtensionDirectory
     */
    public function hasPharExtensionDirectory(): bool
    {
        return $this->pharExtensionDirectory !== null;
    }

    /**
     * @throws NoPharExtensionDirectoryException
     */
    public function pharExtensionDirectory(): string
    {
        if (!$this->hasPharExtensionDirectory()) {
            throw new NoPharExtensionDirectoryException;
        }

        return $this->pharExtensionDirectory;
    }

    public function debug(): bool
    {
        return $this->debug;
    }

    public function backupGlobals(): bool
    {
        return $this->backupGlobals;
    }

    public function backupStaticProperties(): bool
    {
        return $this->backupStaticProperties;
    }

    public function beStrictAboutChangesToGlobalState(): bool
    {
        return $this->beStrictAboutChangesToGlobalState;
    }

    public function colors(): bool
    {
        return $this->colors;
    }

    public function convertDeprecationsToExceptions(): bool
    {
        return $this->convertDeprecationsToExceptions;
    }

    public function convertErrorsToExceptions(): bool
    {
        return $this->convertErrorsToExceptions;
    }

    public function convertNoticesToExceptions(): bool
    {
        return $this->convertNoticesToExceptions;
    }

    public function convertWarningsToExceptions(): bool
    {
        return $this->convertWarningsToExceptions;
    }

    public function processIsolation(): bool
    {
        return $this->processIsolation;
    }

    public function stopOnDefect(): bool
    {
        return $this->stopOnDefect;
    }

    public function stopOnError(): bool
    {
        return $this->stopOnError;
    }

    public function stopOnFailure(): bool
    {
        return $this->stopOnFailure;
    }

    public function stopOnWarning(): bool
    {
        return $this->stopOnWarning;
    }

    public function stopOnIncomplete(): bool
    {
        return $this->stopOnIncomplete;
    }

    public function stopOnRisky(): bool
    {
        return $this->stopOnRisky;
    }

    public function stopOnSkipped(): bool
    {
        return $this->stopOnSkipped;
    }

    public function enforceTimeLimit(): bool
    {
        return $this->enforceTimeLimit;
    }

    public function defaultTimeLimit(): int
    {
        return $this->defaultTimeLimit;
    }

    public function timeoutForSmallTests(): int
    {
        return $this->timeoutForSmallTests;
    }

    public function timeoutForMediumTests(): int
    {
        return $this->timeoutForMediumTests;
    }

    public function timeoutForLargeTests(): int
    {
        return $this->timeoutForLargeTests;
    }

    public function reportUselessTests(): bool
    {
        return $this->reportUselessTests;
    }

    public function strictCoverage(): bool
    {
        return $this->strictCoverage;
    }

    public function disallowTestOutput(): bool
    {
        return $this->disallowTestOutput;
    }

    public function verbose(): bool
    {
        return $this->verbose;
    }

    public function reverseDefectList(): bool
    {
        return $this->reverseDefectList;
    }

    public function forceCoversAnnotation(): bool
    {
        return $this->forceCoversAnnotation;
    }

    public function registerMockObjectsFromTestArgumentsRecursively(): bool
    {
        return $this->registerMockObjectsFromTestArgumentsRecursively;
    }

    public function noInteraction(): bool
    {
        return $this->noInteraction;
    }

    public function executionOrder(): int
    {
        return $this->executionOrder;
    }

    public function executionOrderDefects(): int
    {
        return $this->executionOrderDefects;
    }

    public function resolveDependencies(): bool
    {
        return $this->resolveDependencies;
    }

    /**
     * @psalm-assert-if-true !null $this->logfileText
     */
    public function hasLogfileText(): bool
    {
        return $this->logfileText !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfileText(): string
    {
        if (!$this->hasLogfileText()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileText;
    }

    /**
     * @psalm-assert-if-true !null $this->logfileTeamcity
     */
    public function hasLogfileTeamcity(): bool
    {
        return $this->logfileTeamcity !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfileTeamcity(): string
    {
        if (!$this->hasLogfileTeamcity()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileTeamcity;
    }

    /**
     * @psalm-assert-if-true !null $this->logfileJunit
     */
    public function hasLogfileJunit(): bool
    {
        return $this->logfileJunit !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfileJunit(): string
    {
        if (!$this->hasLogfileJunit()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileJunit;
    }

    /**
     * @psalm-assert-if-true !null $this->logfileTestdoxHtml
     */
    public function hasLogfileTestdoxHtml(): bool
    {
        return $this->logfileTestdoxHtml !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfileTestdoxHtml(): string
    {
        if (!$this->hasLogfileTestdoxHtml()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileTestdoxHtml;
    }

    /**
     * @psalm-assert-if-true !null $this->logfileTestdoxText
     */
    public function hasLogfileTestdoxText(): bool
    {
        return $this->logfileTestdoxText !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfileTestdoxText(): string
    {
        if (!$this->hasLogfileTestdoxText()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileTestdoxText;
    }

    /**
     * @psalm-assert-if-true !null $this->logfileTestdoxXml
     */
    public function hasLogfileTestdoxXml(): bool
    {
        return $this->logfileTestdoxXml !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     */
    public function logfileTestdoxXml(): string
    {
        if (!$this->hasLogfileTestdoxXml()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileTestdoxXml;
    }

    /**
     * @psalm-assert-if-true !null $this->plainTextTrace
     */
    public function hasPlainTextTrace(): bool
    {
        return $this->plainTextTrace !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     */
    public function plainTextTrace(): string
    {
        if (!$this->hasPlainTextTrace()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->plainTextTrace;
    }

    /**
     * @psalm-return class-string
     */
    public function printerClassName(): string
    {
        return $this->printerClassName;
    }

    public function hasRepeat(): bool
    {
        return $this->repeat > 0;
    }

    public function repeat(): int
    {
        return $this->repeat;
    }

    /**
     * @psalm-assert-if-true !null $this->testsCovering
     */
    public function hasTestsCovering(): bool
    {
        return $this->testsCovering !== null && !empty($this->testsCovering);
    }

    /**
     * @psalm-return list<string>
     *
     * @throws FilterNotConfiguredException
     */
    public function testsCovering(): array
    {
        if (!$this->hasTestsCovering()) {
            throw new FilterNotConfiguredException();
        }

        return $this->testsCovering;
    }

    /**
     * @psalm-assert-if-true !null $this->testsUsing
     */
    public function hasTestsUsing(): bool
    {
        return $this->testsUsing !== null && !empty($this->testsUsing);
    }

    /**
     * @psalm-return list<string>
     *
     * @throws FilterNotConfiguredException
     */
    public function testsUsing(): array
    {
        if (!$this->hasTestsUsing()) {
            throw new FilterNotConfiguredException();
        }

        return $this->testsUsing;
    }

    /**
     * @psalm-assert-if-true !null $this->filter
     */
    public function hasFilter(): bool
    {
        return $this->filter !== null;
    }

    /**
     * @throws FilterNotConfiguredException
     */
    public function filter(): string
    {
        if (!$this->hasFilter()) {
            throw new FilterNotConfiguredException;
        }

        return $this->filter;
    }

    /**
     * @psalm-assert-if-true !null $this->groups
     */
    public function hasGroups(): bool
    {
        return $this->groups !== null && !empty($this->groups);
    }

    /**
     * @throws FilterNotConfiguredException
     */
    public function groups(): array
    {
        if (!$this->hasGroups()) {
            throw new FilterNotConfiguredException;
        }

        return $this->groups;
    }

    /**
     * @psalm-assert-if-true !null $this->excludeGroups
     */
    public function hasExcludeGroups(): bool
    {
        return $this->excludeGroups !== null && !empty($this->excludeGroups);
    }

    /**
     * @throws FilterNotConfiguredException
     */
    public function excludeGroups(): array
    {
        if (!$this->hasExcludeGroups()) {
            throw new FilterNotConfiguredException;
        }

        return $this->excludeGroups;
    }

    public function testdoxGroups(): array
    {
        return $this->testdoxGroups;
    }

    public function testdoxExcludeGroups(): array
    {
        return $this->testdoxExcludeGroups;
    }

    /**
     * @psalm-assert-if-true !null $this->includePath
     */
    public function hasIncludePath(): bool
    {
        return $this->includePath !== null;
    }

    /**
     * @throws IncludePathNotConfiguredException
     */
    public function includePath(): string
    {
        if (!$this->hasIncludePath()) {
            throw new FilterNotConfiguredException;
        }

        return $this->includePath;
    }

    public function randomOrderSeed(): int
    {
        return $this->randomOrderSeed;
    }

    /**
     * @psalm-assert-if-true !null $this->xmlValidationErrors
     */
    public function hasXmlValidationErrors(): bool
    {
        return $this->xmlValidationErrors !== null;
    }

    /**
     * @throws NoValidationErrorsException
     */
    public function xmlValidationErrors(): string
    {
        if (!$this->hasXmlValidationErrors()) {
            throw new NoValidationErrorsException;
        }

        return $this->xmlValidationErrors;
    }

    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    /**
     * @psalm-return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @psalm-param list<string> $suffixes
     */
    private static function testSuiteFromPath(string $path, array $suffixes): TestSuite
    {
        if (is_dir($path)) {
            $files = (new FileIteratorFacade)->getFilesAsArray($path, $suffixes);

            $suite = new TestSuite($path);
            $suite->addTestFiles($files);

            return $suite;
        }

        if (is_file($path) && substr($path, -5, 5) === '.phpt') {
            $suite = new TestSuite;
            $suite->addTestFile($path);

            return $suite;
        }

        try {
            $testClass = (new TestSuiteLoader)->load($path);
        } catch (\PHPUnit\Exception $e) {
            print $e->getMessage() . PHP_EOL;

            exit(1);
        }

        return new TestSuite($testClass);
    }

    private static function testSuffixes(CliConfiguration $cliConfiguration): array
    {
        $testSuffixes = ['Test.php', '.phpt'];

        if ($cliConfiguration->hasTestSuffixes()) {
            $testSuffixes = $cliConfiguration->testSuffixes();
        }

        return $testSuffixes;
    }

    /**
     * @throws InvalidBootstrapException
     */
    private static function handleBootstrap(string $filename): void
    {
        if (!is_readable($filename)) {
            throw new InvalidBootstrapException($filename);
        }

        try {
            include $filename;
        } catch (Throwable $t) {
            throw new BootstrapException($t);
        }

        Facade::emitter()->bootstrapFinished($filename);
    }
}
