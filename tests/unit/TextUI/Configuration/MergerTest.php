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
use function dirname;
use function realpath;
use function uniqid;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\Configuration\Configuration as MergedConfiguration;
use PHPUnit\TextUI\Configuration\Merger;
use ReflectionProperty;

#[CoversClass(Merger::class)]
#[Medium]
#[Group('textui')]
#[Group('textui/configuration')]
final class MergerTest extends TestCase
{
    public function testNoLoggingShouldOnlyAffectXmlConfiguration(): void
    {
        $junitLog = uniqid('junit_log_');
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_logging.xml');

        $this->assertTrue($fromFile->logging()->hasTeamCity());
        $this->assertTrue($fromFile->logging()->hasTestDoxHtml());
        $this->assertTrue($fromFile->logging()->hasTestDoxText());

        $this->assertTrue($fromFile->logging()->hasJunit());
        $this->assertNotSame($junitLog, $fromFile->logging()->junit()->target()->path());

        $fromCli = (new Builder)->fromParameters([
            '--no-logging',
            '--log-junit',
            $junitLog,
        ]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->hasLogfileTeamcity());
        $this->assertFalse($mergedConfig->hasLogfileTestdoxHtml());
        $this->assertFalse($mergedConfig->hasLogfileTestdoxText());

        $this->assertTrue($mergedConfig->hasLogfileJunit());
        $this->assertSame($junitLog, $mergedConfig->logfileJunit());
    }

    public function testBranchCoverageCanBeEnabledFromCli(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $this->assertFalse($fromFile->codeCoverage()->branchCoverage());

        $fromCli = (new Builder)->fromParameters([
            '--branch-coverage',
        ]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->branchCoverage());
    }

    public function testClassViewAndFileViewForHtmlCodeCoverageReportAreEnabledByDefault(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = (new Builder)->fromParameters([]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->coverageHtmlClassView());
        $this->assertTrue($mergedConfig->coverageHtmlFileView());
    }

    public function testClassViewForHtmlCodeCoverageReportCanBeDisabledFromXmlConfiguration(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage_html_classview.xml');

        $fromCli = (new Builder)->fromParameters([]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->coverageHtmlClassView());
        $this->assertTrue($mergedConfig->coverageHtmlFileView());
    }

    public function testClassViewForHtmlCodeCoverageReportCanBeDisabledFromCli(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = (new Builder)->fromParameters([
            '--without-class-view',
        ]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->coverageHtmlClassView());
        $this->assertTrue($mergedConfig->coverageHtmlFileView());
    }

    public function testFileViewForHtmlCodeCoverageReportCanBeDisabledFromXmlConfiguration(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage_html_fileview.xml');

        $fromCli = (new Builder)->fromParameters([]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->coverageHtmlClassView());
        $this->assertFalse($mergedConfig->coverageHtmlFileView());
    }

    public function testFileViewForHtmlCodeCoverageReportCanBeDisabledFromCli(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = (new Builder)->fromParameters([
            '--without-file-view',
        ]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->coverageHtmlClassView());
        $this->assertFalse($mergedConfig->coverageHtmlFileView());
    }

    public function testCoverageTargetingCanBeDisabledFromCli(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = (new Builder)->fromParameters([
            '--disable-coverage-targeting',
        ]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->disableCoverageTargeting());
    }

    public function testCoverageTargetingIsNotDisabledByDefault(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $fromCli = (new Builder)->fromParameters([]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->disableCoverageTargeting());
    }

    public function testCoverageDriverIsCarriedOverFromXmlConfiguration(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage_driver.xml');

        $fromCli = (new Builder)->fromParameters([]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->hasCoverageDriver());
        $this->assertSame('My\Custom\Driver', $mergedConfig->coverageDriver());
    }

