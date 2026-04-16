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

use const DIRECTORY_SEPARATOR;
use function assert;
use function basename;
use function count;
use function explode;
use function getcwd;
use function is_file;
use function is_numeric;
use function max;
use function sprintf;
use function strtolower;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Util\Filesystem;
use SebastianBergmann\CliParser\Exception as CliParserException;
use SebastianBergmann\CliParser\Parser as CliParser;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Builder
{
    /**
     * @var non-empty-list<non-empty-string>
     */
    private const array LONG_OPTIONS = [
        'all',
        'atleast-version=',
        'bootstrap=',
        'cache-result',
        'do-not-cache-result',
        'cache-directory=',
        'check-version',
        'check-php-configuration',
        'colors==',
        'columns=',
        'configuration=',
        'warm-coverage-cache',
        'coverage-filter=',
        'coverage-clover=',
        'coverage-cobertura=',
        'coverage-crap4j=',
        'coverage-html=',
        'coverage-openclover=',
        'coverage-php=',
        'coverage-text==',
        'only-summary-for-coverage-text',
        'show-uncovered-for-coverage-text',
        'coverage-xml=',
        'exclude-source-from-xml-coverage',
        'path-coverage',
        'disallow-test-output',
        'display-all-issues',
        'display-incomplete',
        'display-skipped',
        'display-deprecations',
        'display-phpunit-deprecations',
        'display-phpunit-notices',
        'display-errors',
        'display-notices',
        'display-warnings',
        'default-time-limit=',
        'diff-context=',
        'enforce-time-limit',
        'exclude-group=',
        'filter=',
        'exclude-filter=',
        'generate-baseline=',
        'use-baseline=',
        'ignore-baseline',
        'generate-configuration',
        'globals-backup',
        'group=',
        'covers=',
        'uses=',
        'requires-php-extension=',
        'help',
        'resolve-dependencies',
        'ignore-dependencies',
        'include-path=',
        'list-groups',
        'list-suites',
        'list-test-files',
        'list-test-ids',
        'list-tests',
        'list-tests-xml=',
        'log-junit=',
        'log-otr=',
        'include-git-information',
        'log-teamcity=',
        'migrate-configuration',
        'validate-configuration',
        'no-configuration',
        'no-coverage',
        'no-logging',
        'no-extensions',
        'no-output',
        'no-progress',
        'no-results',
        'order-by=',
        'process-isolation',
        'do-not-report-useless-tests',
        'random-order',
        'random-order-seed=',
        'repeat=',
        'reverse-order',
        'reverse-list',
        'static-backup',
        'stderr',
        'fail-on-all-issues',
        'fail-on-deprecation',
        'fail-on-phpunit-deprecation',
        'fail-on-phpunit-notice',
        'fail-on-phpunit-warning',
        'fail-on-empty-test-suite',
        'fail-on-incomplete',
        'fail-on-notice',
        'fail-on-risky',
        'fail-on-skipped',
        'fail-on-warning',
        'do-not-fail-on-deprecation',
        'do-not-fail-on-phpunit-deprecation',
        'do-not-fail-on-phpunit-notice',
        'do-not-fail-on-phpunit-warning',
        'do-not-fail-on-empty-test-suite',
        'do-not-fail-on-incomplete',
        'do-not-fail-on-notice',
        'do-not-fail-on-risky',
        'do-not-fail-on-skipped',
        'do-not-fail-on-warning',
        'stop-on-defect==',
        'stop-on-deprecation==',
        'stop-on-error==',
        'stop-on-failure==',
        'stop-on-incomplete==',
        'stop-on-notice==',
        'stop-on-risky==',
        'stop-on-skipped==',
        'stop-on-warning==',
        'strict-coverage',
        'require-coverage-contribution',
        'disable-coverage-ignore',
        'strict-global-state',
        'teamcity',
        'testdox',
        'testdox-summary',
        'testdox-html=',
        'testdox-text=',
        'test-suffix=',
        'testsuite=',
        'exclude-testsuite=',
        'test-files-file=',
        'test-id-filter-file=',
        'run-test-id=',
        'log-events-text=',
        'log-events-verbose-text=',
        'version',
        'debug',
        'with-telemetry',
        'extension=',
    ];

    private const string SHORT_OPTIONS = 'd:c:h';

    /**
     * @var list<array{non-empty-string, non-empty-string}>
     */
    private const array CONFLICTING_OPTIONS = [
        ['--cache-result', '--do-not-cache-result'],
        ['--fail-on-deprecation', '--do-not-fail-on-deprecation'],
        ['--fail-on-phpunit-deprecation', '--do-not-fail-on-phpunit-deprecation'],
        ['--fail-on-phpunit-notice', '--do-not-fail-on-phpunit-notice'],
        ['--fail-on-phpunit-warning', '--do-not-fail-on-phpunit-warning'],
        ['--fail-on-empty-test-suite', '--do-not-fail-on-empty-test-suite'],
        ['--fail-on-incomplete', '--do-not-fail-on-incomplete'],
        ['--fail-on-notice', '--do-not-fail-on-notice'],
        ['--fail-on-risky', '--do-not-fail-on-risky'],
        ['--fail-on-skipped', '--do-not-fail-on-skipped'],
        ['--fail-on-warning', '--do-not-fail-on-warning'],
        ['--resolve-dependencies', '--ignore-dependencies'],
        ['--random-order', '--reverse-order'],
        ['--generate-baseline', '--ignore-baseline'],
        ['--generate-baseline', '--use-baseline'],
        ['--no-output', '--teamcity'],
        ['--no-output', '--testdox'],
        ['--no-output', '--testdox-summary'],
        ['--no-output', '--debug'],
    ];

    /**
     * @var list<non-empty-string>
     */
    private const array COMMAND_OPTIONS = [
        '--atleast-version',
        '--check-php-configuration',
        '--check-version',
        '--generate-configuration',
        '--help',
        '--list-groups',
        '--list-suites',
        '--list-test-files',
        '--list-test-ids',
        '--list-tests',
        '--list-tests-xml',
        '--migrate-configuration',
        '--validate-configuration',
        '--version',
        '--warm-coverage-cache',
    ];

    /**
     * @var array<string, non-negative-int>
     */
    private array $processed = [];

    /**
     * @param list<string> $parameters
     *
     * @throws Exception
     */
    public function fromParameters(array $parameters): Configuration
    {
        try {
            $options = (new CliParser)->parse(
                $parameters,
                self::SHORT_OPTIONS,
                self::LONG_OPTIONS,
            );
        } catch (CliParserException $e) {
            throw new Exception(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        $all                               = null;
        $atLeastVersion                    = null;
        $backupGlobals                     = null;
        $backupStaticProperties            = null;
        $beStrictAboutChangesToGlobalState = null;
        $bootstrap                         = null;
        $cacheDirectory                    = null;
        $cacheResult                       = null;
        $checkPhpConfiguration             = false;
        $checkVersion                      = false;
        $colors                            = null;
        $columns                           = null;
        $configuration                     = null;
        $warmCoverageCache                 = false;
        $coverageFilter                    = null;
        $coverageClover                    = null;
        $coverageCobertura                 = null;
        $coverageCrap4J                    = null;
        $coverageHtml                      = null;
        $coverageOpenClover                = null;
        $coveragePhp                       = null;
        $coverageText                      = null;
        $coverageTextShowUncoveredFiles    = null;
        $coverageTextShowOnlySummary       = null;
        $coverageXml                       = null;
        $excludeSourceFromXmlCoverage      = null;
        $pathCoverage                      = null;
        $defaultTimeLimit                  = null;
        $diffContext                       = null;
        $disableCodeCoverageIgnore         = null;
        $disallowTestOutput                = null;
        $displayAllIssues                  = null;
        $displayIncomplete                 = null;
        $displaySkipped                    = null;
        $displayDeprecations               = null;
        $displayPhpunitDeprecations        = null;
        $displayPhpunitNotices             = null;
        $displayErrors                     = null;
        $displayNotices                    = null;
        $displayWarnings                   = null;
        $enforceTimeLimit                  = null;
        $excludeGroups                     = null;
        $executionOrder                    = null;
        $executionOrderDefects             = null;
        $failOnAllIssues                   = null;
        $failOnDeprecation                 = null;
        $failOnPhpunitDeprecation          = null;
        $failOnPhpunitNotice               = null;
        $failOnPhpunitWarning              = null;
        $failOnEmptyTestSuite              = null;
        $failOnIncomplete                  = null;
        $failOnNotice                      = null;
        $failOnRisky                       = null;
        $failOnSkipped                     = null;
        $failOnWarning                     = null;
        $doNotFailOnDeprecation            = null;
        $doNotFailOnPhpunitDeprecation     = null;
        $doNotFailOnPhpunitNotice          = null;
        $doNotFailOnPhpunitWarning         = null;
        $doNotFailOnEmptyTestSuite         = null;
        $doNotFailOnIncomplete             = null;
        $doNotFailOnNotice                 = null;
        $doNotFailOnRisky                  = null;
        $doNotFailOnSkipped                = null;
        $doNotFailOnWarning                = null;
        $stopOnDefect                      = null;
        $stopOnDeprecation                 = null;
        $specificDeprecationToStopOn       = null;
        $stopOnError                       = null;
        $stopOnFailure                     = null;
        $stopOnIncomplete                  = null;
        $stopOnNotice                      = null;
        $stopOnRisky                       = null;
        $stopOnSkipped                     = null;
        $stopOnWarning                     = null;
        $filter                            = null;
        $excludeFilter                     = null;
        $generateBaseline                  = null;
        $useBaseline                       = null;
        $ignoreBaseline                    = false;
        $generateConfiguration             = false;
        $migrateConfiguration              = false;
        $validateConfiguration             = false;
        $groups                            = null;
        $testsCovering                     = null;
        $testsUsing                        = null;
        $testsRequiringPhpExtension        = null;
        $help                              = false;
        $includePath                       = null;
        $iniSettings                       = [];
        $junitLogfile                      = null;
        $otrLogfile                        = null;
        $includeGitInformation             = null;
        $listGroups                        = false;
        $listSuites                        = false;
        $listTestFiles                     = false;
        $listTestIds                       = false;
        $listTests                         = false;
        $listTestsXml                      = null;
        $noCoverage                        = null;
        $noExtensions                      = null;
        $noOutput                          = null;
        $noProgress                        = null;
        $noResults                         = null;
        $noLogging                         = null;
        $processIsolation                  = null;
        $randomOrderSeed                   = null;
        $repeat                            = null;
        $reportUselessTests                = null;
        $resolveDependencies               = null;
        $reverseList                       = null;
        $stderr                            = null;
        $strictCoverage                    = null;
        $requireCoverageContribution       = null;
        $teamcityLogfile                   = null;
        $testdoxHtmlFile                   = null;
        $testdoxTextFile                   = null;
        $testSuffixes                      = null;
        $testSuite                         = null;
        $excludeTestSuite                  = null;
        $testFilesFile                     = null;
        $testIdFile                        = null;
        $testIdFilter                      = null;
        $useDefaultConfiguration           = true;
        $version                           = false;
        $logEventsText                     = null;
        $logEventsVerboseText              = null;
        $printerTeamCity                   = null;
        $printerTestDox                    = null;
        $printerTestDoxSummary             = null;
        $debug                             = false;
        $withTelemetry                     = false;
        $extensions                        = [];

        foreach ($options[0] as $option) {
            $optionAllowedMultipleTimes = false;

            switch ($option[0]) {
                case '--all':
                    $all = true;

                    break;

                case '--colors':
                    $colors = \PHPUnit\TextUI\Configuration\Configuration::COLOR_AUTO;

                    if ($option[1] !== null) {
                        $colors = $option[1];
                    }

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

                case '--coverage-openclover':
                    $coverageOpenClover = $option[1];

                    break;

                case '--coverage-text':
                    if ($option[1] === null) {
                        $option[1] = 'php://stdout';
                    }

                    $coverageText = $option[1];

                    break;

                case '--only-summary-for-coverage-text':
                    $coverageTextShowOnlySummary = true;

                    break;

                case '--show-uncovered-for-coverage-text':
                    $coverageTextShowUncoveredFiles = true;

                    break;

                case '--coverage-xml':
                    $coverageXml = $option[1];

                    break;

                case '--exclude-source-from-xml-coverage':
                    $excludeSourceFromXmlCoverage = true;

                    break;

                case '--path-coverage':
                    $pathCoverage = true;

                    break;

                case 'd':
                    $tmp = explode('=', $option[1]);

                    if (isset($tmp[0])) {
                        assert($tmp[0] !== '');

                        if (isset($tmp[1])) {
                            assert($tmp[1] !== '');

                            $iniSettings[$tmp[0]] = $tmp[1];
                        } else {
                            $iniSettings[$tmp[0]] = '1';
                        }
                    }

                    $optionAllowedMultipleTimes = true;

                    break;

                case 'h':
                case '--help':
                    $help = true;

                    break;

                case '--filter':
                    $filter = $option[1];

                    break;

                case '--exclude-filter':
                    $excludeFilter = $option[1];

                    break;

                case '--testsuite':
                    $testSuite = $option[1];

                    break;

                case '--exclude-testsuite':
                    $excludeTestSuite = $option[1];

                    break;

                case '--test-files-file':
                    $testFilesFile = $option[1];

                    break;

                case '--test-id-filter-file':
                    $testIdFile = $option[1];

                    break;

                case '--run-test-id':
                    $testIdFilter = $option[1];

                    break;

                case '--generate-baseline':
                    $generateBaseline = $option[1];

                    if (basename($generateBaseline) === $generateBaseline) {
                        $generateBaseline = getcwd() . DIRECTORY_SEPARATOR . $generateBaseline;
                    }

                    break;

                case '--use-baseline':
                    $useBaseline = $option[1];

                    if (basename($useBaseline) === $useBaseline && !is_file($useBaseline)) {
                        $useBaseline = getcwd() . DIRECTORY_SEPARATOR . $useBaseline;
                    }

                    break;

                case '--ignore-baseline':
                    $ignoreBaseline = true;

                    break;

                case '--generate-configuration':
                    $generateConfiguration = true;

                    break;

                case '--migrate-configuration':
                    $migrateConfiguration = true;

                    break;

                case '--validate-configuration':
                    $validateConfiguration = true;

                    break;

                case '--group':
                    if ($groups === null) {
                        $groups = [];
                    }

                    $groups[] = $option[1];

                    $optionAllowedMultipleTimes = true;

                    break;

                case '--exclude-group':
                    if ($excludeGroups === null) {
                        $excludeGroups = [];
                    }

                    $excludeGroups[] = $option[1];

                    $optionAllowedMultipleTimes = true;

                    break;

                case '--covers':
                    if ($testsCovering === null) {
                        $testsCovering = [];
                    }

                    $testsCovering[] = strtolower($option[1]);

                    $optionAllowedMultipleTimes = true;

                    break;

                case '--uses':
                    if ($testsUsing === null) {
                        $testsUsing = [];
                    }

                    $testsUsing[] = strtolower($option[1]);

                    $optionAllowedMultipleTimes = true;

                    break;

                case '--requires-php-extension':
                    if ($testsRequiringPhpExtension === null) {
                        $testsRequiringPhpExtension = [];
                    }

                    $testsRequiringPhpExtension[] = strtolower($option[1]);

                    $optionAllowedMultipleTimes = true;

                    break;

                case '--test-suffix':
                    if ($testSuffixes === null) {
                        $testSuffixes = [];
                    }

                    $testSuffixes[] = $option[1];

                    $optionAllowedMultipleTimes = true;

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

                case '--list-test-files':
                    $listTestFiles = true;

                    break;

                case '--list-test-ids':
                    $listTestIds = true;

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

                case '--log-otr':
                    $otrLogfile = $option[1];

                    break;

                case '--include-git-information':
                    $includeGitInformation = true;

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
                                $executionOrder = TestSuiteSorter::ORDER_DURATION_ASCENDING;

                                EventFacade::emitter()->testRunnerTriggeredPhpunitDeprecation(
                                    'Using "duration" for --order-by is deprecated and will be removed in PHPUnit 14. Use "duration-ascending" instead.',
                                );

                                break;

                            case 'duration-ascending':
                                $executionOrder = TestSuiteSorter::ORDER_DURATION_ASCENDING;

                                break;

                            case 'duration-descending':
                                $executionOrder = TestSuiteSorter::ORDER_DURATION_DESCENDING;

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
                                $executionOrder = TestSuiteSorter::ORDER_SIZE_ASCENDING;

                                EventFacade::emitter()->testRunnerTriggeredPhpunitDeprecation(
                                    'Using "size" for --order-by is deprecated and will be removed in PHPUnit 14. Use "size-ascending" instead.',
                                );

                                break;

                            case 'size-ascending':
                                $executionOrder = TestSuiteSorter::ORDER_SIZE_ASCENDING;

                                break;

                            case 'size-descending':
                                $executionOrder = TestSuiteSorter::ORDER_SIZE_DESCENDING;

                                break;

                            default:
                                throw new Exception(
                                    sprintf(
                                        'unrecognized --order-by option: %s',
                                        $order,
                                    ),
                                );
                        }
                    }

                    break;

                case '--process-isolation':
                    $processIsolation = true;

                    break;

                case '--stderr':
                    $stderr = true;

                    break;

                case '--fail-on-all-issues':
                    $failOnAllIssues = true;

                    break;

                case '--fail-on-deprecation':
                    $failOnDeprecation = true;

                    break;

                case '--fail-on-phpunit-deprecation':
                    $failOnPhpunitDeprecation = true;

                    break;

                case '--fail-on-phpunit-notice':
                    $failOnPhpunitNotice = true;

                    break;

                case '--fail-on-phpunit-warning':
                    $failOnPhpunitWarning = true;

                    break;

                case '--fail-on-empty-test-suite':
                    $failOnEmptyTestSuite = true;

                    break;

                case '--fail-on-incomplete':
                    $failOnIncomplete = true;

                    break;

                case '--fail-on-notice':
                    $failOnNotice = true;

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

                case '--do-not-fail-on-deprecation':
                    $doNotFailOnDeprecation = true;

                    break;

                case '--do-not-fail-on-phpunit-deprecation':
                    $doNotFailOnPhpunitDeprecation = true;

                    break;

                case '--do-not-fail-on-phpunit-notice':
                    $doNotFailOnPhpunitNotice = true;

                    break;

                case '--do-not-fail-on-phpunit-warning':
                    $doNotFailOnPhpunitWarning = true;

                    break;

                case '--do-not-fail-on-empty-test-suite':
                    $doNotFailOnEmptyTestSuite = true;

                    break;

                case '--do-not-fail-on-incomplete':
                    $doNotFailOnIncomplete = true;

                    break;

                case '--do-not-fail-on-notice':
                    $doNotFailOnNotice = true;

                    break;

                case '--do-not-fail-on-risky':
                    $doNotFailOnRisky = true;

                    break;

                case '--do-not-fail-on-skipped':
                    $doNotFailOnSkipped = true;

                    break;

                case '--do-not-fail-on-warning':
                    $doNotFailOnWarning = true;

                    break;

                case '--stop-on-defect':
                    $stopOnDefect = $this->parseStopOnValue($option[1]);

                    break;

                case '--stop-on-deprecation':
                    $stopOnDeprecation = $this->parseStopOnValue($option[1]);

                    if ($option[1] !== null && !is_numeric($option[1])) {
                        $specificDeprecationToStopOn = $option[1];
                    }

                    break;

                case '--stop-on-error':
                    $stopOnError = $this->parseStopOnValue($option[1]);

                    break;

                case '--stop-on-failure':
                    $stopOnFailure = $this->parseStopOnValue($option[1]);

                    break;

                case '--stop-on-incomplete':
                    $stopOnIncomplete = $this->parseStopOnValue($option[1]);

                    break;

                case '--stop-on-notice':
                    $stopOnNotice = $this->parseStopOnValue($option[1]);

                    break;

                case '--stop-on-risky':
                    $stopOnRisky = $this->parseStopOnValue($option[1]);

                    break;

                case '--stop-on-skipped':
                    $stopOnSkipped = $this->parseStopOnValue($option[1]);

                    break;

                case '--stop-on-warning':
                    $stopOnWarning = $this->parseStopOnValue($option[1]);

                    break;

                case '--teamcity':
                    $printerTeamCity = true;

                    break;

                case '--testdox':
                    $printerTestDox = true;

                    break;

                case '--testdox-summary':
                    $printerTestDoxSummary = true;

                    break;

                case '--testdox-html':
                    $testdoxHtmlFile = $option[1];

                    break;

                case '--testdox-text':
                    $testdoxTextFile = $option[1];

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

                case '--no-output':
                    $noOutput = true;

                    break;

                case '--no-progress':
                    $noProgress = true;

                    break;

                case '--no-results':
                    $noResults = true;

                    break;

                case '--globals-backup':
                    $backupGlobals = true;

                    break;

                case '--static-backup':
                    $backupStaticProperties = true;

                    break;

                case '--atleast-version':
                    $atLeastVersion = $option[1];

                    break;

                case '--version':
                    $version = true;

                    break;

                case '--do-not-report-useless-tests':
                    $reportUselessTests = false;

                    break;

                case '--strict-coverage':
                    $strictCoverage = true;

                    break;

                case '--require-coverage-contribution':
                    $requireCoverageContribution = true;

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

                case '--display-all-issues':
                    $displayAllIssues = true;

                    break;

                case '--display-incomplete':
                    $displayIncomplete = true;

                    break;

                case '--display-skipped':
                    $displaySkipped = true;

                    break;

                case '--display-deprecations':
                    $displayDeprecations = true;

                    break;

                case '--display-phpunit-deprecations':
                    $displayPhpunitDeprecations = true;

                    break;

                case '--display-phpunit-notices':
                    $displayPhpunitNotices = true;

                    break;

                case '--display-errors':
                    $displayErrors = true;

                    break;

                case '--display-notices':
                    $displayNotices = true;

                    break;

                case '--display-warnings':
                    $displayWarnings = true;

                    break;

                case '--default-time-limit':
                    $defaultTimeLimit = (int) $option[1];

                    break;

                case '--diff-context':
                    $diffContext = (int) $option[1];

                    break;

                case '--enforce-time-limit':
                    $enforceTimeLimit = true;

                    break;

                case '--reverse-list':
                    $reverseList = true;

                    break;

                case '--check-php-configuration':
                    $checkPhpConfiguration = true;

                    break;

                case '--check-version':
                    $checkVersion = true;

                    break;

                case '--coverage-filter':
                    if ($coverageFilter === null) {
                        $coverageFilter = [];
                    }

                    $coverageFilter[] = $option[1];

                    $optionAllowedMultipleTimes = true;

                    break;

                case '--random-order':
                    $executionOrder = TestSuiteSorter::ORDER_RANDOMIZED;

                    break;

                case '--random-order-seed':
                    $randomOrderSeed = (int) $option[1];

                    break;

                case '--repeat':
                    if (!is_numeric($option[1]) ||
                        (string) (int) $option[1] !== $option[1] ||
                        (int) $option[1] < 1) {
                        EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                            sprintf(
                                'Option "--repeat %s" ignored because "%s" is not a positive integer',
                                $option[1],
                                $option[1],
                            ),
                        );

                        break;
                    }

                    $repeat = (int) $option[1];

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

                case '--log-events-text':
                    $logEventsText = Filesystem::resolveStreamOrFile($option[1]);

                    if ($logEventsText === false) {
                        throw new Exception(
                            sprintf(
                                'The path "%s" specified for the --log-events-text option could not be resolved',
                                $option[1],
                            ),
                        );
                    }

                    break;

                case '--log-events-verbose-text':
                    $logEventsVerboseText = Filesystem::resolveStreamOrFile($option[1]);

                    if ($logEventsVerboseText === false) {
                        throw new Exception(
                            sprintf(
                                'The path "%s" specified for the --log-events-verbose-text option could not be resolved',
                                $option[1],
                            ),
                        );
                    }

                    break;

                case '--debug':
                    $debug = true;

                    break;

                case '--with-telemetry':
                    $withTelemetry = true;

                    break;

                case '--extension':
                    $extensions[] = $option[1];

                    $optionAllowedMultipleTimes = true;

                    break;
            }

            if ($optionAllowedMultipleTimes) {
                $this->processed[$option[0]] = 1;
            } else {
                $this->markProcessed($option[0]);
            }
        }

        if ($iniSettings === []) {
            $iniSettings = null;
        }

        if ($extensions === []) {
            $extensions = null;
        }

        $this->warnAboutConflictingOptions();

        if ($randomOrderSeed !== null && $executionOrder !== TestSuiteSorter::ORDER_RANDOMIZED) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                '--random-order-seed is only used when execution order is "random" (use --order-by random or --random-order)',
            );
        }

        return new Configuration(
            $options[1],
            $testFilesFile,
            $testIdFile,
            $testIdFilter,
            $all,
            $atLeastVersion,
            $backupGlobals,
            $backupStaticProperties,
            $beStrictAboutChangesToGlobalState,
            $bootstrap,
            $cacheDirectory,
            $cacheResult,
            $checkPhpConfiguration,
            $checkVersion,
            $colors,
            $columns,
            $configuration,
            $coverageClover,
            $coverageCobertura,
            $coverageCrap4J,
            $coverageHtml,
            $coverageOpenClover,
            $coveragePhp,
            $coverageText,
            $coverageTextShowUncoveredFiles,
            $coverageTextShowOnlySummary,
            $coverageXml,
            $excludeSourceFromXmlCoverage,
            $pathCoverage,
            $warmCoverageCache,
            $defaultTimeLimit,
            $diffContext,
            $disableCodeCoverageIgnore,
            $disallowTestOutput,
            $enforceTimeLimit,
            $excludeGroups,
            $executionOrder,
            $executionOrderDefects,
            $failOnAllIssues,
            $failOnDeprecation,
            $failOnPhpunitDeprecation,
            $failOnPhpunitNotice,
            $failOnPhpunitWarning,
            $failOnEmptyTestSuite,
            $failOnIncomplete,
            $failOnNotice,
            $failOnRisky,
            $failOnSkipped,
            $failOnWarning,
            $doNotFailOnDeprecation,
            $doNotFailOnPhpunitDeprecation,
            $doNotFailOnPhpunitNotice,
            $doNotFailOnPhpunitWarning,
            $doNotFailOnEmptyTestSuite,
            $doNotFailOnIncomplete,
            $doNotFailOnNotice,
            $doNotFailOnRisky,
            $doNotFailOnSkipped,
            $doNotFailOnWarning,
            $stopOnDefect,
            $stopOnDeprecation,
            $specificDeprecationToStopOn,
            $stopOnError,
            $stopOnFailure,
            $stopOnIncomplete,
            $stopOnNotice,
            $stopOnRisky,
            $stopOnSkipped,
            $stopOnWarning,
            $filter,
            $excludeFilter,
            $generateBaseline,
            $useBaseline,
            $ignoreBaseline,
            $generateConfiguration,
            $migrateConfiguration,
            $validateConfiguration,
            $groups,
            $testsCovering,
            $testsUsing,
            $testsRequiringPhpExtension,
            $help,
            $includePath,
            $iniSettings,
            $junitLogfile,
            $otrLogfile,
            $includeGitInformation,
            $listGroups,
            $listSuites,
            $listTestFiles,
            $listTestIds,
            $listTests,
            $listTestsXml,
            $noCoverage,
            $noExtensions,
            $noOutput,
            $noProgress,
            $noResults,
            $noLogging,
            $processIsolation,
            $randomOrderSeed,
            $repeat,
            $reportUselessTests,
            $resolveDependencies,
            $reverseList,
            $stderr,
            $strictCoverage,
            $requireCoverageContribution,
            $teamcityLogfile,
            $testdoxHtmlFile,
            $testdoxTextFile,
            $testSuffixes,
            $testSuite,
            $excludeTestSuite,
            $useDefaultConfiguration,
            $displayAllIssues,
            $displayIncomplete,
            $displaySkipped,
            $displayDeprecations,
            $displayPhpunitDeprecations,
            $displayPhpunitNotices,
            $displayErrors,
            $displayNotices,
            $displayWarnings,
            $version,
            $coverageFilter,
            $logEventsText,
            $logEventsVerboseText,
            $printerTeamCity,
            $printerTestDox,
            $printerTestDoxSummary,
            $debug,
            $withTelemetry,
            $extensions,
        );
    }

    /**
     * @param non-empty-string $option
     */
    private function markProcessed(string $option): void
    {
        if (!isset($this->processed[$option])) {
            $this->processed[$option] = 1;

            return;
        }

        $this->processed[$option]++;

        if ($this->processed[$option] === 2) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Option %s cannot be used more than once',
                    $option,
                ),
            );
        }
    }

    private function warnAboutConflictingOptions(): void
    {
        foreach (self::CONFLICTING_OPTIONS as $conflictingOptions) {
            if (isset($this->processed[$conflictingOptions[0]], $this->processed[$conflictingOptions[1]])) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Options %s and %s cannot be used together',
                        $conflictingOptions[0],
                        $conflictingOptions[1],
                    ),
                );
            }
        }

        $usedCommandOptions = [];

        foreach (self::COMMAND_OPTIONS as $commandOption) {
            if (isset($this->processed[$commandOption])) {
                $usedCommandOptions[] = $commandOption;
            }
        }

        if (count($usedCommandOptions) > 1) {
            throw new Exception(
                sprintf(
                    'Options %s and %s cannot be used together',
                    $usedCommandOptions[0],
                    $usedCommandOptions[1],
                ),
            );
        }
    }

    /**
     * @return positive-int
     */
    private function parseStopOnValue(?string $value): int
    {
        if ($value !== null && is_numeric($value)) {
            return max(1, (int) $value);
        }

        return 1;
    }
}
