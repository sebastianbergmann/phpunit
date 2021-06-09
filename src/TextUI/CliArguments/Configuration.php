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

    private ?bool $debug;

    private ?int $defaultTimeLimit;

    private ?bool $disableCodeCoverageIgnore;

    private ?bool $disallowTestOutput;

    private ?bool $enforceTimeLimit;

    private ?array $excludeGroups;

    private ?int $executionOrder;

    private ?int $executionOrderDefects;

    private ?array $extensions;

    private ?array $unavailableExtensions;

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

    private ?bool $verbose;

    private ?bool $version;

    private ?string $plainTextTrace;

    public function __construct(?string $argument, ?string $atLeastVersion, ?bool $backupGlobals, ?bool $backupStaticProperties, ?bool $beStrictAboutChangesToGlobalState, ?string $bootstrap, ?string $cacheDirectory, ?bool $cacheResult, ?string $cacheResultFile, ?bool $checkVersion, ?string $colors, null|int|string $columns, ?string $configuration, ?string $coverageClover, ?string $coverageCobertura, ?string $coverageCrap4J, ?string $coverageHtml, ?string $coveragePhp, ?string $coverageText, ?bool $coverageTextShowUncoveredFiles, ?bool $coverageTextShowOnlySummary, ?string $coverageXml, ?bool $pathCoverage, ?string $coverageCacheDirectory, ?bool $warmCoverageCache, ?bool $debug, ?int $defaultTimeLimit, ?bool $disableCodeCoverageIgnore, ?bool $disallowTestOutput, ?bool $enforceTimeLimit, ?array $excludeGroups, ?int $executionOrder, ?int $executionOrderDefects, ?array $extensions, ?array $unavailableExtensions, ?bool $failOnEmptyTestSuite, ?bool $failOnIncomplete, ?bool $failOnRisky, ?bool $failOnSkipped, ?bool $failOnWarning, ?string $filter, ?bool $generateConfiguration, ?bool $migrateConfiguration, ?array $groups, ?array $testsCovering, ?array $testsUsing, ?bool $help, ?string $includePath, ?array $iniSettings, ?string $junitLogfile, ?bool $listGroups, ?bool $listSuites, ?bool $listTests, ?string $listTestsXml, ?bool $noCoverage, ?bool $noExtensions, ?bool $noInteraction, ?bool $noLogging, ?bool $processIsolation, ?int $randomOrderSeed, ?int $repeat, ?bool $reportUselessTests, ?bool $resolveDependencies, ?bool $reverseList, ?bool $stderr, ?bool $strictCoverage, ?bool $stopOnDefect, ?bool $stopOnError, ?bool $stopOnFailure, ?bool $stopOnIncomplete, ?bool $stopOnRisky, ?bool $stopOnSkipped, ?bool $stopOnWarning, ?string $teamcityLogfile, ?array $testdoxExcludeGroups, ?array $testdoxGroups, ?string $testdoxHtmlFile, ?string $testdoxTextFile, ?string $testdoxXmlFile, ?array $testSuffixes, ?string $testSuite, ?string $excludeTestSuite, ?string $unrecognizedOrderBy, ?bool $useDefaultConfiguration, ?bool $verbose, ?bool $version, ?array $coverageFilter, ?string $plainTextTrace, ?bool $printerTeamCity, ?bool $printerTestDox)
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
        $this->debug                             = $debug;
        $this->defaultTimeLimit                  = $defaultTimeLimit;
        $this->disableCodeCoverageIgnore         = $disableCodeCoverageIgnore;
        $this->disallowTestOutput                = $disallowTestOutput;
        $this->enforceTimeLimit                  = $enforceTimeLimit;
        $this->excludeGroups                     = $excludeGroups;
        $this->executionOrder                    = $executionOrder;
        $this->executionOrderDefects             = $executionOrderDefects;
        $this->extensions                        = $extensions;
        $this->unavailableExtensions             = $unavailableExtensions;
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
        $this->verbose                           = $verbose;
        $this->version                           = $version;
        $this->plainTextTrace                    = $plainTextTrace;
        $this->teamCityPrinter                   = $printerTeamCity;
        $this->testdoxPrinter                    = $printerTestDox;
    }

    public function hasArgument(): bool
    {
        return $this->argument !== null;
    }

    /**
     * @throws Exception
     */
    public function argument(): string
    {
        if ($this->argument === null) {
            throw new Exception;
        }

        return $this->argument;
    }

    public function hasAtLeastVersion(): bool
    {
        return $this->atLeastVersion !== null;
    }

    /**
     * @throws Exception
     */
    public function atLeastVersion(): string
    {
        if ($this->atLeastVersion === null) {
            throw new Exception;
        }

        return $this->atLeastVersion;
    }

    public function hasBackupGlobals(): bool
    {
        return $this->backupGlobals !== null;
    }

    /**
     * @throws Exception
     */
    public function backupGlobals(): bool
    {
        if ($this->backupGlobals === null) {
            throw new Exception;
        }

        return $this->backupGlobals;
    }

    public function hasBackupStaticProperties(): bool
    {
        return $this->backupStaticProperties !== null;
    }

    /**
     * @throws Exception
     */
    public function backupStaticProperties(): bool
    {
        if ($this->backupStaticProperties === null) {
            throw new Exception;
        }

        return $this->backupStaticProperties;
    }

    public function hasBeStrictAboutChangesToGlobalState(): bool
    {
        return $this->beStrictAboutChangesToGlobalState !== null;
    }

    /**
     * @throws Exception
     */
    public function beStrictAboutChangesToGlobalState(): bool
    {
        if ($this->beStrictAboutChangesToGlobalState === null) {
            throw new Exception;
        }

        return $this->beStrictAboutChangesToGlobalState;
    }

    public function hasBootstrap(): bool
    {
        return $this->bootstrap !== null;
    }

    /**
     * @throws Exception
     */
    public function bootstrap(): string
    {
        if ($this->bootstrap === null) {
            throw new Exception;
        }

        return $this->bootstrap;
    }

    public function hasCacheDirectory(): bool
    {
        return $this->cacheDirectory !== null;
    }

    /**
     * @throws Exception
     */
    public function cacheDirectory(): string
    {
        if ($this->cacheDirectory === null) {
            throw new Exception;
        }

        return $this->cacheDirectory;
    }

    public function hasCacheResult(): bool
    {
        return $this->cacheResult !== null;
    }

    /**
     * @throws Exception
     */
    public function cacheResult(): bool
    {
        if ($this->cacheResult === null) {
            throw new Exception;
        }

        return $this->cacheResult;
    }

    public function hasCacheResultFile(): bool
    {
        return $this->cacheResultFile !== null;
    }

    /**
     * @throws Exception
     */
    public function cacheResultFile(): string
    {
        if ($this->cacheResultFile === null) {
            throw new Exception;
        }

        return $this->cacheResultFile;
    }

    public function hasCheckVersion(): bool
    {
        return $this->checkVersion !== null;
    }

    /**
     * @throws Exception
     */
    public function checkVersion(): bool
    {
        if ($this->checkVersion === null) {
            throw new Exception;
        }

        return $this->checkVersion;
    }

    public function hasColors(): bool
    {
        return $this->colors !== null;
    }

    /**
     * @throws Exception
     */
    public function colors(): string
    {
        if ($this->colors === null) {
            throw new Exception;
        }

        return $this->colors;
    }

    public function hasColumns(): bool
    {
        return $this->columns !== null;
    }

    /**
     * @throws Exception
     */
    public function columns()
    {
        if ($this->columns === null) {
            throw new Exception;
        }

        return $this->columns;
    }

    public function hasConfiguration(): bool
    {
        return $this->configuration !== null;
    }

    /**
     * @throws Exception
     */
    public function configuration(): string
    {
        if ($this->configuration === null) {
            throw new Exception;
        }

        return $this->configuration;
    }

    public function hasCoverageFilter(): bool
    {
        return $this->coverageFilter !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageFilter(): array
    {
        if ($this->coverageFilter === null) {
            throw new Exception;
        }

        return $this->coverageFilter;
    }

    public function hasCoverageClover(): bool
    {
        return $this->coverageClover !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageClover(): string
    {
        if ($this->coverageClover === null) {
            throw new Exception;
        }

        return $this->coverageClover;
    }

    public function hasCoverageCobertura(): bool
    {
        return $this->coverageCobertura !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageCobertura(): string
    {
        if ($this->coverageCobertura === null) {
            throw new Exception;
        }

        return $this->coverageCobertura;
    }

    public function hasCoverageCrap4J(): bool
    {
        return $this->coverageCrap4J !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageCrap4J(): string
    {
        if ($this->coverageCrap4J === null) {
            throw new Exception;
        }

        return $this->coverageCrap4J;
    }

    public function hasCoverageHtml(): bool
    {
        return $this->coverageHtml !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageHtml(): string
    {
        if ($this->coverageHtml === null) {
            throw new Exception;
        }

        return $this->coverageHtml;
    }

    public function hasCoveragePhp(): bool
    {
        return $this->coveragePhp !== null;
    }

    /**
     * @throws Exception
     */
    public function coveragePhp(): string
    {
        if ($this->coveragePhp === null) {
            throw new Exception;
        }

        return $this->coveragePhp;
    }

    public function hasCoverageText(): bool
    {
        return $this->coverageText !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageText(): string
    {
        if ($this->coverageText === null) {
            throw new Exception;
        }

        return $this->coverageText;
    }

    public function hasCoverageTextShowUncoveredFiles(): bool
    {
        return $this->coverageTextShowUncoveredFiles !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageTextShowUncoveredFiles(): bool
    {
        if ($this->coverageTextShowUncoveredFiles === null) {
            throw new Exception;
        }

        return $this->coverageTextShowUncoveredFiles;
    }

    public function hasCoverageTextShowOnlySummary(): bool
    {
        return $this->coverageTextShowOnlySummary !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageTextShowOnlySummary(): bool
    {
        if ($this->coverageTextShowOnlySummary === null) {
            throw new Exception;
        }

        return $this->coverageTextShowOnlySummary;
    }

    public function hasCoverageXml(): bool
    {
        return $this->coverageXml !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageXml(): string
    {
        if ($this->coverageXml === null) {
            throw new Exception;
        }

        return $this->coverageXml;
    }

    public function hasPathCoverage(): bool
    {
        return $this->pathCoverage !== null;
    }

    /**
     * @throws Exception
     */
    public function pathCoverage(): bool
    {
        if ($this->pathCoverage === null) {
            throw new Exception;
        }

        return $this->pathCoverage;
    }

    public function hasCoverageCacheDirectory(): bool
    {
        return $this->coverageCacheDirectory !== null;
    }

    /**
     * @throws Exception
     */
    public function coverageCacheDirectory(): string
    {
        if ($this->coverageCacheDirectory === null) {
            throw new Exception;
        }

        return $this->coverageCacheDirectory;
    }

    public function hasWarmCoverageCache(): bool
    {
        return $this->warmCoverageCache !== null;
    }

    /**
     * @throws Exception
     */
    public function warmCoverageCache(): bool
    {
        if ($this->warmCoverageCache === null) {
            throw new Exception;
        }

        return $this->warmCoverageCache;
    }

    public function hasDebug(): bool
    {
        return $this->debug !== null;
    }

    /**
     * @throws Exception
     */
    public function debug(): bool
    {
        if ($this->debug === null) {
            throw new Exception;
        }

        return $this->debug;
    }

    public function hasDefaultTimeLimit(): bool
    {
        return $this->defaultTimeLimit !== null;
    }

    /**
     * @throws Exception
     */
    public function defaultTimeLimit(): int
    {
        if ($this->defaultTimeLimit === null) {
            throw new Exception;
        }

        return $this->defaultTimeLimit;
    }

    public function hasDisableCodeCoverageIgnore(): bool
    {
        return $this->disableCodeCoverageIgnore !== null;
    }

    /**
     * @throws Exception
     */
    public function disableCodeCoverageIgnore(): bool
    {
        if ($this->disableCodeCoverageIgnore === null) {
            throw new Exception;
        }

        return $this->disableCodeCoverageIgnore;
    }

    public function hasDisallowTestOutput(): bool
    {
        return $this->disallowTestOutput !== null;
    }

    /**
     * @throws Exception
     */
    public function disallowTestOutput(): bool
    {
        if ($this->disallowTestOutput === null) {
            throw new Exception;
        }

        return $this->disallowTestOutput;
    }

    public function hasEnforceTimeLimit(): bool
    {
        return $this->enforceTimeLimit !== null;
    }

    /**
     * @throws Exception
     */
    public function enforceTimeLimit(): bool
    {
        if ($this->enforceTimeLimit === null) {
            throw new Exception;
        }

        return $this->enforceTimeLimit;
    }

    public function hasExcludeGroups(): bool
    {
        return $this->excludeGroups !== null;
    }

    /**
     * @throws Exception
     */
    public function excludeGroups(): array
    {
        if ($this->excludeGroups === null) {
            throw new Exception;
        }

        return $this->excludeGroups;
    }

    public function hasExecutionOrder(): bool
    {
        return $this->executionOrder !== null;
    }

    /**
     * @throws Exception
     */
    public function executionOrder(): int
    {
        if ($this->executionOrder === null) {
            throw new Exception;
        }

        return $this->executionOrder;
    }

    public function hasExecutionOrderDefects(): bool
    {
        return $this->executionOrderDefects !== null;
    }

    /**
     * @throws Exception
     */
    public function executionOrderDefects(): int
    {
        if ($this->executionOrderDefects === null) {
            throw new Exception;
        }

        return $this->executionOrderDefects;
    }

    public function hasFailOnEmptyTestSuite(): bool
    {
        return $this->failOnEmptyTestSuite !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnEmptyTestSuite(): bool
    {
        if ($this->failOnEmptyTestSuite === null) {
            throw new Exception;
        }

        return $this->failOnEmptyTestSuite;
    }

    public function hasFailOnIncomplete(): bool
    {
        return $this->failOnIncomplete !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnIncomplete(): bool
    {
        if ($this->failOnIncomplete === null) {
            throw new Exception;
        }

        return $this->failOnIncomplete;
    }

    public function hasFailOnRisky(): bool
    {
        return $this->failOnRisky !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnRisky(): bool
    {
        if ($this->failOnRisky === null) {
            throw new Exception;
        }

        return $this->failOnRisky;
    }

    public function hasFailOnSkipped(): bool
    {
        return $this->failOnSkipped !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnSkipped(): bool
    {
        if ($this->failOnSkipped === null) {
            throw new Exception;
        }

        return $this->failOnSkipped;
    }

    public function hasFailOnWarning(): bool
    {
        return $this->failOnWarning !== null;
    }

    /**
     * @throws Exception
     */
    public function failOnWarning(): bool
    {
        if ($this->failOnWarning === null) {
            throw new Exception;
        }

        return $this->failOnWarning;
    }

    public function hasFilter(): bool
    {
        return $this->filter !== null;
    }

    /**
     * @throws Exception
     */
    public function filter(): string
    {
        if ($this->filter === null) {
            throw new Exception;
        }

        return $this->filter;
    }

    public function hasGenerateConfiguration(): bool
    {
        return $this->generateConfiguration !== null;
    }

    /**
     * @throws Exception
     */
    public function generateConfiguration(): bool
    {
        if ($this->generateConfiguration === null) {
            throw new Exception;
        }

        return $this->generateConfiguration;
    }

    public function hasMigrateConfiguration(): bool
    {
        return $this->migrateConfiguration !== null;
    }

    /**
     * @throws Exception
     */
    public function migrateConfiguration(): bool
    {
        if ($this->migrateConfiguration === null) {
            throw new Exception;
        }

        return $this->migrateConfiguration;
    }

    public function hasGroups(): bool
    {
        return $this->groups !== null;
    }

    /**
     * @throws Exception
     */
    public function groups(): array
    {
        if ($this->groups === null) {
            throw new Exception;
        }

        return $this->groups;
    }

    public function hasTestsCovering(): bool
    {
        return $this->testsCovering !== null;
    }

    /**
     * @throws Exception
     */
    public function testsCovering(): array
    {
        if ($this->testsCovering === null) {
            throw new Exception;
        }

        return $this->testsCovering;
    }

    public function hasTestsUsing(): bool
    {
        return $this->testsUsing !== null;
    }

    /**
     * @throws Exception
     */
    public function testsUsing(): array
    {
        if ($this->testsUsing === null) {
            throw new Exception;
        }

        return $this->testsUsing;
    }

    public function hasHelp(): bool
    {
        return $this->help !== null;
    }

    /**
     * @throws Exception
     */
    public function help(): bool
    {
        if ($this->help === null) {
            throw new Exception;
        }

        return $this->help;
    }

    public function hasIncludePath(): bool
    {
        return $this->includePath !== null;
    }

    /**
     * @throws Exception
     */
    public function includePath(): string
    {
        if ($this->includePath === null) {
            throw new Exception;
        }

        return $this->includePath;
    }

    public function hasIniSettings(): bool
    {
        return $this->iniSettings !== null;
    }

    /**
     * @throws Exception
     */
    public function iniSettings(): array
    {
        if ($this->iniSettings === null) {
            throw new Exception;
        }

        return $this->iniSettings;
    }

    public function hasJunitLogfile(): bool
    {
        return $this->junitLogfile !== null;
    }

    /**
     * @throws Exception
     */
    public function junitLogfile(): string
    {
        if ($this->junitLogfile === null) {
            throw new Exception;
        }

        return $this->junitLogfile;
    }

    public function hasListGroups(): bool
    {
        return $this->listGroups !== null;
    }

    /**
     * @throws Exception
     */
    public function listGroups(): bool
    {
        if ($this->listGroups === null) {
            throw new Exception;
        }

        return $this->listGroups;
    }

    public function hasListSuites(): bool
    {
        return $this->listSuites !== null;
    }

    /**
     * @throws Exception
     */
    public function listSuites(): bool
    {
        if ($this->listSuites === null) {
            throw new Exception;
        }

        return $this->listSuites;
    }

    public function hasListTests(): bool
    {
        return $this->listTests !== null;
    }

    /**
     * @throws Exception
     */
    public function listTests(): bool
    {
        if ($this->listTests === null) {
            throw new Exception;
        }

        return $this->listTests;
    }

    public function hasListTestsXml(): bool
    {
        return $this->listTestsXml !== null;
    }

    /**
     * @throws Exception
     */
    public function listTestsXml(): string
    {
        if ($this->listTestsXml === null) {
            throw new Exception;
        }

        return $this->listTestsXml;
    }

    public function hasNoCoverage(): bool
    {
        return $this->noCoverage !== null;
    }

    /**
     * @throws Exception
     */
    public function noCoverage(): bool
    {
        if ($this->noCoverage === null) {
            throw new Exception;
        }

        return $this->noCoverage;
    }

    public function hasNoExtensions(): bool
    {
        return $this->noExtensions !== null;
    }

    /**
     * @throws Exception
     */
    public function noExtensions(): bool
    {
        if ($this->noExtensions === null) {
            throw new Exception;
        }

        return $this->noExtensions;
    }

    public function hasExtensions(): bool
    {
        return $this->extensions !== null;
    }

    /**
     * @throws Exception
     */
    public function extensions(): array
    {
        if ($this->extensions === null) {
            throw new Exception;
        }

        return $this->extensions;
    }

    public function hasUnavailableExtensions(): bool
    {
        return $this->unavailableExtensions !== null;
    }

    /**
     * @throws Exception
     */
    public function unavailableExtensions(): array
    {
        if ($this->unavailableExtensions === null) {
            throw new Exception;
        }

        return $this->unavailableExtensions;
    }

    public function hasNoInteraction(): bool
    {
        return $this->noInteraction !== null;
    }

    /**
     * @throws Exception
     */
    public function noInteraction(): bool
    {
        if ($this->noInteraction === null) {
            throw new Exception;
        }

        return $this->noInteraction;
    }

    public function hasNoLogging(): bool
    {
        return $this->noLogging !== null;
    }

    /**
     * @throws Exception
     */
    public function noLogging(): bool
    {
        if ($this->noLogging === null) {
            throw new Exception;
        }

        return $this->noLogging;
    }

    public function hasProcessIsolation(): bool
    {
        return $this->processIsolation !== null;
    }

    /**
     * @throws Exception
     */
    public function processIsolation(): bool
    {
        if ($this->processIsolation === null) {
            throw new Exception;
        }

        return $this->processIsolation;
    }

    public function hasRandomOrderSeed(): bool
    {
        return $this->randomOrderSeed !== null;
    }

    /**
     * @throws Exception
     */
    public function randomOrderSeed(): int
    {
        if ($this->randomOrderSeed === null) {
            throw new Exception;
        }

        return $this->randomOrderSeed;
    }

    public function hasRepeat(): bool
    {
        return $this->repeat !== null;
    }

    /**
     * @throws Exception
     */
    public function repeat(): int
    {
        if ($this->repeat === null) {
            throw new Exception;
        }

        return $this->repeat;
    }

    public function hasReportUselessTests(): bool
    {
        return $this->reportUselessTests !== null;
    }

    /**
     * @throws Exception
     */
    public function reportUselessTests(): bool
    {
        if ($this->reportUselessTests === null) {
            throw new Exception;
        }

        return $this->reportUselessTests;
    }

    public function hasResolveDependencies(): bool
    {
        return $this->resolveDependencies !== null;
    }

    /**
     * @throws Exception
     */
    public function resolveDependencies(): bool
    {
        if ($this->resolveDependencies === null) {
            throw new Exception;
        }

        return $this->resolveDependencies;
    }

    public function hasReverseList(): bool
    {
        return $this->reverseList !== null;
    }

    /**
     * @throws Exception
     */
    public function reverseList(): bool
    {
        if ($this->reverseList === null) {
            throw new Exception;
        }

        return $this->reverseList;
    }

    public function hasStderr(): bool
    {
        return $this->stderr !== null;
    }

    /**
     * @throws Exception
     */
    public function stderr(): bool
    {
        if ($this->stderr === null) {
            throw new Exception;
        }

        return $this->stderr;
    }

    public function hasStrictCoverage(): bool
    {
        return $this->strictCoverage !== null;
    }

    /**
     * @throws Exception
     */
    public function strictCoverage(): bool
    {
        if ($this->strictCoverage === null) {
            throw new Exception;
        }

        return $this->strictCoverage;
    }

    public function hasStopOnDefect(): bool
    {
        return $this->stopOnDefect !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnDefect(): bool
    {
        if ($this->stopOnDefect === null) {
            throw new Exception;
        }

        return $this->stopOnDefect;
    }

    public function hasStopOnError(): bool
    {
        return $this->stopOnError !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnError(): bool
    {
        if ($this->stopOnError === null) {
            throw new Exception;
        }

        return $this->stopOnError;
    }

    public function hasStopOnFailure(): bool
    {
        return $this->stopOnFailure !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnFailure(): bool
    {
        if ($this->stopOnFailure === null) {
            throw new Exception;
        }

        return $this->stopOnFailure;
    }

    public function hasStopOnIncomplete(): bool
    {
        return $this->stopOnIncomplete !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnIncomplete(): bool
    {
        if ($this->stopOnIncomplete === null) {
            throw new Exception;
        }

        return $this->stopOnIncomplete;
    }

    public function hasStopOnRisky(): bool
    {
        return $this->stopOnRisky !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnRisky(): bool
    {
        if ($this->stopOnRisky === null) {
            throw new Exception;
        }

        return $this->stopOnRisky;
    }

    public function hasStopOnSkipped(): bool
    {
        return $this->stopOnSkipped !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnSkipped(): bool
    {
        if ($this->stopOnSkipped === null) {
            throw new Exception;
        }

        return $this->stopOnSkipped;
    }

    public function hasStopOnWarning(): bool
    {
        return $this->stopOnWarning !== null;
    }

    /**
     * @throws Exception
     */
    public function stopOnWarning(): bool
    {
        if ($this->stopOnWarning === null) {
            throw new Exception;
        }

        return $this->stopOnWarning;
    }

    public function hasTeamcityLogfile(): bool
    {
        return $this->teamcityLogfile !== null;
    }

    /**
     * @throws Exception
     */
    public function teamcityLogfile(): string
    {
        if ($this->teamcityLogfile === null) {
            throw new Exception;
        }

        return $this->teamcityLogfile;
    }

    public function hasTeamCityPrinter(): bool
    {
        return $this->teamCityPrinter !== null;
    }

    /**
     * @throws Exception
     */
    public function teamCityPrinter(): bool
    {
        if ($this->teamCityPrinter === null) {
            throw new Exception;
        }

        return $this->teamCityPrinter;
    }

    public function hasTestdoxExcludeGroups(): bool
    {
        return $this->testdoxExcludeGroups !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxExcludeGroups(): array
    {
        if ($this->testdoxExcludeGroups === null) {
            throw new Exception;
        }

        return $this->testdoxExcludeGroups;
    }

    public function hasTestdoxGroups(): bool
    {
        return $this->testdoxGroups !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxGroups(): array
    {
        if ($this->testdoxGroups === null) {
            throw new Exception;
        }

        return $this->testdoxGroups;
    }

    public function hasTestdoxHtmlFile(): bool
    {
        return $this->testdoxHtmlFile !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxHtmlFile(): string
    {
        if ($this->testdoxHtmlFile === null) {
            throw new Exception;
        }

        return $this->testdoxHtmlFile;
    }

    public function hasTestdoxTextFile(): bool
    {
        return $this->testdoxTextFile !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxTextFile(): string
    {
        if ($this->testdoxTextFile === null) {
            throw new Exception;
        }

        return $this->testdoxTextFile;
    }

    public function hasTestdoxXmlFile(): bool
    {
        return $this->testdoxXmlFile !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxXmlFile(): string
    {
        if ($this->testdoxXmlFile === null) {
            throw new Exception;
        }

        return $this->testdoxXmlFile;
    }

    public function hasTestDoxPrinter(): bool
    {
        return $this->testdoxPrinter !== null;
    }

    /**
     * @throws Exception
     */
    public function testdoxPrinter(): bool
    {
        if ($this->testdoxPrinter === null) {
            throw new Exception;
        }

        return $this->testdoxPrinter;
    }

    public function hasTestSuffixes(): bool
    {
        return $this->testSuffixes !== null;
    }

    /**
     * @throws Exception
     */
    public function testSuffixes(): array
    {
        if ($this->testSuffixes === null) {
            throw new Exception;
        }

        return $this->testSuffixes;
    }

    public function hasTestSuite(): bool
    {
        return $this->testSuite !== null;
    }

    public function hasExcludedTestSuite(): bool
    {
        return $this->excludeTestSuite !== null;
    }

    /**
     * @throws Exception
     */
    public function testSuite(): string
    {
        if ($this->testSuite === null) {
            throw new Exception;
        }

        return $this->testSuite;
    }

    /**
     * @throws Exception
     */
    public function excludedTestSuite(): string
    {
        if ($this->excludeTestSuite === null) {
            throw new Exception;
        }

        return $this->excludeTestSuite;
    }

    public function hasUnrecognizedOrderBy(): bool
    {
        return $this->unrecognizedOrderBy !== null;
    }

    /**
     * @throws Exception
     */
    public function unrecognizedOrderBy(): string
    {
        if ($this->unrecognizedOrderBy === null) {
            throw new Exception;
        }

        return $this->unrecognizedOrderBy;
    }

    public function hasUseDefaultConfiguration(): bool
    {
        return $this->useDefaultConfiguration !== null;
    }

    /**
     * @throws Exception
     */
    public function useDefaultConfiguration(): bool
    {
        if ($this->useDefaultConfiguration === null) {
            throw new Exception;
        }

        return $this->useDefaultConfiguration;
    }

    public function hasVerbose(): bool
    {
        return $this->verbose !== null;
    }

    /**
     * @throws Exception
     */
    public function verbose(): bool
    {
        if ($this->verbose === null) {
            throw new Exception;
        }

        return $this->verbose;
    }

    public function hasVersion(): bool
    {
        return $this->version !== null;
    }

    /**
     * @throws Exception
     */
    public function version(): bool
    {
        if ($this->version === null) {
            throw new Exception;
        }

        return $this->version;
    }

    public function hasPlainTextTrace(): bool
    {
        return $this->plainTextTrace !== null;
    }

    /**
     * @throws Exception
     */
    public function plainTextTrace(): string
    {
        if ($this->plainTextTrace === null) {
            throw new Exception;
        }

        return $this->plainTextTrace;
    }
}
