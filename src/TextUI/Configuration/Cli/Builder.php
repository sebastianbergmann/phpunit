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

use function array_map;
use function array_merge;
use function explode;
use function is_numeric;
use PHPUnit\Runner\TestSuiteSorter;
use SebastianBergmann\CliParser\Exception as CliParserException;
use SebastianBergmann\CliParser\Parser as CliParser;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Builder
{
    private const LONG_OPTIONS = [
        'atleast-version=',
        'bootstrap=',
        'cache-result',
        'do-not-cache-result',
        'cache-directory=',
        'cache-result-file=',
        'check-version',
        'colors==',
        'columns=',
        'configuration=',
        'coverage-cache=',
        'warm-coverage-cache',
        'coverage-filter=',
        'coverage-clover=',
        'coverage-cobertura=',
        'coverage-crap4j=',
        'coverage-html=',
        'coverage-php=',
        'coverage-text==',
        'coverage-xml=',
        'path-coverage',
        'disallow-test-output',
        'default-time-limit=',
        'enforce-time-limit',
        'exclude-group=',
        'filter=',
        'generate-configuration',
        'globals-backup',
        'group=',
        'covers=',
        'uses=',
        'help',
        'resolve-dependencies',
        'ignore-dependencies',
        'include-path=',
        'list-groups',
        'list-suites',
        'list-tests',
        'list-tests-xml=',
        'log-junit=',
        'log-teamcity=',
        'migrate-configuration',
        'no-configuration',
        'no-coverage',
        'no-logging',
        'no-interaction',
        'no-extensions',
        'no-output',
        'order-by=',
        'process-isolation',
        'repeat=',
        'dont-report-useless-tests',
        'random-order',
        'random-order-seed=',
        'reverse-order',
        'reverse-list',
        'static-backup',
        'stderr',
        'stop-on-defect',
        'stop-on-error',
        'stop-on-failure',
        'stop-on-warning',
        'stop-on-incomplete',
        'stop-on-risky',
        'stop-on-skipped',
        'fail-on-empty-test-suite',
        'fail-on-incomplete',
        'fail-on-risky',
        'fail-on-skipped',
        'fail-on-warning',
        'strict-coverage',
        'disable-coverage-ignore',
        'strict-global-state',
        'teamcity',
        'testdox',
        'testdox-group=',
        'testdox-exclude-group=',
        'testdox-html=',
        'testdox-text=',
        'testdox-xml=',
        'test-suffix=',
        'testsuite=',
        'exclude-testsuite=',
        'trace-text=',
        'verbose',
        'version',
    ];

    private const SHORT_OPTIONS = 'd:c:hv';

    public function fromParameters(array $parameters, array $additionalLongOptions): Configuration
    {
        try {
            $options = (new CliParser)->parse(
                $parameters,
                self::SHORT_OPTIONS,
                array_merge(self::LONG_OPTIONS, $additionalLongOptions)
            );
        } catch (CliParserException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        $argument                          = null;
        $atLeastVersion                    = null;
        $backupGlobals                     = null;
        $backupStaticProperties            = null;
        $beStrictAboutChangesToGlobalState = null;
        $bootstrap                         = null;
        $cacheDirectory                    = null;
        $cacheResult                       = null;
        $cacheResultFile                   = null;
        $checkVersion                      = null;
        $colors                            = null;
        $columns                           = null;
        $configuration                     = null;
        $coverageCacheDirectory            = null;
        $warmCoverageCache                 = null;
        $coverageFilter                    = null;
        $coverageClover                    = null;
        $coverageCobertura                 = null;
        $coverageCrap4J                    = null;
        $coverageHtml                      = null;
        $coveragePhp                       = null;
        $coverageText                      = null;
        $coverageTextShowUncoveredFiles    = null;
        $coverageTextShowOnlySummary       = null;
        $coverageXml                       = null;
        $pathCoverage                      = null;
        $defaultTimeLimit                  = null;
        $disableCodeCoverageIgnore         = null;
        $disallowTestOutput                = null;
        $enforceTimeLimit                  = null;
        $excludeGroups                     = null;
        $executionOrder                    = null;
        $executionOrderDefects             = null;
        $failOnEmptyTestSuite              = null;
        $failOnIncomplete                  = null;
        $failOnRisky                       = null;
        $failOnSkipped                     = null;
        $failOnWarning                     = null;
        $filter                            = null;
        $generateConfiguration             = null;
        $migrateConfiguration              = null;
        $groups                            = null;
        $testsCovering                     = null;
        $testsUsing                        = null;
        $help                              = null;
        $includePath                       = null;
        $iniSettings                       = [];
        $junitLogfile                      = null;
        $listGroups                        = null;
        $listSuites                        = null;
        $listTests                         = null;
        $listTestsXml                      = null;
        $noCoverage                        = null;
        $noExtensions                      = null;
        $noInteraction                     = null;
        $noOutput                          = null;
        $noLogging                         = null;
        $processIsolation                  = null;
        $randomOrderSeed                   = null;
        $repeat                            = null;
        $reportUselessTests                = null;
        $resolveDependencies               = null;
        $reverseList                       = null;
        $stderr                            = null;
        $strictCoverage                    = null;
        $stopOnDefect                      = null;
        $stopOnError                       = null;
        $stopOnFailure                     = null;
        $stopOnIncomplete                  = null;
        $stopOnRisky                       = null;
        $stopOnSkipped                     = null;
        $stopOnWarning                     = null;
        $teamcityLogfile                   = null;
        $testdoxExcludeGroups              = null;
        $testdoxGroups                     = null;
        $testdoxHtmlFile                   = null;
        $testdoxTextFile                   = null;
        $testdoxXmlFile                    = null;
        $testSuffixes                      = null;
        $testSuite                         = null;
        $excludeTestSuite                  = null;
        $unrecognizedOrderBy               = null;
        $useDefaultConfiguration           = null;
        $verbose                           = null;
        $version                           = null;
        $plainTextTrace                    = null;
        $printerTeamCity                   = null;
        $printerTestDox                    = null;

        if (isset($options[1][0])) {
            $argument = $options[1][0];
        }

        foreach ($options[0] as $option) {
            switch ($option[0]) {
                case '--colors':
                    $colors = $option[1] ?: \PHPUnit\TextUI\Configuration\Configuration::COLOR_AUTO;

                    break;

                case '--bootstrap':
                    $bootstrap = $option[1];

                    break;

                case '--cache-directory':
                    $cacheDirectory = $option[1];

                    break;

                case '--cache-result':
                    $cacheResult = true;

                    break;

                case '--do-not-cache-result':
                    $cacheResult = false;

                    break;

                case '--cache-result-file':
                    $cacheResultFile = $option[1];

                    break;

                case '--columns':
                    if (is_numeric($option[1])) {
                        $columns = (int) $option[1];
                    } elseif ($option[1] === 'max') {
                        $columns = 'max';
                    }

                    break;

                case 'c':
                case '--configuration':
                    $configuration = $option[1];

                    break;

                case '--coverage-cache':
                    $coverageCacheDirectory = $option[1];

                    break;

                case '--warm-coverage-cache':
                    $warmCoverageCache = true;

                    break;

                case '--coverage-clover':
                    $coverageClover = $option[1];

                    break;

                case '--coverage-cobertura':
                    $coverageCobertura = $option[1];

                    break;

                case '--coverage-crap4j':
                    $coverageCrap4J = $option[1];

                    break;

                case '--coverage-html':
                    $coverageHtml = $option[1];

                    break;

                case '--coverage-php':
                    $coveragePhp = $option[1];

                    break;

                case '--coverage-text':
                    if ($option[1] === null) {
                        $option[1] = 'php://stdout';
                    }

                    $coverageText                   = $option[1];
                    $coverageTextShowUncoveredFiles = false;
                    $coverageTextShowOnlySummary    = false;

                    break;

                case '--coverage-xml':
                    $coverageXml = $option[1];

                    break;

                case '--path-coverage':
                    $pathCoverage = true;

                    break;

                case 'd':
                    $tmp = explode('=', $option[1]);

                    if (isset($tmp[0])) {
                        if (isset($tmp[1])) {
                            $iniSettings[$tmp[0]] = $tmp[1];
                        } else {
                            $iniSettings[$tmp[0]] = '1';
                        }
                    }

                    break;

                case 'h':
                case '--help':
                    $help = true;

                    break;

                case '--filter':
                    $filter = $option[1];

                    break;

                case '--testsuite':
                    $testSuite = $option[1];

                    break;

                case '--exclude-testsuite':
                    $excludeTestSuite = $option[1];

                    break;

                case '--generate-configuration':
                    $generateConfiguration = true;

                    break;

                case '--migrate-configuration':
                    $migrateConfiguration = true;

                    break;

                case '--group':
                    $groups = explode(',', $option[1]);

                    break;

                case '--exclude-group':
                    $excludeGroups = explode(',', $option[1]);

                    break;

                case '--covers':
                    $testsCovering = array_map('strtolower', explode(',', $option[1]));

                    break;

                case '--uses':
                    $testsUsing = array_map('strtolower', explode(',', $option[1]));

                    break;

                case '--test-suffix':
                    $testSuffixes = explode(',', $option[1]);

                    break;

                case '--include-path':
                    $includePath = $option[1];

                    break;

                case '--list-groups':
                    $listGroups = true;

                    break;

                case '--list-suites':
                    $listSuites = true;

                    break;

                case '--list-tests':
                    $listTests = true;

                    break;

                case '--list-tests-xml':
                    $listTestsXml = $option[1];

                    break;

                case '--log-junit':
                    $junitLogfile = $option[1];

                    break;

                case '--log-teamcity':
                    $teamcityLogfile = $option[1];

                    break;

                case '--order-by':
                    foreach (explode(',', $option[1]) as $order) {
                        switch ($order) {
                            case 'default':
                                $executionOrder        = TestSuiteSorter::ORDER_DEFAULT;
                                $executionOrderDefects = TestSuiteSorter::ORDER_DEFAULT;
                                $resolveDependencies   = true;

                                break;

                            case 'defects':
                                $executionOrderDefects = TestSuiteSorter::ORDER_DEFECTS_FIRST;

                                break;

                            case 'depends':
                                $resolveDependencies = true;

                                break;

                            case 'duration':
                                $executionOrder = TestSuiteSorter::ORDER_DURATION;

                                break;

                            case 'no-depends':
                                $resolveDependencies = false;

                                break;

                            case 'random':
                                $executionOrder = TestSuiteSorter::ORDER_RANDOMIZED;

                                break;

                            case 'reverse':
                                $executionOrder = TestSuiteSorter::ORDER_REVERSED;

                                break;

                            case 'size':
                                $executionOrder = TestSuiteSorter::ORDER_SIZE;

                                break;

                            default:
                                $unrecognizedOrderBy = $order;
                        }
                    }

                    break;

                case '--process-isolation':
                    $processIsolation = true;

                    break;

                case '--repeat':
                    $repeat = (int) $option[1];

                    break;

                case '--stderr':
                    $stderr = true;

                    break;

                case '--stop-on-defect':
                    $stopOnDefect = true;

                    break;

                case '--stop-on-error':
                    $stopOnError = true;

                    break;

                case '--stop-on-failure':
                    $stopOnFailure = true;

                    break;

                case '--stop-on-warning':
                    $stopOnWarning = true;

                    break;

                case '--stop-on-incomplete':
                    $stopOnIncomplete = true;

                    break;

                case '--stop-on-risky':
                    $stopOnRisky = true;

                    break;

                case '--stop-on-skipped':
                    $stopOnSkipped = true;

                    break;

                case '--fail-on-empty-test-suite':
                    $failOnEmptyTestSuite = true;

                    break;

                case '--fail-on-incomplete':
                    $failOnIncomplete = true;

                    break;

                case '--fail-on-risky':
                    $failOnRisky = true;

                    break;

                case '--fail-on-skipped':
                    $failOnSkipped = true;

                    break;

                case '--fail-on-warning':
                    $failOnWarning = true;

                    break;

                case '--teamcity':
                    $printerTeamCity = true;

                    break;

                case '--testdox':
                    $printerTestDox = true;

                    break;

                case '--testdox-group':
                    $testdoxGroups = explode(',', $option[1]);

                    break;

                case '--testdox-exclude-group':
                    $testdoxExcludeGroups = explode(',', $option[1]);

                    break;

                case '--testdox-html':
                    $testdoxHtmlFile = $option[1];

                    break;

                case '--testdox-text':
                    $testdoxTextFile = $option[1];

                    break;

                case '--testdox-xml':
                    $testdoxXmlFile = $option[1];

                    break;

                case '--no-configuration':
                    $useDefaultConfiguration = false;

                    break;

                case '--no-extensions':
                    $noExtensions = true;

                    break;

                case '--no-coverage':
                    $noCoverage = true;

                    break;

                case '--no-logging':
                    $noLogging = true;

                    break;

                case '--no-interaction':
                    $noInteraction = true;

                    break;

                case '--no-output':
                    $noOutput = true;

                    break;

                case '--globals-backup':
                    $backupGlobals = true;

                    break;

                case '--static-backup':
                    $backupStaticProperties = true;

                    break;

                case 'v':
                case '--verbose':
                    $verbose = true;

                    break;

                case '--atleast-version':
                    $atLeastVersion = $option[1];

                    break;

                case '--version':
                    $version = true;

                    break;

                case '--dont-report-useless-tests':
                    $reportUselessTests = false;

                    break;

                case '--strict-coverage':
                    $strictCoverage = true;

                    break;

                case '--disable-coverage-ignore':
                    $disableCodeCoverageIgnore = true;

                    break;

                case '--strict-global-state':
                    $beStrictAboutChangesToGlobalState = true;

                    break;

                case '--disallow-test-output':
                    $disallowTestOutput = true;

                    break;

                case '--default-time-limit':
                    $defaultTimeLimit = (int) $option[1];

                    break;

                case '--enforce-time-limit':
                    $enforceTimeLimit = true;

                    break;

                case '--reverse-list':
                    $reverseList = true;

                    break;

                case '--check-version':
                    $checkVersion = true;

                    break;

                case '--coverage-filter':
                    if ($coverageFilter === null) {
                        $coverageFilter = [];
                    }

                    $coverageFilter[] = $option[1];

                    break;

                case '--random-order':
                    $executionOrder = TestSuiteSorter::ORDER_RANDOMIZED;

                    break;

                case '--random-order-seed':
                    $randomOrderSeed = (int) $option[1];

                    break;

                case '--resolve-dependencies':
                    $resolveDependencies = true;

                    break;

                case '--ignore-dependencies':
                    $resolveDependencies = false;

                    break;

                case '--reverse-order':
                    $executionOrder = TestSuiteSorter::ORDER_REVERSED;

                    break;

                case '--trace-text':
                    $plainTextTrace = $option[1];

                    break;
            }
        }

        if (empty($iniSettings)) {
            $iniSettings = null;
        }

        if (empty($coverageFilter)) {
            $coverageFilter = null;
        }

        return new Configuration(
            $argument,
            $atLeastVersion,
            $backupGlobals,
            $backupStaticProperties,
            $beStrictAboutChangesToGlobalState,
            $bootstrap,
            $cacheDirectory,
            $cacheResult,
            $cacheResultFile,
            $checkVersion,
            $colors,
            $columns,
            $configuration,
            $coverageClover,
            $coverageCobertura,
            $coverageCrap4J,
            $coverageHtml,
            $coveragePhp,
            $coverageText,
            $coverageTextShowUncoveredFiles,
            $coverageTextShowOnlySummary,
            $coverageXml,
            $pathCoverage,
            $coverageCacheDirectory,
            $warmCoverageCache,
            $defaultTimeLimit,
            $disableCodeCoverageIgnore,
            $disallowTestOutput,
            $enforceTimeLimit,
            $excludeGroups,
            $executionOrder,
            $executionOrderDefects,
            $failOnEmptyTestSuite,
            $failOnIncomplete,
            $failOnRisky,
            $failOnSkipped,
            $failOnWarning,
            $filter,
            $generateConfiguration,
            $migrateConfiguration,
            $groups,
            $testsCovering,
            $testsUsing,
            $help,
            $includePath,
            $iniSettings,
            $junitLogfile,
            $listGroups,
            $listSuites,
            $listTests,
            $listTestsXml,
            $noCoverage,
            $noExtensions,
            $noInteraction,
            $noOutput,
            $noLogging,
            $processIsolation,
            $randomOrderSeed,
            $repeat,
            $reportUselessTests,
            $resolveDependencies,
            $reverseList,
            $stderr,
            $strictCoverage,
            $stopOnDefect,
            $stopOnError,
            $stopOnFailure,
            $stopOnIncomplete,
            $stopOnRisky,
            $stopOnSkipped,
            $stopOnWarning,
            $teamcityLogfile,
            $testdoxExcludeGroups,
            $testdoxGroups,
            $testdoxHtmlFile,
            $testdoxTextFile,
            $testdoxXmlFile,
            $testSuffixes,
            $testSuite,
            $excludeTestSuite,
            $unrecognizedOrderBy,
            $useDefaultConfiguration,
            $verbose,
            $version,
            $coverageFilter,
            $plainTextTrace,
            $printerTeamCity,
            $printerTestDox
        );
    }
}
