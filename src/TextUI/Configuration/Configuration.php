<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use function explode;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Configuration
{
    public const string COLOR_NEVER   = 'never';
    public const string COLOR_AUTO    = 'auto';
    public const string COLOR_ALWAYS  = 'always';
    public const string COLOR_DEFAULT = self::COLOR_NEVER;

    /**
     * @var list<non-empty-string>
     */
    private array $cliArguments;

    /**
     * @var ?non-empty-string
     */
    private ?string $testFilesFile;

    /**
     * @var ?non-empty-string
     */
    private ?string $configurationFile;

    /**
     * @var ?non-empty-string
     */
    private ?string $bootstrap;

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private array $bootstrapForTestSuite;
    private bool $cacheResult;

    /**
     * @var ?non-empty-string
     */
    private ?string $cacheDirectory;

    /**
     * @var ?non-empty-string
     */
    private ?string $coverageCacheDirectory;
    private Source $source;
    private bool $pathCoverage;

    /**
     * @var ?non-empty-string
     */
    private ?string $coverageClover;

    /**
     * @var ?non-empty-string
     */
    private ?string $coverageCobertura;

    /**
     * @var ?non-empty-string
     */
    private ?string $coverageCrap4j;

    /**
     * @var non-negative-int
     */
    private int $coverageCrap4jThreshold;

    /**
     * @var ?non-empty-string
     */
    private ?string $coverageHtml;

    /**
     * @var non-negative-int
     */
    private int $coverageHtmlLowUpperBound;

    /**
     * @var non-negative-int
     */
    private int $coverageHtmlHighLowerBound;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorSuccessLow;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorSuccessLowDark;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorSuccessMedium;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorSuccessMediumDark;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorSuccessHigh;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorSuccessHighDark;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorSuccessBar;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorSuccessBarDark;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorWarning;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorWarningDark;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorWarningBar;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorWarningBarDark;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorDanger;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorDangerDark;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorDangerBar;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorDangerBarDark;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorBreadcrumbs;

    /**
     * @var non-empty-string
     */
    private string $coverageHtmlColorBreadcrumbsDark;

    /**
     * @var ?non-empty-string
     */
    private ?string $coverageHtmlCustomCssFile;

    /**
     * @var ?non-empty-string
     */
    private ?string $coverageOpenClover;

    /**
     * @var ?non-empty-string
     */
    private ?string $coveragePhp;

    /**
     * @var ?non-empty-string
     */
    private ?string $coverageText;
    private bool $coverageTextShowUncoveredFiles;
    private bool $coverageTextShowOnlySummary;

    /**
     * @var ?non-empty-string
     */
    private ?string $coverageXml;
    private bool $coverageXmlIncludeSource;

    /**
     * @var non-empty-string
     */
    private string $testResultCacheFile;
    private bool $ignoreDeprecatedCodeUnitsFromCodeCoverage;
    private bool $disableCodeCoverageIgnore;
    private bool $failOnAllIssues;
    private bool $failOnDeprecation;
    private bool $failOnPhpunitDeprecation;
    private bool $failOnPhpunitNotice;
    private bool $failOnPhpunitWarning;
    private bool $failOnEmptyTestSuite;
    private bool $failOnIncomplete;
    private bool $failOnNotice;
    private bool $failOnRisky;
    private bool $failOnSkipped;
    private bool $failOnWarning;
    private bool $doNotFailOnDeprecation;
    private bool $doNotFailOnPhpunitDeprecation;
    private bool $doNotFailOnPhpunitNotice;
    private bool $doNotFailOnPhpunitWarning;
    private bool $doNotFailOnEmptyTestSuite;
    private bool $doNotFailOnIncomplete;
    private bool $doNotFailOnNotice;
    private bool $doNotFailOnRisky;
    private bool $doNotFailOnSkipped;
    private bool $doNotFailOnWarning;

    /**
     * @var non-negative-int
     */
    private int $stopOnDefect;

    /**
     * @var non-negative-int
     */
    private int $stopOnDeprecation;

    /**
     * @var ?non-empty-string
     */
    private ?string $specificDeprecationToStopOn;

    /**
     * @var non-negative-int
     */
    private int $stopOnError;

    /**
     * @var non-negative-int
     */
    private int $stopOnFailure;

    /**
     * @var non-negative-int
     */
    private int $stopOnIncomplete;

    /**
     * @var non-negative-int
     */
    private int $stopOnNotice;

    /**
     * @var non-negative-int
     */
    private int $stopOnRisky;

    /**
     * @var non-negative-int
     */
    private int $stopOnSkipped;

    /**
     * @var non-negative-int
     */
    private int $stopOnWarning;
    private bool $outputToStandardErrorStream;

    /**
     * @var positive-int
     */
    private int $columns;
    private bool $noExtensions;

    /**
     * @var ?non-empty-string
     */
    private ?string $pharExtensionDirectory;

    /**
     * @var list<array{className: non-empty-string, parameters: array<string, string>}>
     */
    private array $extensionBootstrappers;
    private bool $backupGlobals;
    private bool $backupStaticProperties;
    private bool $beStrictAboutChangesToGlobalState;
    private bool $colors;
    private bool $processIsolation;
    private bool $enforceTimeLimit;

    /**
     * @var non-negative-int
     */
    private int $defaultTimeLimit;

    /**
     * @var positive-int
     */
    private int $diffContext;

    /**
     * @var positive-int
     */
    private int $timeoutForSmallTests;

    /**
     * @var positive-int
     */
    private int $timeoutForMediumTests;

    /**
     * @var positive-int
     */
    private int $timeoutForLargeTests;
    private bool $reportUselessTests;
    private bool $strictCoverage;
    private bool $requireCoverageContribution;
    private bool $disallowTestOutput;
    private bool $displayDetailsOnAllIssues;
    private bool $displayDetailsOnIncompleteTests;
    private bool $displayDetailsOnSkippedTests;
    private bool $displayDetailsOnTestsThatTriggerDeprecations;
    private bool $displayDetailsOnPhpunitDeprecations;
    private bool $displayDetailsOnPhpunitNotices;
    private bool $displayDetailsOnTestsThatTriggerErrors;
    private bool $displayDetailsOnTestsThatTriggerNotices;
    private bool $displayDetailsOnTestsThatTriggerWarnings;
    private bool $reverseDefectList;
    private bool $requireCoverageMetadata;
    private bool $requireSealedMockObjects;
    private bool $noProgress;
    private bool $noResults;
    private bool $noOutput;
    private int $executionOrder;
    private int $executionOrderDefects;
    private bool $resolveDependencies;

    /**
     * @var ?non-empty-string
     */
    private ?string $logfileTeamcity;

    /**
     * @var ?non-empty-string
     */
    private ?string $logfileJunit;

    /**
     * @var ?non-empty-string
     */
    private ?string $logfileOtr;
    private bool $includeGitInformation;
    private bool $includeGitInformationInOtrLogfile;

    /**
     * @var ?non-empty-string
     */
    private ?string $logfileTestdoxHtml;

    /**
     * @var ?non-empty-string
     */
    private ?string $logfileTestdoxText;

    /**
     * @var ?non-empty-string
     */
    private ?string $logEventsText;

    /**
     * @var ?non-empty-string
     */
    private ?string $logEventsVerboseText;

    /**
     * @var ?non-empty-list<non-empty-string>
     */
    private ?array $testsCovering;

    /**
     * @var ?non-empty-list<non-empty-string>
     */
    private ?array $testsUsing;

    /**
     * @var ?non-empty-list<non-empty-string>
     */
    private ?array $testsRequiringPhpExtension;
    private bool $teamCityOutput;
    private bool $testDoxOutput;
    private bool $testDoxOutputSummary;
    private ?string $filter;
    private ?string $excludeFilter;

    /**
     * @var ?non-empty-string
     */
    private ?string $testIdFilterFile;

    /**
     * @var ?non-empty-string
     */
    private ?string $testIdFilter;

    /**
     * @var list<non-empty-string>
     */
    private array $groups;

    /**
     * @var list<non-empty-string>
     */
    private array $excludeGroups;

    /**
     * @var positive-int
     */
    private int $randomOrderSeed;

    /**
     * @var positive-int
     */
    private int $repeat;
    private bool $includeUncoveredFiles;
    private TestSuiteCollection $testSuite;
    private string $includeTestSuite;
    private string $excludeTestSuite;

    /**
     * @var ?non-empty-string
     */
    private ?string $defaultTestSuite;
    private bool $ignoreTestSelectionInXmlConfiguration;

    /**
     * @var non-empty-list<non-empty-string>
     */
    private array $testSuffixes;
    private Php $php;
    private bool $controlGarbageCollector;

    /**
     * @var positive-int
     */
    private int $numberOfTestsBeforeGarbageCollection;

    /**
     * @var null|non-empty-string
     */
    private ?string $generateBaseline;
    private bool $debug;
    private bool $withTelemetry;

    /**
     * @var non-negative-int
     */
    private int $shortenArraysForExportThreshold;

    /**
     * @param list<non-empty-string>                                                      $cliArguments
     * @param ?non-empty-string                                                           $testFilesFile
     * @param ?non-empty-string                                                           $configurationFile
     * @param ?non-empty-string                                                           $bootstrap
     * @param array<non-empty-string, non-empty-string>                                   $bootstrapForTestSuite
     * @param ?non-empty-string                                                           $cacheDirectory
     * @param ?non-empty-string                                                           $coverageCacheDirectory
     * @param non-empty-string                                                            $testResultCacheFile
     * @param ?non-empty-string                                                           $coverageClover
     * @param ?non-empty-string                                                           $coverageCobertura
     * @param ?non-empty-string                                                           $coverageCrap4j
     * @param non-negative-int                                                            $coverageCrap4jThreshold
     * @param ?non-empty-string                                                           $coverageHtml
     * @param non-negative-int                                                            $coverageHtmlLowUpperBound
     * @param non-negative-int                                                            $coverageHtmlHighLowerBound
     * @param non-empty-string                                                            $coverageHtmlColorSuccessLow
     * @param non-empty-string                                                            $coverageHtmlColorSuccessLowDark
     * @param non-empty-string                                                            $coverageHtmlColorSuccessMedium
     * @param non-empty-string                                                            $coverageHtmlColorSuccessMediumDark
     * @param non-empty-string                                                            $coverageHtmlColorSuccessHigh
     * @param non-empty-string                                                            $coverageHtmlColorSuccessHighDark
     * @param non-empty-string                                                            $coverageHtmlColorSuccessBar
     * @param non-empty-string                                                            $coverageHtmlColorSuccessBarDark
     * @param non-empty-string                                                            $coverageHtmlColorWarning
     * @param non-empty-string                                                            $coverageHtmlColorWarningDark
     * @param non-empty-string                                                            $coverageHtmlColorWarningBar
     * @param non-empty-string                                                            $coverageHtmlColorWarningBarDark
     * @param non-empty-string                                                            $coverageHtmlColorDanger
     * @param non-empty-string                                                            $coverageHtmlColorDangerDark
     * @param non-empty-string                                                            $coverageHtmlColorDangerBar
     * @param non-empty-string                                                            $coverageHtmlColorDangerBarDark
     * @param non-empty-string                                                            $coverageHtmlColorBreadcrumbs
     * @param non-empty-string                                                            $coverageHtmlColorBreadcrumbsDark
     * @param ?non-empty-string                                                           $coverageHtmlCustomCssFile
     * @param ?non-empty-string                                                           $coverageOpenClover
     * @param ?non-empty-string                                                           $coveragePhp
     * @param ?non-empty-string                                                           $coverageText
     * @param ?non-empty-string                                                           $coverageXml
     * @param non-negative-int                                                            $stopOnDefect
     * @param non-negative-int                                                            $stopOnDeprecation
     * @param ?non-empty-string                                                           $specificDeprecationToStopOn
     * @param non-negative-int                                                            $stopOnError
     * @param non-negative-int                                                            $stopOnFailure
     * @param non-negative-int                                                            $stopOnIncomplete
     * @param non-negative-int                                                            $stopOnNotice
     * @param non-negative-int                                                            $stopOnRisky
     * @param non-negative-int                                                            $stopOnSkipped
     * @param non-negative-int                                                            $stopOnWarning
     * @param positive-int                                                                $columns
     * @param ?non-empty-string                                                           $pharExtensionDirectory
     * @param list<array{className: non-empty-string, parameters: array<string, string>}> $extensionBootstrappers
     * @param non-negative-int                                                            $defaultTimeLimit
     * @param positive-int                                                                $diffContext
     * @param positive-int                                                                $timeoutForSmallTests
     * @param positive-int                                                                $timeoutForMediumTests
     * @param positive-int                                                                $timeoutForLargeTests
     * @param ?non-empty-string                                                           $logfileTeamcity
     * @param ?non-empty-string                                                           $logfileJunit
     * @param ?non-empty-string                                                           $logfileOtr
     * @param ?non-empty-string                                                           $logfileTestdoxHtml
     * @param ?non-empty-string                                                           $logfileTestdoxText
     * @param ?non-empty-string                                                           $logEventsText
     * @param ?non-empty-string                                                           $logEventsVerboseText
     * @param ?non-empty-list<non-empty-string>                                           $testsCovering
     * @param ?non-empty-list<non-empty-string>                                           $testsUsing
     * @param ?non-empty-list<non-empty-string>                                           $testsRequiringPhpExtension
     * @param ?non-empty-string                                                           $testIdFilterFile
     * @param ?non-empty-string                                                           $testIdFilter
     * @param list<non-empty-string>                                                      $groups
     * @param list<non-empty-string>                                                      $excludeGroups
     * @param positive-int                                                                $randomOrderSeed
     * @param positive-int                                                                $repeat
     * @param ?non-empty-string                                                           $defaultTestSuite
     * @param non-empty-list<non-empty-string>                                            $testSuffixes
     * @param positive-int                                                                $numberOfTestsBeforeGarbageCollection
     * @param null|non-empty-string                                                       $generateBaseline
     * @param non-negative-int                                                            $shortenArraysForExportThreshold
     */
    public function __construct(array $cliArguments, ?string $testFilesFile, ?string $configurationFile, ?string $bootstrap, array $bootstrapForTestSuite, bool $cacheResult, ?string $cacheDirectory, ?string $coverageCacheDirectory, Source $source, string $testResultCacheFile, ?string $coverageClover, ?string $coverageCobertura, ?string $coverageCrap4j, int $coverageCrap4jThreshold, ?string $coverageHtml, int $coverageHtmlLowUpperBound, int $coverageHtmlHighLowerBound, string $coverageHtmlColorSuccessLow, string $coverageHtmlColorSuccessLowDark, string $coverageHtmlColorSuccessMedium, string $coverageHtmlColorSuccessMediumDark, string $coverageHtmlColorSuccessHigh, string $coverageHtmlColorSuccessHighDark, string $coverageHtmlColorSuccessBar, string $coverageHtmlColorSuccessBarDark, string $coverageHtmlColorWarning, string $coverageHtmlColorWarningDark, string $coverageHtmlColorWarningBar, string $coverageHtmlColorWarningBarDark, string $coverageHtmlColorDanger, string $coverageHtmlColorDangerDark, string $coverageHtmlColorDangerBar, string $coverageHtmlColorDangerBarDark, string $coverageHtmlColorBreadcrumbs, string $coverageHtmlColorBreadcrumbsDark, ?string $coverageHtmlCustomCssFile, ?string $coverageOpenClover, ?string $coveragePhp, ?string $coverageText, bool $coverageTextShowUncoveredFiles, bool $coverageTextShowOnlySummary, ?string $coverageXml, bool $coverageXmlIncludeSource, bool $pathCoverage, bool $ignoreDeprecatedCodeUnitsFromCodeCoverage, bool $disableCodeCoverageIgnore, bool $failOnAllIssues, bool $failOnDeprecation, bool $failOnPhpunitDeprecation, bool $failOnPhpunitNotice, bool $failOnPhpunitWarning, bool $failOnEmptyTestSuite, bool $failOnIncomplete, bool $failOnNotice, bool $failOnRisky, bool $failOnSkipped, bool $failOnWarning, bool $doNotFailOnDeprecation, bool $doNotFailOnPhpunitDeprecation, bool $doNotFailOnPhpunitNotice, bool $doNotFailOnPhpunitWarning, bool $doNotFailOnEmptyTestSuite, bool $doNotFailOnIncomplete, bool $doNotFailOnNotice, bool $doNotFailOnRisky, bool $doNotFailOnSkipped, bool $doNotFailOnWarning, int $stopOnDefect, int $stopOnDeprecation, ?string $specificDeprecationToStopOn, int $stopOnError, int $stopOnFailure, int $stopOnIncomplete, int $stopOnNotice, int $stopOnRisky, int $stopOnSkipped, int $stopOnWarning, bool $outputToStandardErrorStream, int $columns, bool $noExtensions, ?string $pharExtensionDirectory, array $extensionBootstrappers, bool $backupGlobals, bool $backupStaticProperties, bool $beStrictAboutChangesToGlobalState, bool $colors, bool $processIsolation, bool $enforceTimeLimit, int $defaultTimeLimit, int $diffContext, int $timeoutForSmallTests, int $timeoutForMediumTests, int $timeoutForLargeTests, bool $reportUselessTests, bool $strictCoverage, bool $requireCoverageContribution, bool $disallowTestOutput, bool $displayDetailsOnAllIssues, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $displayDetailsOnTestsThatTriggerDeprecations, bool $displayDetailsOnPhpunitDeprecations, bool $displayDetailsOnPhpunitNotices, bool $displayDetailsOnTestsThatTriggerErrors, bool $displayDetailsOnTestsThatTriggerNotices, bool $displayDetailsOnTestsThatTriggerWarnings, bool $reverseDefectList, bool $requireCoverageMetadata, bool $requireSealedMockObjects, bool $noProgress, bool $noResults, bool $noOutput, int $executionOrder, int $executionOrderDefects, bool $resolveDependencies, ?string $logfileTeamcity, ?string $logfileJunit, ?string $logfileOtr, bool $includeGitInformation, bool $includeGitInformationInOtrLogfile, ?string $logfileTestdoxHtml, ?string $logfileTestdoxText, ?string $logEventsText, ?string $logEventsVerboseText, bool $teamCityOutput, bool $testDoxOutput, bool $testDoxOutputSummary, ?array $testsCovering, ?array $testsUsing, ?array $testsRequiringPhpExtension, ?string $filter, ?string $excludeFilter, ?string $testIdFilterFile, ?string $testIdFilter, array $groups, array $excludeGroups, int $randomOrderSeed, int $repeat, bool $includeUncoveredFiles, TestSuiteCollection $testSuite, string $includeTestSuite, string $excludeTestSuite, ?string $defaultTestSuite, bool $ignoreTestSelectionInXmlConfiguration, array $testSuffixes, Php $php, bool $controlGarbageCollector, int $numberOfTestsBeforeGarbageCollection, ?string $generateBaseline, bool $debug, bool $withTelemetry, int $shortenArraysForExportThreshold)
    {
        $this->cliArguments                                 = $cliArguments;
        $this->testFilesFile                                = $testFilesFile;
        $this->configurationFile                            = $configurationFile;
        $this->bootstrap                                    = $bootstrap;
        $this->bootstrapForTestSuite                        = $bootstrapForTestSuite;
        $this->cacheResult                                  = $cacheResult;
        $this->cacheDirectory                               = $cacheDirectory;
        $this->coverageCacheDirectory                       = $coverageCacheDirectory;
        $this->source                                       = $source;
        $this->testResultCacheFile                          = $testResultCacheFile;
        $this->coverageClover                               = $coverageClover;
        $this->coverageCobertura                            = $coverageCobertura;
        $this->coverageCrap4j                               = $coverageCrap4j;
        $this->coverageCrap4jThreshold                      = $coverageCrap4jThreshold;
        $this->coverageHtml                                 = $coverageHtml;
        $this->coverageHtmlLowUpperBound                    = $coverageHtmlLowUpperBound;
        $this->coverageHtmlHighLowerBound                   = $coverageHtmlHighLowerBound;
        $this->coverageHtmlColorSuccessLow                  = $coverageHtmlColorSuccessLow;
        $this->coverageHtmlColorSuccessLowDark              = $coverageHtmlColorSuccessLowDark;
        $this->coverageHtmlColorSuccessMedium               = $coverageHtmlColorSuccessMedium;
        $this->coverageHtmlColorSuccessMediumDark           = $coverageHtmlColorSuccessMediumDark;
        $this->coverageHtmlColorSuccessHigh                 = $coverageHtmlColorSuccessHigh;
        $this->coverageHtmlColorSuccessHighDark             = $coverageHtmlColorSuccessHighDark;
        $this->coverageHtmlColorSuccessBar                  = $coverageHtmlColorSuccessBar;
        $this->coverageHtmlColorSuccessBarDark              = $coverageHtmlColorSuccessBarDark;
        $this->coverageHtmlColorWarning                     = $coverageHtmlColorWarning;
        $this->coverageHtmlColorWarningDark                 = $coverageHtmlColorWarningDark;
        $this->coverageHtmlColorWarningBar                  = $coverageHtmlColorWarningBar;
        $this->coverageHtmlColorWarningBarDark              = $coverageHtmlColorWarningBarDark;
        $this->coverageHtmlColorDanger                      = $coverageHtmlColorDanger;
        $this->coverageHtmlColorDangerDark                  = $coverageHtmlColorDangerDark;
        $this->coverageHtmlColorDangerBar                   = $coverageHtmlColorDangerBar;
        $this->coverageHtmlColorDangerBarDark               = $coverageHtmlColorDangerBarDark;
        $this->coverageHtmlColorBreadcrumbs                 = $coverageHtmlColorBreadcrumbs;
        $this->coverageHtmlColorBreadcrumbsDark             = $coverageHtmlColorBreadcrumbsDark;
        $this->coverageHtmlCustomCssFile                    = $coverageHtmlCustomCssFile;
        $this->coverageOpenClover                           = $coverageOpenClover;
        $this->coveragePhp                                  = $coveragePhp;
        $this->coverageText                                 = $coverageText;
        $this->coverageTextShowUncoveredFiles               = $coverageTextShowUncoveredFiles;
        $this->coverageTextShowOnlySummary                  = $coverageTextShowOnlySummary;
        $this->coverageXml                                  = $coverageXml;
        $this->coverageXmlIncludeSource                     = $coverageXmlIncludeSource;
        $this->pathCoverage                                 = $pathCoverage;
        $this->ignoreDeprecatedCodeUnitsFromCodeCoverage    = $ignoreDeprecatedCodeUnitsFromCodeCoverage;
        $this->disableCodeCoverageIgnore                    = $disableCodeCoverageIgnore;
        $this->failOnAllIssues                              = $failOnAllIssues;
        $this->failOnDeprecation                            = $failOnDeprecation;
        $this->failOnPhpunitDeprecation                     = $failOnPhpunitDeprecation;
        $this->failOnPhpunitNotice                          = $failOnPhpunitNotice;
        $this->failOnPhpunitWarning                         = $failOnPhpunitWarning;
        $this->failOnEmptyTestSuite                         = $failOnEmptyTestSuite;
        $this->failOnIncomplete                             = $failOnIncomplete;
        $this->failOnNotice                                 = $failOnNotice;
        $this->failOnRisky                                  = $failOnRisky;
        $this->failOnSkipped                                = $failOnSkipped;
        $this->failOnWarning                                = $failOnWarning;
        $this->doNotFailOnDeprecation                       = $doNotFailOnDeprecation;
        $this->doNotFailOnPhpunitDeprecation                = $doNotFailOnPhpunitDeprecation;
        $this->doNotFailOnPhpunitNotice                     = $doNotFailOnPhpunitNotice;
        $this->doNotFailOnPhpunitWarning                    = $doNotFailOnPhpunitWarning;
        $this->doNotFailOnEmptyTestSuite                    = $doNotFailOnEmptyTestSuite;
        $this->doNotFailOnIncomplete                        = $doNotFailOnIncomplete;
        $this->doNotFailOnNotice                            = $doNotFailOnNotice;
        $this->doNotFailOnRisky                             = $doNotFailOnRisky;
        $this->doNotFailOnSkipped                           = $doNotFailOnSkipped;
        $this->doNotFailOnWarning                           = $doNotFailOnWarning;
        $this->stopOnDefect                                 = $stopOnDefect;
        $this->stopOnDeprecation                            = $stopOnDeprecation;
        $this->specificDeprecationToStopOn                  = $specificDeprecationToStopOn;
        $this->stopOnError                                  = $stopOnError;
        $this->stopOnFailure                                = $stopOnFailure;
        $this->stopOnIncomplete                             = $stopOnIncomplete;
        $this->stopOnNotice                                 = $stopOnNotice;
        $this->stopOnRisky                                  = $stopOnRisky;
        $this->stopOnSkipped                                = $stopOnSkipped;
        $this->stopOnWarning                                = $stopOnWarning;
        $this->outputToStandardErrorStream                  = $outputToStandardErrorStream;
        $this->columns                                      = $columns;
        $this->noExtensions                                 = $noExtensions;
        $this->pharExtensionDirectory                       = $pharExtensionDirectory;
        $this->extensionBootstrappers                       = $extensionBootstrappers;
        $this->backupGlobals                                = $backupGlobals;
        $this->backupStaticProperties                       = $backupStaticProperties;
        $this->beStrictAboutChangesToGlobalState            = $beStrictAboutChangesToGlobalState;
        $this->colors                                       = $colors;
        $this->processIsolation                             = $processIsolation;
        $this->enforceTimeLimit                             = $enforceTimeLimit;
        $this->defaultTimeLimit                             = $defaultTimeLimit;
        $this->diffContext                                  = $diffContext;
        $this->timeoutForSmallTests                         = $timeoutForSmallTests;
        $this->timeoutForMediumTests                        = $timeoutForMediumTests;
        $this->timeoutForLargeTests                         = $timeoutForLargeTests;
        $this->reportUselessTests                           = $reportUselessTests;
        $this->strictCoverage                               = $strictCoverage;
        $this->requireCoverageContribution                  = $requireCoverageContribution;
        $this->disallowTestOutput                           = $disallowTestOutput;
        $this->displayDetailsOnAllIssues                    = $displayDetailsOnAllIssues;
        $this->displayDetailsOnIncompleteTests              = $displayDetailsOnIncompleteTests;
        $this->displayDetailsOnSkippedTests                 = $displayDetailsOnSkippedTests;
        $this->displayDetailsOnTestsThatTriggerDeprecations = $displayDetailsOnTestsThatTriggerDeprecations;
        $this->displayDetailsOnPhpunitDeprecations          = $displayDetailsOnPhpunitDeprecations;
        $this->displayDetailsOnPhpunitNotices               = $displayDetailsOnPhpunitNotices;
        $this->displayDetailsOnTestsThatTriggerErrors       = $displayDetailsOnTestsThatTriggerErrors;
        $this->displayDetailsOnTestsThatTriggerNotices      = $displayDetailsOnTestsThatTriggerNotices;
        $this->displayDetailsOnTestsThatTriggerWarnings     = $displayDetailsOnTestsThatTriggerWarnings;
        $this->reverseDefectList                            = $reverseDefectList;
        $this->requireCoverageMetadata                      = $requireCoverageMetadata;
        $this->requireSealedMockObjects                     = $requireSealedMockObjects;
        $this->noProgress                                   = $noProgress;
        $this->noResults                                    = $noResults;
        $this->noOutput                                     = $noOutput;
        $this->executionOrder                               = $executionOrder;
        $this->executionOrderDefects                        = $executionOrderDefects;
        $this->resolveDependencies                          = $resolveDependencies;
        $this->logfileTeamcity                              = $logfileTeamcity;
        $this->logfileJunit                                 = $logfileJunit;
        $this->logfileOtr                                   = $logfileOtr;
        $this->includeGitInformation                        = $includeGitInformation;
        $this->includeGitInformationInOtrLogfile            = $includeGitInformationInOtrLogfile;
        $this->logfileTestdoxHtml                           = $logfileTestdoxHtml;
        $this->logfileTestdoxText                           = $logfileTestdoxText;
        $this->logEventsText                                = $logEventsText;
        $this->logEventsVerboseText                         = $logEventsVerboseText;
        $this->teamCityOutput                               = $teamCityOutput;
        $this->testDoxOutput                                = $testDoxOutput;
        $this->testDoxOutputSummary                         = $testDoxOutputSummary;
        $this->testsCovering                                = $testsCovering;
        $this->testsUsing                                   = $testsUsing;
        $this->testsRequiringPhpExtension                   = $testsRequiringPhpExtension;
        $this->filter                                       = $filter;
        $this->excludeFilter                                = $excludeFilter;
        $this->testIdFilterFile                             = $testIdFilterFile;
        $this->testIdFilter                                 = $testIdFilter;
        $this->groups                                       = $groups;
        $this->excludeGroups                                = $excludeGroups;
        $this->randomOrderSeed                              = $randomOrderSeed;
        $this->repeat                                       = $repeat;
        $this->includeUncoveredFiles                        = $includeUncoveredFiles;
        $this->testSuite                                    = $testSuite;
        $this->includeTestSuite                             = $includeTestSuite;
        $this->excludeTestSuite                             = $excludeTestSuite;
        $this->defaultTestSuite                             = $defaultTestSuite;
        $this->ignoreTestSelectionInXmlConfiguration        = $ignoreTestSelectionInXmlConfiguration;
        $this->testSuffixes                                 = $testSuffixes;
        $this->php                                          = $php;
        $this->controlGarbageCollector                      = $controlGarbageCollector;
        $this->numberOfTestsBeforeGarbageCollection         = $numberOfTestsBeforeGarbageCollection;
        $this->generateBaseline                             = $generateBaseline;
        $this->debug                                        = $debug;
        $this->withTelemetry                                = $withTelemetry;
        $this->shortenArraysForExportThreshold              = $shortenArraysForExportThreshold;
    }

    /**
     * @phpstan-assert-if-true !empty $this->cliArguments
     */
    public function hasCliArguments(): bool
    {
        return $this->cliArguments !== [];
    }

    /**
     * @return list<non-empty-string>
     */
    public function cliArguments(): array
    {
        return $this->cliArguments;
    }

    /**
     * @phpstan-assert-if-true !null $this->testFilesFile
     */
    public function hasTestFilesFile(): bool
    {
        return $this->testFilesFile !== null;
    }

    /**
     * @throws NoTestFilesFileException
     *
     * @return non-empty-string
     */
    public function testFilesFile(): string
    {
        if (!$this->hasTestFilesFile()) {
            throw new NoTestFilesFileException;
        }

        return $this->testFilesFile;
    }

    /**
     * @phpstan-assert-if-true !null $this->configurationFile
     */
    public function hasConfigurationFile(): bool
    {
        return $this->configurationFile !== null;
    }

    /**
     * @throws NoConfigurationFileException
     *
     * @return non-empty-string
     */
    public function configurationFile(): string
    {
        if (!$this->hasConfigurationFile()) {
            throw new NoConfigurationFileException;
        }

        return $this->configurationFile;
    }

    /**
     * @phpstan-assert-if-true !null $this->bootstrap
     */
    public function hasBootstrap(): bool
    {
        return $this->bootstrap !== null;
    }

    /**
     * @throws NoBootstrapException
     *
     * @return non-empty-string
     */
    public function bootstrap(): string
    {
        if (!$this->hasBootstrap()) {
            throw new NoBootstrapException;
        }

        return $this->bootstrap;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function bootstrapForTestSuite(): array
    {
        return $this->bootstrapForTestSuite;
    }

    public function cacheResult(): bool
    {
        return $this->cacheResult;
    }

    /**
     * @phpstan-assert-if-true !null $this->cacheDirectory
     */
    public function hasCacheDirectory(): bool
    {
        return $this->cacheDirectory !== null;
    }

    /**
     * @throws NoCacheDirectoryException
     *
     * @return non-empty-string
     */
    public function cacheDirectory(): string
    {
        if (!$this->hasCacheDirectory()) {
            throw new NoCacheDirectoryException;
        }

        return $this->cacheDirectory;
    }

    /**
     * @phpstan-assert-if-true !null $this->coverageCacheDirectory
     */
    public function hasCoverageCacheDirectory(): bool
    {
        return $this->coverageCacheDirectory !== null;
    }

    /**
     * @throws NoCoverageCacheDirectoryException
     *
     * @return non-empty-string
     */
    public function coverageCacheDirectory(): string
    {
        if (!$this->hasCoverageCacheDirectory()) {
            throw new NoCoverageCacheDirectoryException;
        }

        return $this->coverageCacheDirectory;
    }

    public function source(): Source
    {
        return $this->source;
    }

    /**
     * @return non-empty-string
     */
    public function testResultCacheFile(): string
    {
        return $this->testResultCacheFile;
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
            $this->hasCoverageOpenClover() ||
            $this->hasCoveragePhp() ||
            $this->hasCoverageText() ||
            $this->hasCoverageXml();
    }

    /**
     * @phpstan-assert-if-true !null $this->coverageClover
     */
    public function hasCoverageClover(): bool
    {
        return $this->coverageClover !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     *
     * @return non-empty-string
     */
    public function coverageClover(): string
    {
        if (!$this->hasCoverageClover()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageClover;
    }

    /**
     * @phpstan-assert-if-true !null $this->coverageCobertura
     */
    public function hasCoverageCobertura(): bool
    {
        return $this->coverageCobertura !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     *
     * @return non-empty-string
     */
    public function coverageCobertura(): string
    {
        if (!$this->hasCoverageCobertura()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageCobertura;
    }

    /**
     * @phpstan-assert-if-true !null $this->coverageCrap4j
     */
    public function hasCoverageCrap4j(): bool
    {
        return $this->coverageCrap4j !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     *
     * @return non-empty-string
     */
    public function coverageCrap4j(): string
    {
        if (!$this->hasCoverageCrap4j()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageCrap4j;
    }

    /**
     * @return non-negative-int
     */
    public function coverageCrap4jThreshold(): int
    {
        return $this->coverageCrap4jThreshold;
    }

    /**
     * @phpstan-assert-if-true !null $this->coverageHtml
     */
    public function hasCoverageHtml(): bool
    {
        return $this->coverageHtml !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     *
     * @return non-empty-string
     */
    public function coverageHtml(): string
    {
        if (!$this->hasCoverageHtml()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageHtml;
    }

    /**
     * @return non-negative-int
     */
    public function coverageHtmlLowUpperBound(): int
    {
        return $this->coverageHtmlLowUpperBound;
    }

    /**
     * @return non-negative-int
     */
    public function coverageHtmlHighLowerBound(): int
    {
        return $this->coverageHtmlHighLowerBound;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorSuccessLow(): string
    {
        return $this->coverageHtmlColorSuccessLow;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorSuccessLowDark(): string
    {
        return $this->coverageHtmlColorSuccessLowDark;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorSuccessMedium(): string
    {
        return $this->coverageHtmlColorSuccessMedium;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorSuccessMediumDark(): string
    {
        return $this->coverageHtmlColorSuccessMediumDark;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorSuccessHigh(): string
    {
        return $this->coverageHtmlColorSuccessHigh;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorSuccessHighDark(): string
    {
        return $this->coverageHtmlColorSuccessHighDark;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorSuccessBar(): string
    {
        return $this->coverageHtmlColorSuccessBar;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorSuccessBarDark(): string
    {
        return $this->coverageHtmlColorSuccessBarDark;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorWarning(): string
    {
        return $this->coverageHtmlColorWarning;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorWarningDark(): string
    {
        return $this->coverageHtmlColorWarningDark;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorWarningBar(): string
    {
        return $this->coverageHtmlColorWarningBar;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorWarningBarDark(): string
    {
        return $this->coverageHtmlColorWarningBarDark;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorDanger(): string
    {
        return $this->coverageHtmlColorDanger;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorDangerDark(): string
    {
        return $this->coverageHtmlColorDangerDark;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorDangerBar(): string
    {
        return $this->coverageHtmlColorDangerBar;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorDangerBarDark(): string
    {
        return $this->coverageHtmlColorDangerBarDark;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorBreadcrumbs(): string
    {
        return $this->coverageHtmlColorBreadcrumbs;
    }

    /**
     * @return non-empty-string
     */
    public function coverageHtmlColorBreadcrumbsDark(): string
    {
        return $this->coverageHtmlColorBreadcrumbsDark;
    }

    /**
     * @phpstan-assert-if-true !null $this->coverageHtmlCustomCssFile
     */
    public function hasCoverageHtmlCustomCssFile(): bool
    {
        return $this->coverageHtmlCustomCssFile !== null;
    }

    /**
     * @throws NoCustomCssFileException
     *
     * @return non-empty-string
     */
    public function coverageHtmlCustomCssFile(): string
    {
        if (!$this->hasCoverageHtmlCustomCssFile()) {
            throw new NoCustomCssFileException;
        }

        return $this->coverageHtmlCustomCssFile;
    }

    /**
     * @phpstan-assert-if-true !null $this->coverageOpenClover
     */
    public function hasCoverageOpenClover(): bool
    {
        return $this->coverageOpenClover !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     *
     * @return non-empty-string
     */
    public function coverageOpenClover(): string
    {
        if (!$this->hasCoverageOpenClover()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageOpenClover;
    }

    /**
     * @phpstan-assert-if-true !null $this->coveragePhp
     */
    public function hasCoveragePhp(): bool
    {
        return $this->coveragePhp !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     *
     * @return non-empty-string
     */
    public function coveragePhp(): string
    {
        if (!$this->hasCoveragePhp()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coveragePhp;
    }

    /**
     * @phpstan-assert-if-true !null $this->coverageText
     */
    public function hasCoverageText(): bool
    {
        return $this->coverageText !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     *
     * @return non-empty-string
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
     * @phpstan-assert-if-true !null $this->coverageXml
     */
    public function hasCoverageXml(): bool
    {
        return $this->coverageXml !== null;
    }

    /**
     * @throws CodeCoverageReportNotConfiguredException
     *
     * @return non-empty-string
     */
    public function coverageXml(): string
    {
        if (!$this->hasCoverageXml()) {
            throw new CodeCoverageReportNotConfiguredException;
        }

        return $this->coverageXml;
    }

    public function coverageXmlIncludeSource(): bool
    {
        return $this->coverageXmlIncludeSource;
    }

    public function failOnAllIssues(): bool
    {
        return $this->failOnAllIssues;
    }

    public function failOnDeprecation(): bool
    {
        return $this->failOnDeprecation;
    }

    public function failOnPhpunitDeprecation(): bool
    {
        return $this->failOnPhpunitDeprecation;
    }

    public function failOnPhpunitNotice(): bool
    {
        return $this->failOnPhpunitNotice;
    }

    public function failOnPhpunitWarning(): bool
    {
        return $this->failOnPhpunitWarning;
    }

    public function failOnEmptyTestSuite(): bool
    {
        return $this->failOnEmptyTestSuite;
    }

    public function failOnIncomplete(): bool
    {
        return $this->failOnIncomplete;
    }

    public function failOnNotice(): bool
    {
        return $this->failOnNotice;
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

    public function doNotFailOnDeprecation(): bool
    {
        return $this->doNotFailOnDeprecation;
    }

    public function doNotFailOnPhpunitDeprecation(): bool
    {
        return $this->doNotFailOnPhpunitDeprecation;
    }

    public function doNotFailOnPhpunitNotice(): bool
    {
        return $this->doNotFailOnPhpunitNotice;
    }

    public function doNotFailOnPhpunitWarning(): bool
    {
        return $this->doNotFailOnPhpunitWarning;
    }

    public function doNotFailOnEmptyTestSuite(): bool
    {
        return $this->doNotFailOnEmptyTestSuite;
    }

    public function doNotFailOnIncomplete(): bool
    {
        return $this->doNotFailOnIncomplete;
    }

    public function doNotFailOnNotice(): bool
    {
        return $this->doNotFailOnNotice;
    }

    public function doNotFailOnRisky(): bool
    {
        return $this->doNotFailOnRisky;
    }

    public function doNotFailOnSkipped(): bool
    {
        return $this->doNotFailOnSkipped;
    }

    public function doNotFailOnWarning(): bool
    {
        return $this->doNotFailOnWarning;
    }

    /**
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function stopOnDefect(): bool
    {
        return $this->stopOnDefect > 0;
    }

    /**
     * @return non-negative-int
     */
    public function stopOnDefectThreshold(): int
    {
        return $this->stopOnDefect;
    }

    /**
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function stopOnDeprecation(): bool
    {
        return $this->stopOnDeprecation > 0;
    }

    /**
     * @return non-negative-int
     */
    public function stopOnDeprecationThreshold(): int
    {
        return $this->stopOnDeprecation;
    }

    /**
     * @phpstan-assert-if-true !null $this->specificDeprecationToStopOn
     */
    public function hasSpecificDeprecationToStopOn(): bool
    {
        return $this->specificDeprecationToStopOn !== null;
    }

    /**
     * @throws SpecificDeprecationToStopOnNotConfiguredException
     *
     * @return non-empty-string
     */
    public function specificDeprecationToStopOn(): string
    {
        if (!$this->hasSpecificDeprecationToStopOn()) {
            throw new SpecificDeprecationToStopOnNotConfiguredException;
        }

        return $this->specificDeprecationToStopOn;
    }

    /**
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function stopOnError(): bool
    {
        return $this->stopOnError > 0;
    }

    /**
     * @return non-negative-int
     */
    public function stopOnErrorThreshold(): int
    {
        return $this->stopOnError;
    }

    /**
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function stopOnFailure(): bool
    {
        return $this->stopOnFailure > 0;
    }

    /**
     * @return non-negative-int
     */
    public function stopOnFailureThreshold(): int
    {
        return $this->stopOnFailure;
    }

    /**
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function stopOnIncomplete(): bool
    {
        return $this->stopOnIncomplete > 0;
    }

    /**
     * @return non-negative-int
     */
    public function stopOnIncompleteThreshold(): int
    {
        return $this->stopOnIncomplete;
    }

    /**
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function stopOnNotice(): bool
    {
        return $this->stopOnNotice > 0;
    }

    /**
     * @return non-negative-int
     */
    public function stopOnNoticeThreshold(): int
    {
        return $this->stopOnNotice;
    }

    /**
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function stopOnRisky(): bool
    {
        return $this->stopOnRisky > 0;
    }

    /**
     * @return non-negative-int
     */
    public function stopOnRiskyThreshold(): int
    {
        return $this->stopOnRisky;
    }

    /**
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function stopOnSkipped(): bool
    {
        return $this->stopOnSkipped > 0;
    }

    /**
     * @return non-negative-int
     */
    public function stopOnSkippedThreshold(): int
    {
        return $this->stopOnSkipped;
    }

    /**
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function stopOnWarning(): bool
    {
        return $this->stopOnWarning > 0;
    }

    /**
     * @return non-negative-int
     */
    public function stopOnWarningThreshold(): int
    {
        return $this->stopOnWarning;
    }

    public function outputToStandardErrorStream(): bool
    {
        return $this->outputToStandardErrorStream;
    }

    /**
     * @return positive-int
     */
    public function columns(): int
    {
        return $this->columns;
    }

    public function noExtensions(): bool
    {
        return $this->noExtensions;
    }

    /**
     * @phpstan-assert-if-true !null $this->pharExtensionDirectory
     */
    public function hasPharExtensionDirectory(): bool
    {
        return $this->pharExtensionDirectory !== null;
    }

    /**
     * @throws NoPharExtensionDirectoryException
     *
     * @return non-empty-string
     */
    public function pharExtensionDirectory(): string
    {
        if (!$this->hasPharExtensionDirectory()) {
            throw new NoPharExtensionDirectoryException;
        }

        return $this->pharExtensionDirectory;
    }

    /**
     * @return list<array{className: non-empty-string, parameters: array<string, string>}>
     */
    public function extensionBootstrappers(): array
    {
        return $this->extensionBootstrappers;
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

    public function processIsolation(): bool
    {
        return $this->processIsolation;
    }

    public function enforceTimeLimit(): bool
    {
        return $this->enforceTimeLimit;
    }

    /**
     * @return non-negative-int
     */
    public function defaultTimeLimit(): int
    {
        return $this->defaultTimeLimit;
    }

    /**
     * @return positive-int
     */
    public function diffContext(): int
    {
        return $this->diffContext;
    }

    /**
     * @return positive-int
     */
    public function timeoutForSmallTests(): int
    {
        return $this->timeoutForSmallTests;
    }

    /**
     * @return positive-int
     */
    public function timeoutForMediumTests(): int
    {
        return $this->timeoutForMediumTests;
    }

    /**
     * @return positive-int
     */
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

    public function requireCoverageContribution(): bool
    {
        return $this->requireCoverageContribution;
    }

    public function disallowTestOutput(): bool
    {
        return $this->disallowTestOutput;
    }

    public function displayDetailsOnAllIssues(): bool
    {
        return $this->displayDetailsOnAllIssues;
    }

    public function displayDetailsOnIncompleteTests(): bool
    {
        return $this->displayDetailsOnIncompleteTests;
    }

    public function displayDetailsOnSkippedTests(): bool
    {
        return $this->displayDetailsOnSkippedTests;
    }

    public function displayDetailsOnTestsThatTriggerDeprecations(): bool
    {
        return $this->displayDetailsOnTestsThatTriggerDeprecations;
    }

    public function displayDetailsOnPhpunitDeprecations(): bool
    {
        return $this->displayDetailsOnPhpunitDeprecations;
    }

    public function displayDetailsOnPhpunitNotices(): bool
    {
        return $this->displayDetailsOnPhpunitNotices;
    }

    public function displayDetailsOnTestsThatTriggerErrors(): bool
    {
        return $this->displayDetailsOnTestsThatTriggerErrors;
    }

    public function displayDetailsOnTestsThatTriggerNotices(): bool
    {
        return $this->displayDetailsOnTestsThatTriggerNotices;
    }

    public function displayDetailsOnTestsThatTriggerWarnings(): bool
    {
        return $this->displayDetailsOnTestsThatTriggerWarnings;
    }

    public function reverseDefectList(): bool
    {
        return $this->reverseDefectList;
    }

    public function requireCoverageMetadata(): bool
    {
        return $this->requireCoverageMetadata;
    }

    public function requireSealedMockObjects(): bool
    {
        return $this->requireSealedMockObjects;
    }

    public function noProgress(): bool
    {
        return $this->noProgress;
    }

    public function noResults(): bool
    {
        return $this->noResults;
    }

    public function noOutput(): bool
    {
        return $this->noOutput;
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
     * @phpstan-assert-if-true !null $this->logfileTeamcity
     */
    public function hasLogfileTeamcity(): bool
    {
        return $this->logfileTeamcity !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     *
     * @return non-empty-string
     */
    public function logfileTeamcity(): string
    {
        if (!$this->hasLogfileTeamcity()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileTeamcity;
    }

    /**
     * @phpstan-assert-if-true !null $this->logfileJunit
     */
    public function hasLogfileJunit(): bool
    {
        return $this->logfileJunit !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     *
     * @return non-empty-string
     */
    public function logfileJunit(): string
    {
        if (!$this->hasLogfileJunit()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileJunit;
    }

    /**
     * @phpstan-assert-if-true !null $this->logfileOtr
     */
    public function hasLogfileOtr(): bool
    {
        return $this->logfileOtr !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     *
     * @return non-empty-string
     */
    public function logfileOtr(): string
    {
        if (!$this->hasLogfileOtr()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileOtr;
    }

    public function includeGitInformationInOtrLogfile(): bool
    {
        return $this->includeGitInformationInOtrLogfile;
    }

    public function includeGitInformation(): bool
    {
        return $this->includeGitInformation;
    }

    /**
     * @phpstan-assert-if-true !null $this->logfileTestdoxHtml
     */
    public function hasLogfileTestdoxHtml(): bool
    {
        return $this->logfileTestdoxHtml !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     *
     * @return non-empty-string
     */
    public function logfileTestdoxHtml(): string
    {
        if (!$this->hasLogfileTestdoxHtml()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileTestdoxHtml;
    }

    /**
     * @phpstan-assert-if-true !null $this->logfileTestdoxText
     */
    public function hasLogfileTestdoxText(): bool
    {
        return $this->logfileTestdoxText !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     *
     * @return non-empty-string
     */
    public function logfileTestdoxText(): string
    {
        if (!$this->hasLogfileTestdoxText()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logfileTestdoxText;
    }

    /**
     * @phpstan-assert-if-true !null $this->logEventsText
     */
    public function hasLogEventsText(): bool
    {
        return $this->logEventsText !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     *
     * @return non-empty-string
     */
    public function logEventsText(): string
    {
        if (!$this->hasLogEventsText()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logEventsText;
    }

    /**
     * @phpstan-assert-if-true !null $this->logEventsVerboseText
     */
    public function hasLogEventsVerboseText(): bool
    {
        return $this->logEventsVerboseText !== null;
    }

    /**
     * @throws LoggingNotConfiguredException
     *
     * @return non-empty-string
     */
    public function logEventsVerboseText(): string
    {
        if (!$this->hasLogEventsVerboseText()) {
            throw new LoggingNotConfiguredException;
        }

        return $this->logEventsVerboseText;
    }

    public function outputIsTeamCity(): bool
    {
        return $this->teamCityOutput;
    }

    public function outputIsTestDox(): bool
    {
        return $this->testDoxOutput;
    }

    public function testDoxOutputWithSummary(): bool
    {
        return $this->testDoxOutputSummary;
    }

    /**
     * @phpstan-assert-if-true !empty $this->testsCovering
     */
    public function hasTestsCovering(): bool
    {
        return $this->testsCovering !== null;
    }

    /**
     * @throws FilterNotConfiguredException
     *
     * @return list<string>
     */
    public function testsCovering(): array
    {
        if (!$this->hasTestsCovering()) {
            throw new FilterNotConfiguredException;
        }

        return $this->testsCovering;
    }

    /**
     * @phpstan-assert-if-true !empty $this->testsUsing
     */
    public function hasTestsUsing(): bool
    {
        return $this->testsUsing !== null;
    }

    /**
     * @throws FilterNotConfiguredException
     *
     * @return list<string>
     */
    public function testsUsing(): array
    {
        if (!$this->hasTestsUsing()) {
            throw new FilterNotConfiguredException;
        }

        return $this->testsUsing;
    }

    /**
     * @phpstan-assert-if-true !empty $this->testsRequiringPhpExtension
     */
    public function hasTestsRequiringPhpExtension(): bool
    {
        return $this->testsRequiringPhpExtension !== null;
    }

    /**
     * @throws FilterNotConfiguredException
     *
     * @return non-empty-list<non-empty-string>
     */
    public function testsRequiringPhpExtension(): array
    {
        if (!$this->hasTestsRequiringPhpExtension()) {
            throw new FilterNotConfiguredException;
        }

        return $this->testsRequiringPhpExtension;
    }

    /**
     * @phpstan-assert-if-true !null $this->filter
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
     * @phpstan-assert-if-true !null $this->excludeFilter
     */
    public function hasExcludeFilter(): bool
    {
        return $this->excludeFilter !== null;
    }

    /**
     * @throws FilterNotConfiguredException
     */
    public function excludeFilter(): string
    {
        if (!$this->hasExcludeFilter()) {
            throw new FilterNotConfiguredException;
        }

        return $this->excludeFilter;
    }

    /**
     * @phpstan-assert-if-true !null $this->testIdFilterFile
     */
    public function hasTestIdFilterFile(): bool
    {
        return $this->testIdFilterFile !== null;
    }

    /**
     * @throws FilterNotConfiguredException
     *
     * @return non-empty-string
     */
    public function testIdFilterFile(): string
    {
        if (!$this->hasTestIdFilterFile()) {
            throw new FilterNotConfiguredException;
        }

        return $this->testIdFilterFile;
    }

    /**
     * @phpstan-assert-if-true !null $this->testIdFilter
     */
    public function hasTestIdFilter(): bool
    {
        return $this->testIdFilter !== null;
    }

    /**
     * @throws FilterNotConfiguredException
     *
     * @return non-empty-string
     */
    public function testIdFilter(): string
    {
        if (!$this->hasTestIdFilter()) {
            throw new FilterNotConfiguredException;
        }

        return $this->testIdFilter;
    }

    /**
     * @phpstan-assert-if-true !empty $this->groups
     */
    public function hasGroups(): bool
    {
        return $this->groups !== [];
    }

    /**
     * @throws FilterNotConfiguredException
     *
     * @return non-empty-list<non-empty-string>
     */
    public function groups(): array
    {
        if (!$this->hasGroups()) {
            throw new FilterNotConfiguredException;
        }

        return $this->groups;
    }

    /**
     * @phpstan-assert-if-true !empty $this->excludeGroups
     */
    public function hasExcludeGroups(): bool
    {
        return $this->excludeGroups !== [];
    }

    /**
     * @throws FilterNotConfiguredException
     *
     * @return non-empty-list<non-empty-string>
     */
    public function excludeGroups(): array
    {
        if (!$this->hasExcludeGroups()) {
            throw new FilterNotConfiguredException;
        }

        return $this->excludeGroups;
    }

    /**
     * @return positive-int
     */
    public function randomOrderSeed(): int
    {
        return $this->randomOrderSeed;
    }

    /**
     * @return positive-int
     */
    public function repeat(): int
    {
        return $this->repeat;
    }

    public function includeUncoveredFiles(): bool
    {
        return $this->includeUncoveredFiles;
    }

    public function testSuite(): TestSuiteCollection
    {
        return $this->testSuite;
    }

    /**
     * @return list<non-empty-string>
     */
    public function includeTestSuites(): array
    {
        if ($this->includeTestSuite === '') {
            return [];
        }

        return explode(',', $this->includeTestSuite);
    }

    /**
     * @return list<non-empty-string>
     */
    public function excludeTestSuites(): array
    {
        if ($this->excludeTestSuite === '') {
            return [];
        }

        return explode(',', $this->excludeTestSuite);
    }

    /**
     * @phpstan-assert-if-true !null $this->defaultTestSuite
     */
    public function hasDefaultTestSuite(): bool
    {
        return $this->defaultTestSuite !== null;
    }

    /**
     * @throws NoDefaultTestSuiteException
     *
     * @return non-empty-string
     */
    public function defaultTestSuite(): string
    {
        if (!$this->hasDefaultTestSuite()) {
            throw new NoDefaultTestSuiteException;
        }

        return $this->defaultTestSuite;
    }

    public function ignoreTestSelectionInXmlConfiguration(): bool
    {
        return $this->ignoreTestSelectionInXmlConfiguration;
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function testSuffixes(): array
    {
        return $this->testSuffixes;
    }

    public function php(): Php
    {
        return $this->php;
    }

    public function controlGarbageCollector(): bool
    {
        return $this->controlGarbageCollector;
    }

    /**
     * @return positive-int
     */
    public function numberOfTestsBeforeGarbageCollection(): int
    {
        return $this->numberOfTestsBeforeGarbageCollection;
    }

    /**
     * @phpstan-assert-if-true !null $this->generateBaseline
     */
    public function hasGenerateBaseline(): bool
    {
        return $this->generateBaseline !== null;
    }

    /**
     * @throws NoBaselineException
     *
     * @return non-empty-string
     */
    public function generateBaseline(): string
    {
        if (!$this->hasGenerateBaseline()) {
            throw new NoBaselineException;
        }

        return $this->generateBaseline;
    }

    public function debug(): bool
    {
        return $this->debug;
    }

    public function withTelemetry(): bool
    {
        return $this->withTelemetry;
    }

    /**
     * @return non-negative-int
     */
    public function shortenArraysForExportThreshold(): int
    {
        return $this->shortenArraysForExportThreshold;
    }
}
