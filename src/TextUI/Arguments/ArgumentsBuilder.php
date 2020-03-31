<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Arguments;

use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\Configuration\Extension;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\Util\Exception as UtilException;
use PHPUnit\Util\Getopt;
use PHPUnit\Util\Log\TeamCity;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ArgumentsBuilder
{
    private const LONG_OPTIONS = [
        'atleast-version=',
        'prepend=',
        'bootstrap=',
        'cache-result',
        'do-not-cache-result',
        'cache-result-file=',
        'check-version',
        'colors==',
        'columns=',
        'configuration=',
        'coverage-clover=',
        'coverage-crap4j=',
        'coverage-html=',
        'coverage-php=',
        'coverage-text==',
        'coverage-xml=',
        'debug',
        'disallow-test-output',
        'disallow-resource-usage',
        'disallow-todo-tests',
        'default-time-limit=',
        'enforce-time-limit',
        'exclude-group=',
        'extensions=',
        'filter=',
        'generate-configuration',
        'globals-backup',
        'group=',
        'help',
        'resolve-dependencies',
        'ignore-dependencies',
        'include-path=',
        'list-groups',
        'list-suites',
        'list-tests',
        'list-tests-xml=',
        'loader=',
        'log-junit=',
        'log-teamcity=',
        'no-configuration',
        'no-coverage',
        'no-logging',
        'no-interaction',
        'no-extensions',
        'order-by=',
        'printer=',
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
        'fail-on-warning',
        'fail-on-risky',
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
        'verbose',
        'version',
        'whitelist=',
        'dump-xdebug-filter=',
    ];

    private const SHORT_OPTIONS = 'd:c:hv';

    public function fromParameters(array $parameters, array $additionalLongOptions): Arguments
    {
        try {
            $options = Getopt::getopt(
                $parameters,
                self::SHORT_OPTIONS,
                \array_merge(self::LONG_OPTIONS, $additionalLongOptions)
            );
        } catch (UtilException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        $argument                                   = null;
        $atLeastVersion                             = null;
        $backupGlobals                              = null;
        $backupStaticAttributes                     = null;
        $beStrictAboutChangesToGlobalState          = null;
        $beStrictAboutResourceUsageDuringSmallTests = null;
        $bootstrap                                  = null;
        $cacheResult                                = null;
        $cacheResultFile                            = null;
        $checkVersion                               = null;
        $colors                                     = null;
        $columns                                    = null;
        $configuration                              = null;
        $coverageClover                             = null;
        $coverageCrap4J                             = null;
        $coverageHtml                               = null;
        $coveragePhp                                = null;
        $coverageText                               = null;
        $coverageTextShowUncoveredFiles             = null;
        $coverageTextShowOnlySummary                = null;
        $coverageXml                                = null;
        $debug                                      = null;
        $defaultTimeLimit                           = null;
        $disableCodeCoverageIgnore                  = null;
        $disallowTestOutput                         = null;
        $disallowTodoAnnotatedTests                 = null;
        $enforceTimeLimit                           = null;
        $excludeGroups                              = null;
        $executionOrder                             = null;
        $executionOrderDefects                      = null;
        $extensions                                 = [];
        $unavailableExtensions                      = [];
        $failOnIncomplete                           = null;
        $failOnRisky                                = null;
        $failOnSkipped                              = null;
        $failOnWarning                              = null;
        $filter                                     = null;
        $generateConfiguration                      = null;
        $groups                                     = null;
        $help                                       = null;
        $includePath                                = null;
        $iniSettings                                = [];
        $junitLogfile                               = null;
        $listGroups                                 = null;
        $listSuites                                 = null;
        $listTests                                  = null;
        $listTestsXml                               = null;
        $loader                                     = null;
        $noCoverage                                 = null;
        $noExtensions                               = null;
        $noInteraction                              = null;
        $noLogging                                  = null;
        $printer                                    = null;
        $processIsolation                           = null;
        $randomOrderSeed                            = null;
        $repeat                                     = null;
        $reportUselessTests                         = null;
        $resolveDependencies                        = null;
        $reverseList                                = null;
        $stderr                                     = null;
        $strictCoverage                             = null;
        $stopOnDefect                               = null;
        $stopOnError                                = null;
        $stopOnFailure                              = null;
        $stopOnIncomplete                           = null;
        $stopOnRisky                                = null;
        $stopOnSkipped                              = null;
        $stopOnWarning                              = null;
        $teamcityLogfile                            = null;
        $testdoxExcludeGroups                       = null;
        $testdoxGroups                              = null;
        $testdoxHtmlFile                            = null;
        $testdoxTextFile                            = null;
        $testdoxXmlFile                             = null;
        $testSuffixes                               = null;
        $testSuite                                  = null;
        $unrecognizedOptions                        = [];
        $unrecognizedOrderBy                        = null;
        $useDefaultConfiguration                    = null;
        $verbose                                    = null;
        $version                                    = null;
        $whitelist                                  = null;
        $xdebugFilterFile                           = null;

        if (isset($options[1][0])) {
            $argument = $options[1][0];
        }

        foreach ($options[0] as $option) {
            switch ($option[0]) {
                case '--colors':
                    $colors = $option[1] ?: DefaultResultPrinter::COLOR_AUTO;

                    break;

                case '--bootstrap':
                    $bootstrap = $option[1];

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
                    if (\is_numeric($option[1])) {
                        $columns = (int) $option[1];
                    } elseif ($option[1] === 'max') {
                        $columns = 'max';
                    }

                    break;

                case 'c':
                case '--configuration':
                    $configuration = $option[1];

                    break;

                case '--coverage-clover':
                    $coverageClover = $option[1];

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

                case 'd':
                    $tmp = \explode('=', $option[1]);

                    if (isset($tmp[0])) {
                        if (isset($tmp[1])) {
                            $iniSettings[$tmp[0]] = $tmp[1];
                        } else {
                            $iniSettings[$tmp[0]] = '1';
                        }
                    }

                    break;

                case '--debug':
                    $debug = true;

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

                case '--generate-configuration':
                    $generateConfiguration = true;

                    break;

                case '--group':
                    $groups = \explode(',', $option[1]);

                    break;

                case '--exclude-group':
                    $excludeGroups = \explode(',', $option[1]);

                    break;

                case '--test-suffix':
                    $testSuffixes = \explode(',', $option[1]);

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

                case '--printer':
                    $printer = $option[1];

                    break;

                case '--loader':
                    $loader = $option[1];

                    break;

                case '--log-junit':
                    $junitLogfile = $option[1];

                    break;

                case '--log-teamcity':
                    $teamcityLogfile = $option[1];

                    break;

                case '--order-by':
                    foreach (\explode(',', $option[1]) as $order) {
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

                case '--fail-on-incomplete':
                    $failOnIncomplete = true;

                    break;

                case '--fail-on-risky':
                    $failOnRisky = true;

                    break;

                case '--fail-on-Skipped':
                    $failOnSkipped = true;

                    break;

                case '--fail-on-warning':
                    $failOnWarning = true;

                    break;

                case '--teamcity':
                    $printer = TeamCity::class;

                    break;

                case '--testdox':
                    $printer = CliTestDoxPrinter::class;

                    break;

                case '--testdox-group':
                    $testdoxGroups = \explode(',', $option[1]);

                    break;

                case '--testdox-exclude-group':
                    $testdoxExcludeGroups = \explode(',', $option[1]);

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

                case '--extensions':
                    foreach (\explode(',', $option[1]) as $extensionClass) {
                        if (!\class_exists($extensionClass)) {
                            $unavailableExtensions[] = $extensionClass;

                            continue;
                        }

                        $extensions[] = new Extension($extensionClass, '', []);
                    }

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

                case '--globals-backup':
                    $backupGlobals = true;

                    break;

                case '--static-backup':
                    $backupStaticAttributes = true;

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

                case '--disallow-resource-usage':
                    $beStrictAboutResourceUsageDuringSmallTests = true;

                    break;

                case '--default-time-limit':
                    $defaultTimeLimit = (int) $option[1];

                    break;

                case '--enforce-time-limit':
                    $enforceTimeLimit = true;

                    break;

                case '--disallow-todo-tests':
                    $disallowTodoAnnotatedTests = true;

                    break;

                case '--reverse-list':
                    $reverseList = true;

                    break;

                case '--check-version':
                    $checkVersion = true;

                    break;

                case '--whitelist':
                    if ($whitelist === null) {
                        $whitelist = [];
                    }

                    $whitelist[] = $option[1];

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

                case '--dump-xdebug-filter':
                    $xdebugFilterFile = $option[1];

                    break;

                default:
                    $unrecognizedOptions[\str_replace('--', '', $option[0])] = $option[1];
            }
        }

        if (empty($extensions)) {
            $extensions = null;
        }

        if (empty($unavailableExtensions)) {
            $unavailableExtensions = null;
        }

        if (empty($iniSettings)) {
            $iniSettings = null;
        }

        if (empty($unrecognizedOptions)) {
            $unrecognizedOptions = null;
        }

        if (empty($whitelist)) {
            $whitelist = null;
        }

        return new Arguments(
            $argument,
            $atLeastVersion,
            $backupGlobals,
            $backupStaticAttributes,
            $beStrictAboutChangesToGlobalState,
            $beStrictAboutResourceUsageDuringSmallTests,
            $bootstrap,
            $cacheResult,
            $cacheResultFile,
            $checkVersion,
            $colors,
            $columns,
            $configuration,
            $coverageClover,
            $coverageCrap4J,
            $coverageHtml,
            $coveragePhp,
            $coverageText,
            $coverageTextShowUncoveredFiles,
            $coverageTextShowOnlySummary,
            $coverageXml,
            $debug,
            $defaultTimeLimit,
            $disableCodeCoverageIgnore,
            $disallowTestOutput,
            $disallowTodoAnnotatedTests,
            $enforceTimeLimit,
            $excludeGroups,
            $executionOrder,
            $executionOrderDefects,
            $extensions,
            $unavailableExtensions,
            $failOnIncomplete,
            $failOnRisky,
            $failOnSkipped,
            $failOnWarning,
            $filter,
            $generateConfiguration,
            $groups,
            $help,
            $includePath,
            $iniSettings,
            $junitLogfile,
            $listGroups,
            $listSuites,
            $listTests,
            $listTestsXml,
            $loader,
            $noCoverage,
            $noExtensions,
            $noInteraction,
            $noLogging,
            $printer,
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
            $unrecognizedOptions,
            $unrecognizedOrderBy,
            $useDefaultConfiguration,
            $verbose,
            $version,
            $whitelist,
            $xdebugFilterFile
        );
    }
}
