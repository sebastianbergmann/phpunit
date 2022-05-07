<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\CliArguments;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Configuration
{
    private ?string $argument;
    private ?string $atLeastVersion;
    private ?bool $backupGlobals;
    private ?bool $backupStaticProperties;
    private ?bool $beStrictAboutChangesToGlobalState;
    private ?string $bootstrap;
    private ?string $cacheDirectory;
    private ?bool $cacheResult;
    private ?string $cacheResultFile;
    private ?bool $checkVersion;
    private ?string $colors;
    private null|int|string $columns;
    private ?string $configuration;
    private ?array $coverageFilter;
    private ?string $coverageClover;
    private ?string $coverageCobertura;
    private ?string $coverageCrap4J;
    private ?string $coverageHtml;
    private ?string $coveragePhp;
    private ?string $coverageText;
    private ?bool $coverageTextShowUncoveredFiles;
    private ?bool $coverageTextShowOnlySummary;
    private ?string $coverageXml;
    private ?bool $pathCoverage;
    private ?string $coverageCacheDirectory;
    private ?bool $warmCoverageCache;
    private ?int $defaultTimeLimit;
    private ?bool $disableCodeCoverageIgnore;
    private ?bool $disallowTestOutput;
    private ?bool $enforceTimeLimit;
    private ?array $excludeGroups;
    private ?int $executionOrder;
    private ?int $executionOrderDefects;
    private ?bool $failOnEmptyTestSuite;
    private ?bool $failOnIncomplete;
    private ?bool $failOnRisky;
    private ?bool $failOnSkipped;
    private ?bool $failOnWarning;
    private ?string $filter;
    private ?bool $generateConfiguration;
    private ?bool $migrateConfiguration;
    private ?array $groups;
    private ?array $testsCovering;
    private ?array $testsUsing;
    private ?bool $help;
    private ?string $includePath;
    private ?array $iniSettings;
    private ?string $junitLogfile;
    private ?bool $listGroups;
    private ?bool $listSuites;
    private ?bool $listTests;
    private ?string $listTestsXml;
    private ?bool $noCoverage;
    private ?bool $noExtensions;
    private ?bool $noInteraction;
    private ?bool $noOutput;
    private ?bool $noLogging;
    private ?bool $processIsolation;
    private ?int $randomOrderSeed;
    private ?int $repeat;
    private ?bool $reportUselessTests;
    private ?bool $resolveDependencies;
    private ?bool $reverseList;
    private ?bool $stderr;
    private ?bool $strictCoverage;
    private ?bool $stopOnDefect;
    private ?bool $stopOnError;
    private ?bool $stopOnFailure;
    private ?bool $stopOnIncomplete;
    private ?bool $stopOnRisky;
    private ?bool $stopOnSkipped;
    private ?bool $stopOnWarning;
    private ?string $teamcityLogfile;
    private ?bool $teamCityPrinter;
    private ?array $testdoxExcludeGroups;
    private ?array $testdoxGroups;
    private ?string $testdoxHtmlFile;
    private ?string $testdoxTextFile;
    private ?string $testdoxXmlFile;
    private ?bool $testdoxPrinter;
    private ?array $testSuffixes;
    private ?string $testSuite;
    private ?string $excludeTestSuite = null;
    private ?string $unrecognizedOrderBy;
    private ?bool $useDefaultConfiguration;
    private ?bool $displayDetailsOnIncompleteTests;
    private ?bool $displayDetailsOnSkippedTests;
    private ?bool $version;
    private ?string $logEventsText;
    private ?string $logEventsVerboseText;

    public function __construct(?string $argument, ?string $atLeastVersion, ?bool $backupGlobals, ?bool $backupStaticProperties, ?bool $beStrictAboutChangesToGlobalState, ?string $bootstrap, ?string $cacheDirectory, ?bool $cacheResult, ?string $cacheResultFile, ?bool $checkVersion, ?string $colors, null|int|string $columns, ?string $configuration, ?string $coverageClover, ?string $coverageCobertura, ?string $coverageCrap4J, ?string $coverageHtml, ?string $coveragePhp, ?string $coverageText, ?bool $coverageTextShowUncoveredFiles, ?bool $coverageTextShowOnlySummary, ?string $coverageXml, ?bool $pathCoverage, ?string $coverageCacheDirectory, ?bool $warmCoverageCache, ?int $defaultTimeLimit, ?bool $disableCodeCoverageIgnore, ?bool $disallowTestOutput, ?bool $enforceTimeLimit, ?array $excludeGroups, ?int $executionOrder, ?int $executionOrderDefects, ?bool $failOnEmptyTestSuite, ?bool $failOnIncomplete, ?bool $failOnRisky, ?bool $failOnSkipped, ?bool $failOnWarning, ?string $filter, ?bool $generateConfiguration, ?bool $migrateConfiguration, ?array $groups, ?array $testsCovering, ?array $testsUsing, ?bool $help, ?string $includePath, ?array $iniSettings, ?string $junitLogfile, ?bool $listGroups, ?bool $listSuites, ?bool $listTests, ?string $listTestsXml, ?bool $noCoverage, ?bool $noExtensions, ?bool $noInteraction, ?bool $noOutput, ?bool $noLogging, ?bool $processIsolation, ?int $randomOrderSeed, ?int $repeat, ?bool $reportUselessTests, ?bool $resolveDependencies, ?bool $reverseList, ?bool $stderr, ?bool $strictCoverage, ?bool $stopOnDefect, ?bool $stopOnError, ?bool $stopOnFailure, ?bool $stopOnIncomplete, ?bool $stopOnRisky, ?bool $stopOnSkipped, ?bool $stopOnWarning, ?string $teamcityLogfile, ?array $testdoxExcludeGroups, ?array $testdoxGroups, ?string $testdoxHtmlFile, ?string $testdoxTextFile, ?string $testdoxXmlFile, ?array $testSuffixes, ?string $testSuite, ?string $excludeTestSuite, ?string $unrecognizedOrderBy, ?bool $useDefaultConfiguration, ?bool $displayDetailsOnIncompleteTests, ?bool $displayDetailsOnSkippedTests, ?bool $version, ?array $coverageFilter, ?string $logEventsText, ?string $logEventsVerboseText, ?bool $printerTeamCity, ?bool $printerTestDox)
    {
        $this->argument                          = $argument;
        $this->atLeastVersion                    = $atLeastVersion;
        $this->backupGlobals                     = $backupGlobals;
        $this->backupStaticProperties            = $backupStaticProperties;
        $this->beStrictAboutChangesToGlobalState = $beStrictAboutChangesToGlobalState;
        $this->bootstrap                         = $bootstrap;
        $this->cacheDirectory                    = $cacheDirectory;
        $this->cacheResult                       = $cacheResult;
        $this->cacheResultFile                   = $cacheResultFile;
        $this->checkVersion                      = $checkVersion;
        $this->colors                            = $colors;
        $this->columns                           = $columns;
        $this->configuration                     = $configuration;
        $this->coverageFilter                    = $coverageFilter;
        $this->coverageClover                    = $coverageClover;
        $this->coverageCobertura                 = $coverageCobertura;
        $this->coverageCrap4J                    = $coverageCrap4J;
        $this->coverageHtml                      = $coverageHtml;
        $this->coveragePhp                       = $coveragePhp;
        $this->coverageText                      = $coverageText;
        $this->coverageTextShowUncoveredFiles    = $coverageTextShowUncoveredFiles;
        $this->coverageTextShowOnlySummary       = $coverageTextShowOnlySummary;
        $this->coverageXml                       = $coverageXml;
        $this->pathCoverage                      = $pathCoverage;
        $this->coverageCacheDirectory            = $coverageCacheDirectory;
        $this->warmCoverageCache                 = $warmCoverageCache;
        $this->defaultTimeLimit                  = $defaultTimeLimit;
        $this->disableCodeCoverageIgnore         = $disableCodeCoverageIgnore;
        $this->disallowTestOutput                = $disallowTestOutput;
        $this->enforceTimeLimit                  = $enforceTimeLimit;
        $this->excludeGroups                     = $excludeGroups;
        $this->executionOrder                    = $executionOrder;
        $this->executionOrderDefects             = $executionOrderDefects;
        $this->failOnEmptyTestSuite              = $failOnEmptyTestSuite;
        $this->failOnIncomplete                  = $failOnIncomplete;
        $this->failOnRisky                       = $failOnRisky;
        $this->failOnSkipped                     = $failOnSkipped;
        $this->failOnWarning                     = $failOnWarning;
        $this->filter                            = $filter;
        $this->generateConfiguration             = $generateConfiguration;
        $this->migrateConfiguration              = $migrateConfiguration;
        $this->groups                            = $groups;
        $this->testsCovering                     = $testsCovering;
        $this->testsUsing                        = $testsUsing;
        $this->help                              = $help;
        $this->includePath                       = $includePath;
        $this->iniSettings                       = $iniSettings;
        $this->junitLogfile                      = $junitLogfile;
        $this->listGroups                        = $listGroups;
        $this->listSuites                        = $listSuites;
        $this->listTests                         = $listTests;
        $this->listTestsXml                      = $listTestsXml;
        $this->noCoverage                        = $noCoverage;
        $this->noExtensions                      = $noExtensions;
        $this->noInteraction                     = $noInteraction;
        $this->noOutput                          = $noOutput;
        $this->noLogging                         = $noLogging;
        $this->processIsolation                  = $processIsolation;
        $this->randomOrderSeed                   = $randomOrderSeed;
        $this->repeat                            = $repeat;
        $this->reportUselessTests                = $reportUselessTests;
        $this->resolveDependencies               = $resolveDependencies;
        $this->reverseList                       = $reverseList;
        $this->stderr                            = $stderr;
        $this->strictCoverage                    = $strictCoverage;
        $this->stopOnDefect                      = $stopOnDefect;
        $this->stopOnError                       = $stopOnError;
        $this->stopOnFailure                     = $stopOnFailure;
        $this->stopOnIncomplete                  = $stopOnIncomplete;
        $this->stopOnRisky                       = $stopOnRisky;
        $this->stopOnSkipped                     = $stopOnSkipped;
        $this->stopOnWarning                     = $stopOnWarning;
        $this->teamcityLogfile                   = $teamcityLogfile;
        $this->testdoxExcludeGroups              = $testdoxExcludeGroups;
        $this->testdoxGroups                     = $testdoxGroups;
        $this->testdoxHtmlFile                   = $testdoxHtmlFile;
        $this->testdoxTextFile                   = $testdoxTextFile;
        $this->testdoxXmlFile                    = $testdoxXmlFile;
        $this->testSuffixes                      = $testSuffixes;
        $this->testSuite                         = $testSuite;
        $this->excludeTestSuite                  = $excludeTestSuite;
        $this->unrecognizedOrderBy               = $unrecognizedOrderBy;
        $this->useDefaultConfiguration           = $useDefaultConfiguration;
        $this->displayDetailsOnIncompleteTests   = $displayDetailsOnIncompleteTests;
        $this->displayDetailsOnSkippedTests      = $displayDetailsOnSkippedTests;
        $this->version                           = $version;
        $this->logEventsText                     = $logEventsText;
        $this->logEventsVerboseText              = $logEventsVerboseText;
        $this->teamCityPrinter                   = $printerTeamCity;
        $this->testdoxPrinter                    = $printerTestDox;
    }

    /**
     * @psalm-assert-if-true !null $this->argument
     */
    public function hasArgument(): bool
    {
        return $this->argument !== null;
    }

    /**
     * @throws Exception
     */
    public function argument(): string
    {
        if (!$this->hasArgument()) {
            throw new Exception;
        }

        return $this->argument;
    }

    /**
     * @psalm-assert-if-true !null $this->atLeastVersion
     */
    public function hasAtLeastVersion(): bool
    {
        return $this->atLeastVersion !== null;
    }

    /**
     * @throws Exception
     */
    public function atLeastVersion(): string
    {
        if (!$this->hasAtLeastVersion()) {
            throw new Exception;
        }

        return $this->atLeastVersion;
    }

    /**
     * @psalm-assert-if-true !null $this->backupGlobals
     */
    public function hasBackupGlobals(): bool
    {
        return $this->backupGlobals !== null;
    }

    /**
     * @throws Exception
     */
    public function backupGlobals(): bool
    {
        if (!$this->hasBackupGlobals()) {
            throw new Exception;
        }

        return $this->backupGlobals;
    }

    /**
     * @psalm-assert-if-true !null $this->backupStaticProperties
     */
    public function hasBackupStaticProperties(): bool
    {
        return $this->backupStaticProperties !== null;
    }

    /**
     * @throws Exception
     */
    public function backupStaticProperties(): bool
    {
        if (!$this->hasBackupStaticProperties()) {
            throw new Exception;
        }

        return $this->backupStaticProperties;
    }

    /**
     * @psalm-assert-if-true !null $this->beStrictAboutChangesToGlobalState
     */
    public function hasBeStrictAboutChangesToGlobalState(): bool
    {
        return $this->beStrictAboutChangesToGlobalState !== null;
    }

    /**
     * @throws Exception
     */
    public function beStrictAboutChangesToGlobalState(): bool
    {
        if (!$this->hasBeStrictAboutChangesToGlobalState()) {
            throw new Exception;
        }

        return $this->beStrictAboutChangesToGlobalState;
    }

    /**
     * @psalm-assert-if-true !null $this->bootstrap
     */
    public function hasBootstrap(): bool
    {
        return $this->bootstrap !== null;
    }

    /**
     * @throws Exception
     */
    public function bootstrap(): string
    {
        if (!$this->hasBootstrap()) {
            throw new Exception;
        }

        return $this->bootstrap;
    }

    /**
     * @psalm-assert-if-true !null $this->cacheDirectory
     */
    public function hasCacheDirectory(): bool
    {
        return $this->cacheDirectory !== null;
    }

    /**
     * @throws Exception
     */
    public function cacheDirectory(): string
    {
        if (!$this->hasCacheDirectory()) {
            throw new Exception;
        }

        return $this->cacheDirectory;
    }

    /**
     * @psalm-assert-if-true !null $this->cacheResult
     */
    public function hasCacheResult(): bool
    {
        return $this->cacheResult !== null;
    }

    /**
     * @throws Exception
     */
    public function cacheResult(): bool
    {
        if (!$this->hasCacheResult()) {
            throw new Exception;
        }

        return $this->cacheResult;
    }

    /**
     * @psalm-assert-if-true !null $this->cacheResultFile
     */
    public function hasCacheResultFile(): bool
    {
        return $this->cacheResultFile !== null;
    }

    /**
     * @throws Exception
     */
    public function cacheResultFile(): string
    {
        if (!$this->hasCacheResultFile()) {
            throw new Exception;
        }

        return $this->cacheResultFile;
    }

    /**
     * @psalm-assert-if-true !null $this->checkVersion
     */
    public function hasCheckVersion(): bool
    {
        return $this->checkVersion !== null;
    }

    /**
     * @throws Exception
     */
    public function checkVersion(): bool
    {
        if (!$this->hasCheckVersion()) {
            throw new Exception;
        }

        return $this->checkVersion;
    }

    /**
     * @psalm-assert-if-true !null $this->colors
     */
    public function hasColors(): bool
    {
        return $this->colors !== null;
    }

    /**
     * @throws Exception
     */
    public function colors(): string
    {
        if (!$this->hasColors()) {
            throw new Exception;
        }

        return $this->colors;
    }

    /**
     * @psalm-assert-if-true !null $this->columns
     */
    public function hasColumns(): bool
    {
        return $this->columns !== null;
    }

    /**
     * @throws Exception
     */
    public function columns(): int|string
    {
        if (!$this->hasColumns()) {
            throw new Exception;
        }

        return $this->columns;
    }

    /**
     * @psalm-assert-if-true !null $this->configuration
     */
    public function hasConfiguration(): bool
    {
        return $this->configuration !== null;
    }

    /**
     * @throws Exception
     */
    public function configuration(): string
    {
        if (!$this->hasConfiguration()) {
            throw new Exception;
        }

        return $this->configuration;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageFilter
     */
    public function hasCoverageFilter(): bool
    {
        return $this->coverageFilter !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageFilter(): array
    {
        if (!$this->hasCoverageFilter()) {
            throw new Exception;
        }

        return $this->coverageFilter;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageClover
     */
    public function hasCoverageClover(): bool
    {
        return $this->coverageClover !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageClover(): string
    {
        if (!$this->hasCoverageClover()) {
            throw new Exception;
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
     * @throws Exception
     */
    public function coverageCobertura(): string
    {
        if (!$this->hasCoverageCobertura()) {
            throw new Exception;
        }

        return $this->coverageCobertura;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageCrap4J
     */
    public function hasCoverageCrap4J(): bool
    {
        return $this->coverageCrap4J !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageCrap4J(): string
    {
        if (!$this->hasCoverageCrap4J()) {
            throw new Exception;
        }

        return $this->coverageCrap4J;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageHtml
     */
    public function hasCoverageHtml(): bool
    {
        return $this->coverageHtml !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageHtml(): string
    {
        if (!$this->hasCoverageHtml()) {
            throw new Exception;
        }

        return $this->coverageHtml;
    }

    /**
     * @psalm-assert-if-true !null $this->coveragePhp
     */
    public function hasCoveragePhp(): bool
    {
        return $this->coveragePhp !== null;
    }

    /**
     * @throws Exception
     */
    public function coveragePhp(): string
    {
        if (!$this->hasCoveragePhp()) {
            throw new Exception;
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
     * @throws Exception
     */
    public function coverageText(): string
    {
        if (!$this->hasCoverageText()) {
            throw new Exception;
        }

        return $this->coverageText;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageTextShowUncoveredFiles
     */
    public function hasCoverageTextShowUncoveredFiles(): bool
    {
        return $this->coverageTextShowUncoveredFiles !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageTextShowUncoveredFiles(): bool
    {
        if (!$this->hasCoverageTextShowUncoveredFiles()) {
            throw new Exception;
        }

        return $this->coverageTextShowUncoveredFiles;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageTextShowOnlySummary
     */
    public function hasCoverageTextShowOnlySummary(): bool
    {
        return $this->coverageTextShowOnlySummary !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageTextShowOnlySummary(): bool
    {
        if (!$this->hasCoverageTextShowOnlySummary()) {
            throw new Exception;
        }

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
     * @throws Exception
     */
    public function coverageXml(): string
    {
        if (!$this->hasCoverageXml()) {
            throw new Exception;
        }

        return $this->coverageXml;
    }

    /**
     * @psalm-assert-if-true !null $this->pathCoverage
     */
    public function hasPathCoverage(): bool
    {
        return $this->pathCoverage !== null;
    }

    /**
     * @throws Exception
     */
    public function pathCoverage(): bool
    {
        if (!$this->hasPathCoverage()) {
            throw new Exception;
        }

        return $this->pathCoverage;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageCacheDirectory
     */
    public function hasCoverageCacheDirectory(): bool
    {
        return $this->coverageCacheDirectory !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageCacheDirectory(): string
    {
        if (!$this->hasCoverageCacheDirectory()) {
            throw new Exception;
        }

        return $this->coverageCacheDirectory;
    }

    /**
     * @psalm-assert-if-true !null $this->warmCoverageCache
     */
    public function hasWarmCoverageCache(): bool
    {
        return $this->warmCoverageCache !== null;
    }

    /**
     * @throws Exception
     */
    public function warmCoverageCache(): bool
    {
        if (!$this->hasWarmCoverageCache()) {
            throw new Exception;
        }

        return $this->warmCoverageCache;
    }

    /**
     * @psalm-assert-if-true !null $this->defaultTimeLimit
     */
    public function hasDefaultTimeLimit(): bool
    {
        return $this->defaultTimeLimit !== null;
    }

    /**
     * @throws Exception
     */
    public function defaultTimeLimit(): int
    {
        if (!$this->hasDefaultTimeLimit()) {
            throw new Exception;
        }

        return $this->defaultTimeLimit;
    }

    /**
     * @psalm-assert-if-true !null $this->disableCodeCoverageIgnore
     */
    public function hasDisableCodeCoverageIgnore(): bool
    {
        return $this->disableCodeCoverageIgnore !== null;
    }

    /**
     * @throws Exception
     */
    public function disableCodeCoverageIgnore(): bool
    {
        if (!$this->hasDisableCodeCoverageIgnore()) {
            throw new Exception;
        }

        return $this->disableCodeCoverageIgnore;
    }

    /**
     * @psalm-assert-if-true !null $this->disallowTestOutput
     */
    public function hasDisallowTestOutput(): bool
    {
        return $this->disallowTestOutput !== null;
    }

    /**
     * @throws Exception
     */
    public function disallowTestOutput(): bool
    {
        if (!$this->hasDisallowTestOutput()) {
            throw new Exception;
        }

        return $this->disallowTestOutput;
    }

    /**
     * @psalm-assert-if-true !null $this->enforceTimeLimit
     */
    public function hasEnforceTimeLimit(): bool
    {
        return $this->enforceTimeLimit !== null;
    }

    /**
     * @throws Exception
     */
    public function enforceTimeLimit(): bool
    {
        if (!$this->hasEnforceTimeLimit()) {
            throw new Exception;
        }

        return $this->enforceTimeLimit;
    }

    /**
     * @psalm-assert-if-true !null $this->excludeGroups
     */
    public function hasExcludeGroups(): bool
    {
        return $this->excludeGroups !== null;
    }

    /**
     * @throws Exception
     */
    public function excludeGroups(): array
    {
        if (!$this->hasExcludeGroups()) {
            throw new Exception;
        }

        return $this->excludeGroups;
    }

    /**
     * @psalm-assert-if-true !null $this->executionOrder
     */
    public function hasExecutionOrder(): bool
    {
        return $this->executionOrder !== null;
    }

    /**
     * @throws Exception
     */
    public function executionOrder(): int
    {
        if (!$this->hasExecutionOrder()) {
            throw new Exception;
        }

        return $this->executionOrder;
    }

    /**
     * @psalm-assert-if-true !null $this->executionOrderDefects
     */
    public function hasExecutionOrderDefects(): bool
    {
        return $this->executionOrderDefects !== null;
    }

    /**
     * @throws Exception
     */
    public function executionOrderDefects(): int
    {
        if (!$this->hasExecutionOrderDefects()) {
            throw new Exception;
        }

        return $this->executionOrderDefects;
    }

    /**
     * @psalm-assert-if-true !null $this->failOnEmptyTestSuite
     */
    public function hasFailOnEmptyTestSuite(): bool
    {
        return $this->failOnEmptyTestSuite !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnEmptyTestSuite(): bool
    {
        if (!$this->hasFailOnEmptyTestSuite()) {
            throw new Exception;
        }

        return $this->failOnEmptyTestSuite;
    }

    /**
     * @psalm-assert-if-true !null $this->failOnIncomplete
     */
    public function hasFailOnIncomplete(): bool
    {
        return $this->failOnIncomplete !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnIncomplete(): bool
    {
        if (!$this->hasFailOnIncomplete()) {
            throw new Exception;
        }

        return $this->failOnIncomplete;
    }

    /**
     * @psalm-assert-if-true !null $this->failOnRisky
     */
    public function hasFailOnRisky(): bool
    {
        return $this->failOnRisky !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnRisky(): bool
    {
        if (!$this->hasFailOnRisky()) {
            throw new Exception;
        }

        return $this->failOnRisky;
    }

    /**
     * @psalm-assert-if-true !null $this->failOnSkipped
     */
    public function hasFailOnSkipped(): bool
    {
        return $this->failOnSkipped !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnSkipped(): bool
    {
        if (!$this->hasFailOnSkipped()) {
            throw new Exception;
        }

        return $this->failOnSkipped;
    }

    /**
     * @psalm-assert-if-true !null $this->failOnWarning
     */
    public function hasFailOnWarning(): bool
    {
        return $this->failOnWarning !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnWarning(): bool
    {
        if (!$this->hasFailOnWarning()) {
            throw new Exception;
        }

        return $this->failOnWarning;
    }

    /**
     * @psalm-assert-if-true !null $this->filter
     */
    public function hasFilter(): bool
    {
        return $this->filter !== null;
    }

    /**
     * @throws Exception
     */
    public function filter(): string
    {
        if (!$this->hasFilter()) {
            throw new Exception;
        }

        return $this->filter;
    }

    /**
     * @psalm-assert-if-true !null $this->generateConfiguration
     */
    public function hasGenerateConfiguration(): bool
    {
        return $this->generateConfiguration !== null;
    }

    /**
     * @throws Exception
     */
    public function generateConfiguration(): bool
    {
        if (!$this->hasGenerateConfiguration()) {
            throw new Exception;
        }

        return $this->generateConfiguration;
    }

    /**
     * @psalm-assert-if-true !null $this->migrateConfiguration
     */
    public function hasMigrateConfiguration(): bool
    {
        return $this->migrateConfiguration !== null;
    }

    /**
     * @throws Exception
     */
    public function migrateConfiguration(): bool
    {
        if (!$this->hasMigrateConfiguration()) {
            throw new Exception;
        }

        return $this->migrateConfiguration;
    }

    /**
     * @psalm-assert-if-true !null $this->groups
     */
    public function hasGroups(): bool
    {
        return $this->groups !== null;
    }

    /**
     * @throws Exception
     */
    public function groups(): array
    {
        if (!$this->hasGroups()) {
            throw new Exception;
        }

        return $this->groups;
    }

    /**
     * @psalm-assert-if-true !null $this->testsCovering
     */
    public function hasTestsCovering(): bool
    {
        return $this->testsCovering !== null;
    }

    /**
     * @throws Exception
     */
    public function testsCovering(): array
    {
        if (!$this->hasTestsCovering()) {
            throw new Exception;
        }

        return $this->testsCovering;
    }

    /**
     * @psalm-assert-if-true !null $this->testsUsing
     */
    public function hasTestsUsing(): bool
    {
        return $this->testsUsing !== null;
    }

    /**
     * @throws Exception
     */
    public function testsUsing(): array
    {
        if (!$this->hasTestsUsing()) {
            throw new Exception;
        }

        return $this->testsUsing;
    }

    /**
     * @psalm-assert-if-true !null $this->help
     */
    public function hasHelp(): bool
    {
        return $this->help !== null;
    }

    /**
     * @throws Exception
     */
    public function help(): bool
    {
        if (!$this->hasHelp()) {
            throw new Exception;
        }

        return $this->help;
    }

    /**
     * @psalm-assert-if-true !null $this->includePath
     */
    public function hasIncludePath(): bool
    {
        return $this->includePath !== null;
    }

    /**
     * @throws Exception
     */
    public function includePath(): string
    {
        if (!$this->hasIncludePath()) {
            throw new Exception;
        }

        return $this->includePath;
    }

    /**
     * @psalm-assert-if-true !null $this->iniSettings
     */
    public function hasIniSettings(): bool
    {
        return $this->iniSettings !== null;
    }

    /**
     * @throws Exception
     */
    public function iniSettings(): array
    {
        if (!$this->hasIniSettings()) {
            throw new Exception;
        }

        return $this->iniSettings;
    }

    /**
     * @psalm-assert-if-true !null $this->junitLogfile
     */
    public function hasJunitLogfile(): bool
    {
        return $this->junitLogfile !== null;
    }

    /**
     * @throws Exception
     */
    public function junitLogfile(): string
    {
        if (!$this->hasJunitLogfile()) {
            throw new Exception;
        }

        return $this->junitLogfile;
    }

    /**
     * @psalm-assert-if-true !null $this->listGroups
     */
    public function hasListGroups(): bool
    {
        return $this->listGroups !== null;
    }

    /**
     * @throws Exception
     */
    public function listGroups(): bool
    {
        if (!$this->hasListGroups()) {
            throw new Exception;
        }

        return $this->listGroups;
    }

    /**
     * @psalm-assert-if-true !null $this->listSuites
     */
    public function hasListSuites(): bool
    {
        return $this->listSuites !== null;
    }

    /**
     * @throws Exception
     */
    public function listSuites(): bool
    {
        if (!$this->hasListSuites()) {
            throw new Exception;
        }

        return $this->listSuites;
    }

    /**
     * @psalm-assert-if-true !null $this->listTests
     */
    public function hasListTests(): bool
    {
        return $this->listTests !== null;
    }

    /**
     * @throws Exception
     */
    public function listTests(): bool
    {
        if (!$this->hasListTests()) {
            throw new Exception;
        }

        return $this->listTests;
    }

    /**
     * @psalm-assert-if-true !null $this->listTestsXml
     */
    public function hasListTestsXml(): bool
    {
        return $this->listTestsXml !== null;
    }

    /**
     * @throws Exception
     */
    public function listTestsXml(): string
    {
        if (!$this->hasListTestsXml()) {
            throw new Exception;
        }

        return $this->listTestsXml;
    }

    /**
     * @psalm-assert-if-true !null $this->noCoverage
     */
    public function hasNoCoverage(): bool
    {
        return $this->noCoverage !== null;
    }

    /**
     * @throws Exception
     */
    public function noCoverage(): bool
    {
        if (!$this->hasNoCoverage()) {
            throw new Exception;
        }

        return $this->noCoverage;
    }

    /**
     * @psalm-assert-if-true !null $this->noExtensions
     */
    public function hasNoExtensions(): bool
    {
        return $this->noExtensions !== null;
    }

    /**
     * @throws Exception
     */
    public function noExtensions(): bool
    {
        if (!$this->hasNoExtensions()) {
            throw new Exception;
        }

        return $this->noExtensions;
    }

    /**
     * @psalm-assert-if-true !null $this->noInteraction
     */
    public function hasNoInteraction(): bool
    {
        return $this->noInteraction !== null;
    }

    /**
     * @throws Exception
     */
    public function noInteraction(): bool
    {
        if (!$this->hasNoInteraction()) {
            throw new Exception;
        }

        return $this->noInteraction;
    }

    public function hasNoOutput(): bool
    {
        return $this->noOutput !== null;
    }

    /**
     * @throws Exception
     */
    public function noOutput(): bool
    {
        if ($this->noOutput === null) {
            throw new Exception;
        }

        return $this->noOutput;
    }

    /**
     * @psalm-assert-if-true !null $this->noLogging
     */
    public function hasNoLogging(): bool
    {
        return $this->noLogging !== null;
    }

    /**
     * @throws Exception
     */
    public function noLogging(): bool
    {
        if (!$this->hasNoLogging()) {
            throw new Exception;
        }

        return $this->noLogging;
    }

    /**
     * @psalm-assert-if-true !null $this->processIsolation
     */
    public function hasProcessIsolation(): bool
    {
        return $this->processIsolation !== null;
    }

    /**
     * @throws Exception
     */
    public function processIsolation(): bool
    {
        if (!$this->hasProcessIsolation()) {
            throw new Exception;
        }

        return $this->processIsolation;
    }

    /**
     * @psalm-assert-if-true !null $this->randomOrderSeed
     */
    public function hasRandomOrderSeed(): bool
    {
        return $this->randomOrderSeed !== null;
    }

    /**
     * @throws Exception
     */
    public function randomOrderSeed(): int
    {
        if (!$this->hasRandomOrderSeed()) {
            throw new Exception;
        }

        return $this->randomOrderSeed;
    }

    /**
     * @psalm-assert-if-true !null $this->repeat
     */
    public function hasRepeat(): bool
    {
        return $this->repeat !== null;
    }

    /**
     * @throws Exception
     */
    public function repeat(): int
    {
        if (!$this->hasRepeat()) {
            throw new Exception;
        }

        return $this->repeat;
    }

    /**
     * @psalm-assert-if-true !null $this->reportUselessTests
     */
    public function hasReportUselessTests(): bool
    {
        return $this->reportUselessTests !== null;
    }

    /**
     * @throws Exception
     */
    public function reportUselessTests(): bool
    {
        if (!$this->hasReportUselessTests()) {
            throw new Exception;
        }

        return $this->reportUselessTests;
    }

    /**
     * @psalm-assert-if-true !null $this->resolveDependencies
     */
    public function hasResolveDependencies(): bool
    {
        return $this->resolveDependencies !== null;
    }

    /**
     * @throws Exception
     */
    public function resolveDependencies(): bool
    {
        if (!$this->hasResolveDependencies()) {
            throw new Exception;
        }

        return $this->resolveDependencies;
    }

    /**
     * @psalm-assert-if-true !null $this->reverseList
     */
    public function hasReverseList(): bool
    {
        return $this->reverseList !== null;
    }

    /**
     * @throws Exception
     */
    public function reverseList(): bool
    {
        if (!$this->hasReverseList()) {
            throw new Exception;
        }

        return $this->reverseList;
    }

    /**
     * @psalm-assert-if-true !null $this->stderr
     */
    public function hasStderr(): bool
    {
        return $this->stderr !== null;
    }

    /**
     * @throws Exception
     */
    public function stderr(): bool
    {
        if (!$this->hasStderr()) {
            throw new Exception;
        }

        return $this->stderr;
    }

    /**
     * @psalm-assert-if-true !null $this->strictCoverage
     */
    public function hasStrictCoverage(): bool
    {
        return $this->strictCoverage !== null;
    }

    /**
     * @throws Exception
     */
    public function strictCoverage(): bool
    {
        if (!$this->hasStrictCoverage()) {
            throw new Exception;
        }

        return $this->strictCoverage;
    }

    /**
     * @psalm-assert-if-true !null $this->stopOnDefect
     */
    public function hasStopOnDefect(): bool
    {
        return $this->stopOnDefect !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnDefect(): bool
    {
        if (!$this->hasStopOnDefect()) {
            throw new Exception;
        }

        return $this->stopOnDefect;
    }

    /**
     * @psalm-assert-if-true !null $this->stopOnError
     */
    public function hasStopOnError(): bool
    {
        return $this->stopOnError !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnError(): bool
    {
        if (!$this->hasStopOnError()) {
            throw new Exception;
        }

        return $this->stopOnError;
    }

    /**
     * @psalm-assert-if-true !null $this->stopOnFailure
     */
    public function hasStopOnFailure(): bool
    {
        return $this->stopOnFailure !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnFailure(): bool
    {
        if (!$this->hasStopOnFailure()) {
            throw new Exception;
        }

        return $this->stopOnFailure;
    }

    /**
     * @psalm-assert-if-true !null $this->stopOnIncomplete
     */
    public function hasStopOnIncomplete(): bool
    {
        return $this->stopOnIncomplete !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnIncomplete(): bool
    {
        if (!$this->hasStopOnIncomplete()) {
            throw new Exception;
        }

        return $this->stopOnIncomplete;
    }

    /**
     * @psalm-assert-if-true !null $this->stopOnRisky
     */
    public function hasStopOnRisky(): bool
    {
        return $this->stopOnRisky !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnRisky(): bool
    {
        if (!$this->hasStopOnRisky()) {
            throw new Exception;
        }

        return $this->stopOnRisky;
    }

    /**
     * @psalm-assert-if-true !null $this->stopOnSkipped
     */
    public function hasStopOnSkipped(): bool
    {
        return $this->stopOnSkipped !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnSkipped(): bool
    {
        if (!$this->hasStopOnSkipped()) {
            throw new Exception;
        }

        return $this->stopOnSkipped;
    }

    /**
     * @psalm-assert-if-true !null $this->stopOnWarning
     */
    public function hasStopOnWarning(): bool
    {
        return $this->stopOnWarning !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnWarning(): bool
    {
        if (!$this->hasStopOnWarning()) {
            throw new Exception;
        }

        return $this->stopOnWarning;
    }

    /**
     * @psalm-assert-if-true !null $this->teamcityLogfile
     */
    public function hasTeamcityLogfile(): bool
    {
        return $this->teamcityLogfile !== null;
    }

    /**
     * @throws Exception
     */
    public function teamcityLogfile(): string
    {
        if (!$this->hasTeamcityLogfile()) {
            throw new Exception;
        }

        return $this->teamcityLogfile;
    }

    /**
     * @psalm-assert-if-true !null $this->teamcityPrinter
     */
    public function hasTeamCityPrinter(): bool
    {
        return $this->teamCityPrinter !== null;
    }

    /**
     * @throws Exception
     */
    public function teamCityPrinter(): bool
    {
        if (!$this->hasTeamCityPrinter()) {
            throw new Exception;
        }

        return $this->teamCityPrinter;
    }

    /**
     * @psalm-assert-if-true !null $this->testdoxExcludeGroups
     */
    public function hasTestdoxExcludeGroups(): bool
    {
        return $this->testdoxExcludeGroups !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxExcludeGroups(): array
    {
        if (!$this->hasTestdoxExcludeGroups()) {
            throw new Exception;
        }

        return $this->testdoxExcludeGroups;
    }

    /**
     * @psalm-assert-if-true !null $this->testdoxGroups
     */
    public function hasTestdoxGroups(): bool
    {
        return $this->testdoxGroups !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxGroups(): array
    {
        if (!$this->hasTestdoxGroups()) {
            throw new Exception;
        }

        return $this->testdoxGroups;
    }

    /**
     * @psalm-assert-if-true !null $this->testdoxHtmlFile
     */
    public function hasTestdoxHtmlFile(): bool
    {
        return $this->testdoxHtmlFile !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxHtmlFile(): string
    {
        if (!$this->hasTestdoxHtmlFile()) {
            throw new Exception;
        }

        return $this->testdoxHtmlFile;
    }

    /**
     * @psalm-assert-if-true !null $this->testdoxTextFile
     */
    public function hasTestdoxTextFile(): bool
    {
        return $this->testdoxTextFile !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxTextFile(): string
    {
        if (!$this->hasTestdoxTextFile()) {
            throw new Exception;
        }

        return $this->testdoxTextFile;
    }

    /**
     * @psalm-assert-if-true !null $this->testdoxXmlFile
     */
    public function hasTestdoxXmlFile(): bool
    {
        return $this->testdoxXmlFile !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxXmlFile(): string
    {
        if (!$this->hasTestdoxXmlFile()) {
            throw new Exception;
        }

        return $this->testdoxXmlFile;
    }

    /**
     * @psalm-assert-if-true !null $this->testdoxPrinter
     */
    public function hasTestDoxPrinter(): bool
    {
        return $this->testdoxPrinter !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxPrinter(): bool
    {
        if (!$this->hasTestdoxPrinter()) {
            throw new Exception;
        }

        return $this->testdoxPrinter;
    }

    /**
     * @psalm-assert-if-true !null $this->testSuffixes
     */
    public function hasTestSuffixes(): bool
    {
        return $this->testSuffixes !== null;
    }

    /**
     * @throws Exception
     */
    public function testSuffixes(): array
    {
        if (!$this->hasTestSuffixes()) {
            throw new Exception;
        }

        return $this->testSuffixes;
    }

    /**
     * @psalm-assert-if-true !null $this->testSuite
     */
    public function hasTestSuite(): bool
    {
        return $this->testSuite !== null;
    }

    /**
     * @throws Exception
     */
    public function testSuite(): string
    {
        if (!$this->hasTestSuite()) {
            throw new Exception;
        }

        return $this->testSuite;
    }

    /**
     * @psalm-assert-if-true !null $this->excludedTestSuite
     */
    public function hasExcludedTestSuite(): bool
    {
        return $this->excludeTestSuite !== null;
    }

    /**
     * @throws Exception
     */
    public function excludedTestSuite(): string
    {
        if (!$this->hasExcludedTestSuite()) {
            throw new Exception;
        }

        return $this->excludeTestSuite;
    }

    /**
     * @psalm-assert-if-true !null $this->unrecognizedOrderBy
     */
    public function hasUnrecognizedOrderBy(): bool
    {
        return $this->unrecognizedOrderBy !== null;
    }

    /**
     * @throws Exception
     */
    public function unrecognizedOrderBy(): string
    {
        if (!$this->hasUnrecognizedOrderBy()) {
            throw new Exception;
        }

        return $this->unrecognizedOrderBy;
    }

    /**
     * @psalm-assert-if-true !null $this->useDefaultConfiguration
     */
    public function hasUseDefaultConfiguration(): bool
    {
        return $this->useDefaultConfiguration !== null;
    }

    /**
     * @throws Exception
     */
    public function useDefaultConfiguration(): bool
    {
        if (!$this->hasUseDefaultConfiguration()) {
            throw new Exception;
        }

        return $this->useDefaultConfiguration;
    }

    /**
     * @psalm-assert-if-true !null $this->displayDetailsOnIncompleteTests
     */
    public function hasDisplayDetailsOnIncompleteTests(): bool
    {
        return $this->displayDetailsOnIncompleteTests !== null;
    }

    /**
     * @throws Exception
     */
    public function displayDetailsOnIncompleteTests(): bool
    {
        if (!$this->hasDisplayDetailsOnIncompleteTests()) {
            throw new Exception;
        }

        return $this->displayDetailsOnIncompleteTests;
    }

    /**
     * @psalm-assert-if-true !null $this->displayDetailsOnSkippedTests
     */
    public function hasDisplayDetailsOnSkippedTests(): bool
    {
        return $this->displayDetailsOnSkippedTests !== null;
    }

    /**
     * @throws Exception
     */
    public function displayDetailsOnSkippedTests(): bool
    {
        if (!$this->hasDisplayDetailsOnSkippedTests()) {
            throw new Exception;
        }

        return $this->displayDetailsOnSkippedTests;
    }

    /**
     * @psalm-assert-if-true !null $this->version
     */
    public function hasVersion(): bool
    {
        return $this->version !== null;
    }

    /**
     * @throws Exception
     */
    public function version(): bool
    {
        if (!$this->hasVersion()) {
            throw new Exception;
        }

        return $this->version;
    }

    /**
     * @psalm-assert-if-true !null $this->logEventsText
     */
    public function hasLogEventsText(): bool
    {
        return $this->logEventsText !== null;
    }

    /**
     * @throws Exception
     */
    public function logEventsText(): string
    {
        if (!$this->hasLogEventsText()) {
            throw new Exception;
        }

        return $this->logEventsText;
    }

    /**
     * @psalm-assert-if-true !null $this->logEventsVerboseText
     */
    public function hasLogEventsVerboseText(): bool
    {
        return $this->logEventsVerboseText !== null;
    }

    /**
     * @throws Exception
     */
    public function logEventsVerboseText(): string
    {
        if (!$this->hasLogEventsVerboseText()) {
            throw new Exception;
        }

        return $this->logEventsVerboseText;
    }
}
