<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use const DIRECTORY_SEPARATOR;
use const PATH_SEPARATOR;
use function basename;
use function dirname;
use function getcwd;
use function is_dir;
use function realpath;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\Configuration as MergedConfiguration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\Configuration\NoFileOutputRestrictionException;
use PHPUnit\TextUI\Configuration\TimeoutNotConfiguredException;
use PHPUnit\Util\Filesystem;

#[CoversClass(Merger::class)]
#[Medium]
#[Group('textui')]
#[Group('textui/configuration')]
final class MergerTest extends TestCase
{
    public function testNoLoggingShouldOnlyAffectXmlConfiguration(): void
    {
        $junitLog = uniqid('junit_log_');
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_logging.xml');

        $this->assertTrue($fromFile->logging()->hasTeamCity());
        $this->assertTrue($fromFile->logging()->hasTestDoxHtml());
        $this->assertTrue($fromFile->logging()->hasTestDoxText());

        $this->assertTrue($fromFile->logging()->hasJunit());
        $this->assertNotSame($junitLog, $fromFile->logging()->junit()->target()->path());

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--no-logging',
            '--log-junit',
            $junitLog,
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->hasLogfileTeamcity());
        $this->assertFalse($mergedConfig->hasLogfileTestdoxHtml());
        $this->assertFalse($mergedConfig->hasLogfileTestdoxText());