    public function testNoCoverageShouldOnlyAffectXmlConfiguration(): void
    {
        $phpCoverage = uniqid('php_coverage_');
        $fromFile    = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $this->assertTrue($fromFile->codeCoverage()->hasClover());
        $this->assertTrue($fromFile->codeCoverage()->hasCobertura());
        $this->assertTrue($fromFile->codeCoverage()->hasCrap4j());
        $this->assertTrue($fromFile->codeCoverage()->hasHtml());
        $this->assertTrue($fromFile->codeCoverage()->hasOpenClover());
        $this->assertTrue($fromFile->codeCoverage()->hasText());
        $this->assertTrue($fromFile->codeCoverage()->hasXml());

        $this->assertTrue($fromFile->codeCoverage()->hasPhp());
        $this->assertNotSame($phpCoverage, $fromFile->codeCoverage()->php()->target()->path());

        $fromCli = (new Builder)->fromParameters([
            '--no-coverage',
            '--coverage-php',
            $phpCoverage,
        ]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->hasCoverageClover());
        $this->assertFalse($mergedConfig->hasCoverageCobertura());
        $this->assertFalse($mergedConfig->hasCoverageCrap4j());
        $this->assertFalse($mergedConfig->hasCoverageHtml());
        $this->assertFalse($mergedConfig->hasCoverageOpenClover());
        $this->assertFalse($mergedConfig->hasCoverageText());
        $this->assertFalse($mergedConfig->hasCoverageXml());

        $this->assertTrue($mergedConfig->hasCoveragePhp());
        $this->assertSame($phpCoverage, $mergedConfig->coveragePhp());
    }

    public function testLoggingConfigurationIsCarriedOverFromXmlConfiguration(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_logging.xml');

        $mergedConfig = (new Merger)->merge((new Builder)->fromParameters([]), $fromFile);

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
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_empty.xml');

        $fromCli = (new Builder)->fromParameters([
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

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->pathCoverage());
        $this->assertTrue($mergedConfig->disableCodeCoverageIgnore());
        $this->assertSame('clover.xml', $mergedConfig->coverageClover());
        $this->assertSame('cobertura.xml', $mergedConfig->coverageCobertura());
        $this->assertSame('crap4j.xml', $mergedConfig->coverageCrap4j());
        $this->assertSame('html', $mergedConfig->coverageHtml());
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
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_empty.xml');

        $fromCli = (new Builder)->fromParameters([
            '--stderr',
            '--no-extensions',
            '--fail-on-phpunit-warning',
            '--display-phpunit-notices',
            '--colors=always',
            '--include-path',
            '.' . PATH_SEPARATOR,
            '--default-time-limit=-1',
        ]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->outputToStandardErrorStream());
        $this->assertTrue($mergedConfig->noExtensions());
        $this->assertTrue($mergedConfig->failOnPhpunitWarning());
        $this->assertTrue($mergedConfig->displayDetailsOnPhpunitNotices());
        $this->assertTrue($mergedConfig->colors());
        $this->assertCount(1, $mergedConfig->php()->includePaths());
        $this->assertSame(0, $mergedConfig->defaultTimeLimit());
    }

    public function testInvalidRandomOrderSeedIsReplacedWithSmallestValidSeed(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_empty.xml');

        $fromCli = (new Builder)->fromParameters([
            '--order-by=random',
            '--random-order-seed=0',
        ]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertSame(1, $mergedConfig->randomOrderSeed());
    }

    public function testIncludePathsAreCarriedOverFromXmlConfiguration(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_edge_case_values.xml');

        $mergedConfig = (new Merger)->merge((new Builder)->fromParameters([]), $fromFile);

        $this->assertCount(2, $mergedConfig->php()->includePaths());
    }

    public function testColorsCanBeEnabledFromXmlConfiguration(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration.colors.true.xml');

        $mergedConfig = (new Merger)->merge((new Builder)->fromParameters([]), $fromFile);

        $this->assertTrue($mergedConfig->colors());
    }

    public function testThresholdsForHtmlCodeCoverageReportAreResetWhenTheyAreInconsistent(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage_html_custom.xml');

        $this->assertSame(90, $fromFile->codeCoverage()->html()->lowUpperBound());
        $this->assertSame(50, $fromFile->codeCoverage()->html()->highLowerBound());

        $mergedConfig = (new Merger)->merge((new Builder)->fromParameters([]), $fromFile);

        $this->assertLessThanOrEqual(
            $mergedConfig->coverageHtmlHighLowerBound(),
            $mergedConfig->coverageHtmlLowUpperBound(),
        );
    }

    public function testCustomCssFileForHtmlCodeCoverageReportIsCarriedOverFromXmlConfiguration(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage_html_custom.xml');

        $mergedConfig = (new Merger)->merge((new Builder)->fromParameters([]), $fromFile);

        $this->assertTrue($mergedConfig->hasCoverageHtmlCustomCssFile());
        $this->assertStringEndsWith('custom.css', $mergedConfig->coverageHtmlCustomCssFile());
    }

    public function testWarningIsTriggeredWhenIssueTriggerIdentificationIsDisabledButNeeded(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_source_without_issue_trigger_identification.xml');

        $this->assertTrue($fromFile->source()->ignoreSelfDeprecations());
        $this->assertFalse($fromFile->source()->identifyIssueTrigger());

        $mergedConfig = $this->mergeWithThrowAwayEventFacade(
            (new Builder)->fromParameters([]),
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
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration-issue-6340.xml');

        $this->assertTrue($fromFile->phpunit()->failOnPhpunitDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnPhpunitNotice());
        $this->assertTrue($fromFile->phpunit()->failOnDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnNotice());
        $this->assertTrue($fromFile->phpunit()->failOnWarning());
        $this->assertTrue($fromFile->phpunit()->failOnIncomplete());
        $this->assertTrue($fromFile->phpunit()->failOnSkipped());

        $fromCli = (new Builder)->fromParameters([
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

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

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
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration-issue-6484.xml');

        $this->assertTrue($fromFile->phpunit()->failOnSelfDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnDirectDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnIndirectDeprecation());

        $fromCli = (new Builder)->fromParameters([]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

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
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration-issue-6484.xml');

        $fromCli = (new Builder)->fromParameters([
            '--do-not-fail-on-self-deprecation',
            '--do-not-fail-on-direct-deprecation',
            '--do-not-fail-on-indirect-deprecation',
        ]);

        $this->assertTrue($fromCli->doNotFailOnSelfDeprecation());
        $this->assertTrue($fromCli->doNotFailOnDirectDeprecation());
        $this->assertTrue($fromCli->doNotFailOnIndirectDeprecation());

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->doNotFailOnSelfDeprecation());
        $this->assertTrue($mergedConfig->doNotFailOnDirectDeprecation());
        $this->assertTrue($mergedConfig->doNotFailOnIndirectDeprecation());

        $this->assertFalse($mergedConfig->displayDetailsOnTestsThatTriggerDeprecations());

        $fromCli = (new Builder)->fromParameters([
            '--fail-on-self-deprecation',
            '--fail-on-direct-deprecation',
            '--fail-on-indirect-deprecation',
        ]);

        $this->assertTrue($fromCli->failOnSelfDeprecation());
        $this->assertTrue($fromCli->failOnDirectDeprecation());
        $this->assertTrue($fromCli->failOnIndirectDeprecation());

        $mergedConfig = (new Merger)->merge($fromCli, (new Loader)->load(TEST_FILES_PATH . 'configuration-issue-6340.xml'));

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
            return (new Merger)->merge(
                (new Builder)->fromParameters([]),
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

    /*
     * Merger emits test runner warnings for configurations that are
     * inconsistent. These must not end up in the result of the test run that
     * exercises Merger, so they are emitted into a throw-away event facade
     * that is never forwarded.
     */
    private function mergeWithThrowAwayEventFacade(CliConfiguration $cliConfiguration, Configuration $xmlConfiguration): MergedConfiguration
    {
        $property = new ReflectionProperty(EventFacade::class, 'instance');
        $facade   = $property->getValue();

        $property->setValue(null, new EventFacade);

        try {
            return (new Merger)->merge($cliConfiguration, $xmlConfiguration);
        } finally {
            $property->setValue(null, $facade);
        }
    }
}