        $this->assertTrue($mergedConfig->hasLogfileJunit());
        $this->assertSame($junitLog, $mergedConfig->logfileJunit());
    }

    public function testBranchCoverageCanBeEnabledFromCli(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $this->assertFalse($fromFile->codeCoverage()->branchCoverage());

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--branch-coverage',
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->branchCoverage());
    }

    public function testClassViewAndFileViewForHtmlCodeCoverageReportAreEnabledByDefault(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->coverageHtmlClassView());
        $this->assertTrue($mergedConfig->coverageHtmlFileView());
    }

    public function testClassViewForHtmlCodeCoverageReportCanBeDisabledFromXmlConfiguration(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage_html_classview.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->coverageHtmlClassView());
        $this->assertTrue($mergedConfig->coverageHtmlFileView());
    }

    public function testClassViewForHtmlCodeCoverageReportCanBeDisabledFromCli(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--without-class-view',
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->coverageHtmlClassView());
        $this->assertTrue($mergedConfig->coverageHtmlFileView());
    }

    public function testFileViewForHtmlCodeCoverageReportCanBeDisabledFromXmlConfiguration(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage_html_fileview.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->coverageHtmlClassView());
        $this->assertFalse($mergedConfig->coverageHtmlFileView());
    }

    public function testFileViewForHtmlCodeCoverageReportCanBeDisabledFromCli(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--without-file-view',
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->coverageHtmlClassView());
        $this->assertFalse($mergedConfig->coverageHtmlFileView());
    }

    public function testCoverageTargetingCanBeDisabledFromCli(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--disable-coverage-targeting',
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->disableCoverageTargeting());
    }

    public function testCoverageTargetingIsNotDisabledByDefault(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->disableCoverageTargeting());
    }

    public function testCoverageDriverIsCarriedOverFromXmlConfiguration(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage_driver.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->hasCoverageDriver());
        $this->assertSame('My\Custom\Driver', $mergedConfig->coverageDriver());
    }

    public function testNoCoverageShouldOnlyAffectXmlConfiguration(): void
    {
        $phpCoverage = uniqid('php_coverage_');
        $fromFile    = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $this->assertTrue($fromFile->codeCoverage()->hasClover());
        $this->assertTrue($fromFile->codeCoverage()->hasCobertura());
        $this->assertTrue($fromFile->codeCoverage()->hasCrap4j());
        $this->assertTrue($fromFile->codeCoverage()->hasHtml());
        $this->assertTrue($fromFile->codeCoverage()->hasJsonl());
        $this->assertTrue($fromFile->codeCoverage()->hasOpenClover());
        $this->assertTrue($fromFile->codeCoverage()->hasText());
        $this->assertTrue($fromFile->codeCoverage()->hasXml());

        $this->assertTrue($fromFile->codeCoverage()->hasPhp());
        $this->assertNotSame($phpCoverage, $fromFile->codeCoverage()->php()->target()->path());

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--no-coverage',
            '--coverage-php',
            $phpCoverage,
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->hasCoverageClover());
        $this->assertFalse($mergedConfig->hasCoverageCobertura());
        $this->assertFalse($mergedConfig->hasCoverageCrap4j());
        $this->assertFalse($mergedConfig->hasCoverageHtml());
        $this->assertFalse($mergedConfig->hasCoverageJsonl());
        $this->assertFalse($mergedConfig->hasCoverageOpenClover());
        $this->assertFalse($mergedConfig->hasCoverageText());
        $this->assertFalse($mergedConfig->hasCoverageXml());

        $this->assertTrue($mergedConfig->hasCoveragePhp());
        $this->assertSame($phpCoverage, $mergedConfig->coveragePhp());
    }

    public function testLoggingConfigurationIsCarriedOverFromXmlConfiguration(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_logging.xml');

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge(new Builder($this->createStub(Emitter::class))->fromParameters([]), $fromFile);

        $this->assertTrue($mergedConfig->hasLogfileTeamcity());
        $this->assertStringEndsWith('teamcity.txt', $mergedConfig->logfileTeamcity());

        $this->assertTrue($mergedConfig->hasLogfileJunit());
        $this->assertStringEndsWith('junit.xml', $mergedConfig->logfileJunit());

        $this->assertTrue($mergedConfig->hasLogfileOtr());
        $this->assertStringEndsWith('otr.xml', $mergedConfig->logfileOtr());
        $this->assertTrue($mergedConfig->includeGitInformationInOtrLogfile());

        $this->assertTrue($mergedConfig->hasLogfileTestdoxHtml());
        $this->assertStringEndsWith('testdox.html', $mergedConfig->logfileTestdoxHtml());

        $this->assertTrue($mergedConfig->hasLogfileTestdoxText());
        $this->assertStringEndsWith('testdox.txt', $mergedConfig->logfileTestdoxText());
    }

    public function testCodeCoverageReportsCanBeConfiguredFromCli(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--path-coverage',
            '--disable-coverage-ignore',
            '--coverage-clover',
            'clover.xml',
            '--coverage-cobertura',
            'cobertura.xml',
            '--coverage-crap4j',
            'crap4j.xml',
            '--coverage-html',
            'html',
            '--coverage-jsonl',
            'jsonl',
            '--coverage-openclover',
            'openclover.xml',
            '--coverage-text=coverage.txt',
            '--show-uncovered-for-coverage-text',
            '--only-summary-for-coverage-text',
            '--coverage-xml',
            'xml',
            '--exclude-source-from-xml-coverage',
            '--strict-coverage',
            '--require-coverage-contribution',
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->pathCoverage());
        $this->assertTrue($mergedConfig->disableCodeCoverageIgnore());
        $this->assertSame('clover.xml', $mergedConfig->coverageClover());
        $this->assertSame('cobertura.xml', $mergedConfig->coverageCobertura());
        $this->assertSame('crap4j.xml', $mergedConfig->coverageCrap4j());
        $this->assertSame('html', $mergedConfig->coverageHtml());
        $this->assertSame('jsonl', $mergedConfig->coverageJsonl());
        $this->assertSame('openclover.xml', $mergedConfig->coverageOpenClover());
        $this->assertSame('coverage.txt', $mergedConfig->coverageText());
        $this->assertTrue($mergedConfig->coverageTextShowUncoveredFiles());
        $this->assertTrue($mergedConfig->coverageTextShowOnlySummary());
        $this->assertSame('xml', $mergedConfig->coverageXml());
        $this->assertFalse($mergedConfig->coverageXmlIncludeSource());
        $this->assertTrue($mergedConfig->strictCoverage());
        $this->assertTrue($mergedConfig->requireCoverageContribution());
    }

    public function testMiscellaneousOptionsCanBeConfiguredFromCli(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--stderr',
            '--no-extensions',
            '--fail-on-phpunit-warning',
            '--display-phpunit-notices',
            '--colors=always',
            '--include-path',
            '.' . PATH_SEPARATOR,
            '--default-time-limit=-1',
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->outputToStandardErrorStream());
        $this->assertTrue($mergedConfig->noExtensions());
        $this->assertTrue($mergedConfig->failOnPhpunitWarning());
        $this->assertTrue($mergedConfig->displayDetailsOnPhpunitNotices());
        $this->assertTrue($mergedConfig->colors());
        $this->assertCount(1, $mergedConfig->php()->includePaths());
        $this->assertSame(0, $mergedConfig->defaultTimeLimit());
    }

    public function testTimeoutIsNotConfiguredByDefault(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');
        $fromCli  = new Builder($this->createStub(Emitter::class))->fromParameters([]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->hasTimeout());

        $this->expectException(TimeoutNotConfiguredException::class);

        $mergedConfig->timeout();
    }

    public function testTimeoutCanBeConfiguredFromCli(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');
        $fromCli  = new Builder($this->createStub(Emitter::class))->fromParameters(['--timeout=60']);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->hasTimeout());
        $this->assertSame(60, $mergedConfig->timeout());
    }

    public function testFileOutputIsNotRestrictedByDefault(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');
        $fromCli  = new Builder($this->createStub(Emitter::class))->fromParameters([]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->hasRestrictFileOutput());

        $this->expectException(NoFileOutputRestrictionException::class);

        $mergedConfig->restrictFileOutput();
    }

    public function testFileOutputRestrictionCanBeConfiguredFromCli(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');
        $fromCli  = new Builder($this->createStub(Emitter::class))->fromParameters(['--restrict-file-output=' . __DIR__]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->hasRestrictFileOutput());
        $this->assertSame(realpath(__DIR__), $mergedConfig->restrictFileOutput());
    }

    public function testOutputPathsAreResolvedWhenFileOutputIsRestricted(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_logging.xml');
        $fromCli  = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--restrict-file-output=' . __DIR__,
            '--log-junit=junit.xml',
            '--coverage-html=coverage',
            '--generate-baseline=baseline.xml',
            '--testdox-text=php://stdout',
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $cwd = getcwd();

        $this->assertNotFalse($cwd);

        $cwd = realpath($cwd);

        $this->assertSame($cwd . DIRECTORY_SEPARATOR . 'junit.xml', $mergedConfig->logfileJunit());
        $this->assertSame($cwd . DIRECTORY_SEPARATOR . 'coverage', $mergedConfig->coverageHtml());
        $this->assertSame($cwd . DIRECTORY_SEPARATOR . 'baseline.xml', $mergedConfig->generateBaseline());
        $this->assertSame('php://stdout', $mergedConfig->logfileTestdoxText());
        $this->assertSame(dirname(realpath(TEST_FILES_PATH . 'configuration_logging.xml')) . DIRECTORY_SEPARATOR . 'teamcity.txt', $mergedConfig->logfileTeamcity());
        $this->assertTrue(Filesystem::isAbsolutePath($mergedConfig->testRunHistoryFile()));
    }

    public function testOutputPathsAreNotResolvedWhenFileOutputIsNotRestricted(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');
        $fromCli  = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--log-junit=junit.xml',
            '--coverage-html=coverage',
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertSame('junit.xml', $mergedConfig->logfileJunit());
        $this->assertSame('coverage', $mergedConfig->coverageHtml());
    }

    public function testCacheDirectoryOutsideTheRestrictedDirectoryIsNotCreated(): void
    {
        $cacheDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-' . uniqid();

        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');
        $fromCli  = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--restrict-file-output=' . __DIR__,
            '--cache-directory=' . $cacheDirectory,
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertFalse(is_dir($cacheDirectory));
        $this->assertTrue($mergedConfig->hasCacheDirectory());
        $this->assertSame(realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . basename($cacheDirectory), $mergedConfig->cacheDirectory());
        $this->assertSame($mergedConfig->cacheDirectory() . DIRECTORY_SEPARATOR . 'test-run-history', $mergedConfig->testRunHistoryFile());
    }

    public function testCacheDirectoryInsideTheRestrictedDirectoryIsCreated(): void
    {
        $restrictedDirectory = realpath(sys_get_temp_dir());
        $cacheDirectory      = $restrictedDirectory . DIRECTORY_SEPARATOR . 'phpunit-' . uniqid();

        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');
        $fromCli  = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--restrict-file-output=' . $restrictedDirectory,
            '--cache-directory=' . $cacheDirectory,
        ]);

        try {
            $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

            $this->assertTrue(is_dir($cacheDirectory));
            $this->assertSame($cacheDirectory, $mergedConfig->cacheDirectory());
        } finally {
            rmdir($cacheDirectory);
        }
    }

    public function testInvalidRandomOrderSeedIsReplacedWithSmallestValidSeed(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_empty.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--order-by=random',
            '--random-order-seed=0',
        ]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertSame(1, $mergedConfig->randomOrderSeed());
    }

    public function testIncludePathsAreCarriedOverFromXmlConfiguration(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_edge_case_values.xml');

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge(new Builder($this->createStub(Emitter::class))->fromParameters([]), $fromFile);

        $this->assertCount(2, $mergedConfig->php()->includePaths());
    }

    public function testColorsCanBeEnabledFromXmlConfiguration(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration.colors.true.xml');

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge(new Builder($this->createStub(Emitter::class))->fromParameters([]), $fromFile);

        $this->assertTrue($mergedConfig->colors());
    }

    public function testThresholdsForHtmlCodeCoverageReportAreResetWhenTheyAreInconsistent(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage_html_custom.xml');

        $this->assertSame(90, $fromFile->codeCoverage()->html()->lowUpperBound());
        $this->assertSame(50, $fromFile->codeCoverage()->html()->highLowerBound());

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge(new Builder($this->createStub(Emitter::class))->fromParameters([]), $fromFile);

        $this->assertLessThanOrEqual(
            $mergedConfig->coverageHtmlHighLowerBound(),
            $mergedConfig->coverageHtmlLowUpperBound(),
        );
    }

    public function testCustomCssFileForHtmlCodeCoverageReportIsCarriedOverFromXmlConfiguration(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_codecoverage_html_custom.xml');

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge(new Builder($this->createStub(Emitter::class))->fromParameters([]), $fromFile);

        $this->assertTrue($mergedConfig->hasCoverageHtmlCustomCssFile());
        $this->assertStringEndsWith('custom.css', $mergedConfig->coverageHtmlCustomCssFile());
    }

    public function testWarningIsTriggeredWhenIssueTriggerIdentificationIsDisabledButNeeded(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration_source_without_issue_trigger_identification.xml');

        $this->assertTrue($fromFile->source()->ignoreSelfDeprecations());
        $this->assertFalse($fromFile->source()->identifyIssueTrigger());

        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->atLeastOnce())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->seal();

        $mergedConfig = new Merger($emitter)->merge(
            new Builder($this->createStub(Emitter::class))->fromParameters([]),
            $fromFile,
        );

        $this->assertTrue($mergedConfig->source()->ignoreSelfDeprecations());
    }

    public function testTestRunHistoryFileIsLocatedNextToTheScriptThatIsRunning(): void
    {
        $mergedConfig = $this->mergeWithPhpSelf(
            'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
        );

        $this->assertSame(
            dirname(realpath(__DIR__ . '/../../../../vendor/autoload.php')) . DIRECTORY_SEPARATOR . '.phpunit.result.cache',
            $mergedConfig->testRunHistoryFile(),
        );
    }

    public function testTestRunHistoryFileIsLocatedInTheCurrentWorkingDirectoryWhenTheScriptThatIsRunningIsUnknown(): void
    {
        $mergedConfig = $this->mergeWithPhpSelf(null);

        $this->assertSame('.phpunit.result.cache', $mergedConfig->testRunHistoryFile());
    }

    #[Group('regression')]
    #[Group('regression/6340')]
    public function testIssue6340(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration-issue-6340.xml');

        $this->assertTrue($fromFile->phpunit()->failOnPhpunitDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnPhpunitNotice());
        $this->assertTrue($fromFile->phpunit()->failOnDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnNotice());
        $this->assertTrue($fromFile->phpunit()->failOnWarning());
        $this->assertTrue($fromFile->phpunit()->failOnIncomplete());
        $this->assertTrue($fromFile->phpunit()->failOnSkipped());

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--do-not-fail-on-phpunit-deprecation',
            '--do-not-fail-on-phpunit-notice',
            '--do-not-fail-on-deprecation',
            '--do-not-fail-on-notice',
            '--do-not-fail-on-warning',
            '--do-not-fail-on-incomplete',
            '--do-not-fail-on-skipped',
        ]);

        $this->assertTrue($fromCli->doNotFailOnPhpunitDeprecation());
        $this->assertTrue($fromCli->doNotFailOnPhpunitNotice());
        $this->assertTrue($fromCli->doNotFailOnDeprecation());
        $this->assertTrue($fromCli->doNotFailOnNotice());
        $this->assertTrue($fromCli->doNotFailOnWarning());
        $this->assertTrue($fromCli->doNotFailOnIncomplete());
        $this->assertTrue($fromCli->doNotFailOnSkipped());

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->doNotFailOnPhpunitDeprecation());
        $this->assertTrue($mergedConfig->doNotFailOnPhpunitNotice());
        $this->assertTrue($mergedConfig->doNotFailOnDeprecation());
        $this->assertTrue($mergedConfig->doNotFailOnNotice());
        $this->assertTrue($mergedConfig->doNotFailOnWarning());
        $this->assertTrue($mergedConfig->doNotFailOnIncomplete());
        $this->assertTrue($mergedConfig->doNotFailOnSkipped());

        $this->assertFalse($mergedConfig->displayDetailsOnPhpunitDeprecations());
        $this->assertFalse($mergedConfig->displayDetailsOnPhpunitNotices());
        $this->assertFalse($mergedConfig->displayDetailsOnTestsThatTriggerDeprecations());
        $this->assertFalse($mergedConfig->displayDetailsOnTestsThatTriggerNotices());
        $this->assertFalse($mergedConfig->displayDetailsOnTestsThatTriggerWarnings());
        $this->assertFalse($mergedConfig->displayDetailsOnIncompleteTests());
        $this->assertFalse($mergedConfig->displayDetailsOnSkippedTests());
    }

    #[Group('issue-6484')]
    public function testFailOnDeprecationTriggerOptionsCanBeConfiguredUsingXmlConfigurationFile(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration-issue-6484.xml');

        $this->assertTrue($fromFile->phpunit()->failOnSelfDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnDirectDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnIndirectDeprecation());

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([]);

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->failOnSelfDeprecation());
        $this->assertTrue($mergedConfig->failOnDirectDeprecation());
        $this->assertTrue($mergedConfig->failOnIndirectDeprecation());

        $this->assertFalse($mergedConfig->doNotFailOnSelfDeprecation());
        $this->assertFalse($mergedConfig->doNotFailOnDirectDeprecation());
        $this->assertFalse($mergedConfig->doNotFailOnIndirectDeprecation());

        $this->assertTrue($mergedConfig->displayDetailsOnTestsThatTriggerDeprecations());
    }

    #[Group('issue-6484')]
    public function testFailOnDeprecationTriggerOptionsCanBeConfiguredUsingCommandLineOptions(): void
    {
        $fromFile = new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration-issue-6484.xml');

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--do-not-fail-on-self-deprecation',
            '--do-not-fail-on-direct-deprecation',
            '--do-not-fail-on-indirect-deprecation',
        ]);

        $this->assertTrue($fromCli->doNotFailOnSelfDeprecation());
        $this->assertTrue($fromCli->doNotFailOnDirectDeprecation());
        $this->assertTrue($fromCli->doNotFailOnIndirectDeprecation());

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->doNotFailOnSelfDeprecation());
        $this->assertTrue($mergedConfig->doNotFailOnDirectDeprecation());
        $this->assertTrue($mergedConfig->doNotFailOnIndirectDeprecation());

        $this->assertFalse($mergedConfig->displayDetailsOnTestsThatTriggerDeprecations());

        $fromCli = new Builder($this->createStub(Emitter::class))->fromParameters([
            '--fail-on-self-deprecation',
            '--fail-on-direct-deprecation',
            '--fail-on-indirect-deprecation',
        ]);

        $this->assertTrue($fromCli->failOnSelfDeprecation());
        $this->assertTrue($fromCli->failOnDirectDeprecation());
        $this->assertTrue($fromCli->failOnIndirectDeprecation());

        $mergedConfig = new Merger($this->createStub(Emitter::class))->merge($fromCli, new Loader($this->createStub(Emitter::class))->load(TEST_FILES_PATH . 'configuration-issue-6340.xml'));

        $this->assertTrue($mergedConfig->failOnSelfDeprecation());
        $this->assertTrue($mergedConfig->failOnDirectDeprecation());
        $this->assertTrue($mergedConfig->failOnIndirectDeprecation());

        $this->assertTrue($mergedConfig->displayDetailsOnTestsThatTriggerDeprecations());
    }

    private function mergeWithPhpSelf(?string $phpSelf): MergedConfiguration
    {
        $backup = null;

        if (isset($_SERVER['PHP_SELF'])) {
            $backup = $_SERVER['PHP_SELF'];
        }

        if ($phpSelf === null) {
            unset($_SERVER['PHP_SELF']);
        } else {
            $_SERVER['PHP_SELF'] = $phpSelf;
        }

        try {
            return new Merger($this->createStub(Emitter::class))->merge(
                new Builder($this->createStub(Emitter::class))->fromParameters([]),
                DefaultConfiguration::create(),
            );
        } finally {
            if ($backup === null) {
                unset($_SERVER['PHP_SELF']);
            } else {
                $_SERVER['PHP_SELF'] = $backup;
            }
        }
    }
}
