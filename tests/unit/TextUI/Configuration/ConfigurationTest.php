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

use const DIRECTORY_SEPARATOR;
use function is_dir;
use function realpath;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder as CliBuilder;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Loader;

#[CoversClass(Configuration::class)]
#[Medium]
#[Group('textui')]
#[Group('textui/configuration')]
final class ConfigurationTest extends TestCase
{
    public function testTestFilesFileThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasTestFilesFile());

        $this->expectException(NoTestFilesFileException::class);

        $configuration->testFilesFile();
    }

    public function testConfigurationFileThrowsWhenNotLoadedFromFile(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasConfigurationFile());

        $this->expectException(NoConfigurationFileException::class);

        $configuration->configurationFile();
    }

    public function testBootstrapThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasBootstrap());

        $this->expectException(NoBootstrapException::class);

        $configuration->bootstrap();
    }

    public function testCacheDirectoryThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCacheDirectory());

        $this->expectException(NoCacheDirectoryException::class);

        $configuration->cacheDirectory();
    }

    public function testCoverageCacheDirectoryThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageCacheDirectory());

        $this->expectException(NoCoverageCacheDirectoryException::class);

        $configuration->coverageCacheDirectory();
    }

    public function testCoverageDriverThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageDriver());

        $this->expectException(CodeCoverageDriverNotConfiguredException::class);

        $configuration->coverageDriver();
    }

    public function testReturnsConfiguredCoverageDriver(): void
    {
        $configuration = $this->configurationFromXml('configuration_codecoverage_driver.xml');

        $this->assertTrue($configuration->hasCoverageDriver());
        $this->assertSame('My\Custom\Driver', $configuration->coverageDriver());
    }

    public function testCoverageCloverThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageClover());

        $this->expectException(CodeCoverageReportNotConfiguredException::class);

        $configuration->coverageClover();
    }

    public function testCoverageCoberturaThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageCobertura());

        $this->expectException(CodeCoverageReportNotConfiguredException::class);

        $configuration->coverageCobertura();
    }

    public function testCoverageCrap4jThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageCrap4j());

        $this->expectException(CodeCoverageReportNotConfiguredException::class);

        $configuration->coverageCrap4j();
    }

    public function testCoverageHtmlThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageHtml());

        $this->expectException(CodeCoverageReportNotConfiguredException::class);

        $configuration->coverageHtml();
    }

    public function testCoverageHtmlCustomCssFileThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageHtmlCustomCssFile());

        $this->expectException(NoCustomCssFileException::class);

        $configuration->coverageHtmlCustomCssFile();
    }

    public function testCoverageOpenCloverThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageOpenClover());

        $this->expectException(CodeCoverageReportNotConfiguredException::class);

        $configuration->coverageOpenClover();
    }

    public function testCoveragePhpThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoveragePhp());

        $this->expectException(CodeCoverageReportNotConfiguredException::class);

        $configuration->coveragePhp();
    }

    public function testCoverageTextThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageText());

        $this->expectException(CodeCoverageReportNotConfiguredException::class);

        $configuration->coverageText();
    }

    public function testCoverageXmlThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasCoverageXml());

        $this->expectException(CodeCoverageReportNotConfiguredException::class);

        $configuration->coverageXml();
    }

    public function testPharExtensionDirectoryThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasPharExtensionDirectory());

        $this->expectException(NoPharExtensionDirectoryException::class);

        $configuration->pharExtensionDirectory();
    }

    public function testLogfileTeamcityThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasLogfileTeamcity());

        $this->expectException(LoggingNotConfiguredException::class);

        $configuration->logfileTeamcity();
    }

    public function testLogfileJunitThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasLogfileJunit());

        $this->expectException(LoggingNotConfiguredException::class);

        $configuration->logfileJunit();
    }

    public function testLogfileOtrThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasLogfileOtr());

        $this->expectException(LoggingNotConfiguredException::class);

        $configuration->logfileOtr();
    }

    public function testLogfileTestdoxHtmlThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasLogfileTestdoxHtml());

        $this->expectException(LoggingNotConfiguredException::class);

        $configuration->logfileTestdoxHtml();
    }

    public function testLogfileTestdoxTextThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasLogfileTestdoxText());

        $this->expectException(LoggingNotConfiguredException::class);

        $configuration->logfileTestdoxText();
    }

    public function testLogEventsTextThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasLogEventsText());

        $this->expectException(LoggingNotConfiguredException::class);

        $configuration->logEventsText();
    }

    public function testLogEventsVerboseTextThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasLogEventsVerboseText());

        $this->expectException(LoggingNotConfiguredException::class);

        $configuration->logEventsVerboseText();
    }

    public function testTestsCoveringThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasTestsCovering());

        $this->expectException(FilterNotConfiguredException::class);

        $configuration->testsCovering();
    }

    public function testTestsUsingThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasTestsUsing());

        $this->expectException(FilterNotConfiguredException::class);

        $configuration->testsUsing();
    }

    public function testTestsRequiringPhpExtensionThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasTestsRequiringPhpExtension());

        $this->expectException(FilterNotConfiguredException::class);

        $configuration->testsRequiringPhpExtension();
    }

    public function testFilterThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasFilter());

        $this->expectException(FilterNotConfiguredException::class);

        $configuration->filter();
    }

    public function testExcludeFilterThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasExcludeFilter());

        $this->expectException(FilterNotConfiguredException::class);

        $configuration->excludeFilter();
    }

    public function testTestIdFilterFileThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasTestIdFilterFile());

        $this->expectException(FilterNotConfiguredException::class);

        $configuration->testIdFilterFile();
    }

    public function testTestIdFilterThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasTestIdFilter());

        $this->expectException(FilterNotConfiguredException::class);

        $configuration->testIdFilter();
    }

    public function testGroupsThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasGroups());

        $this->expectException(FilterNotConfiguredException::class);

        $configuration->groups();
    }

    public function testExcludeGroupsThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasExcludeGroups());

        $this->expectException(FilterNotConfiguredException::class);

        $configuration->excludeGroups();
    }

    public function testSpecificDeprecationToStopOnThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasSpecificDeprecationToStopOn());

        $this->expectException(SpecificDeprecationToStopOnNotConfiguredException::class);

        $configuration->specificDeprecationToStopOn();
    }

    public function testDefaultTestSuiteThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasDefaultTestSuite());

        $this->expectException(NoDefaultTestSuiteException::class);

        $configuration->defaultTestSuite();
    }

    public function testGenerateBaselineThrowsWhenNotConfigured(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->hasGenerateBaseline());

        $this->expectException(NoBaselineException::class);

        $configuration->generateBaseline();
    }

    public function testReturnsDefaultValuesForCoverageHtmlReportThresholds(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertGreaterThanOrEqual(0, $configuration->coverageHtmlLowUpperBound());
        $this->assertGreaterThanOrEqual(0, $configuration->coverageHtmlHighLowerBound());
        $this->assertSame(30, $configuration->coverageCrap4jThreshold());
    }

    public function testReturnsDefaultValuesForCoverageHtmlReportColors(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertNotEmpty($configuration->coverageHtmlColorSuccessLow());
        $this->assertNotEmpty($configuration->coverageHtmlColorSuccessLowDark());
        $this->assertNotEmpty($configuration->coverageHtmlColorSuccessMedium());
        $this->assertNotEmpty($configuration->coverageHtmlColorSuccessMediumDark());
        $this->assertNotEmpty($configuration->coverageHtmlColorSuccessHigh());
        $this->assertNotEmpty($configuration->coverageHtmlColorSuccessHighDark());
        $this->assertNotEmpty($configuration->coverageHtmlColorSuccessBar());
        $this->assertNotEmpty($configuration->coverageHtmlColorSuccessBarDark());
        $this->assertNotEmpty($configuration->coverageHtmlColorWarning());
        $this->assertNotEmpty($configuration->coverageHtmlColorWarningDark());
        $this->assertNotEmpty($configuration->coverageHtmlColorWarningBar());
        $this->assertNotEmpty($configuration->coverageHtmlColorWarningBarDark());
        $this->assertNotEmpty($configuration->coverageHtmlColorDanger());
        $this->assertNotEmpty($configuration->coverageHtmlColorDangerDark());
        $this->assertNotEmpty($configuration->coverageHtmlColorDangerBar());
        $this->assertNotEmpty($configuration->coverageHtmlColorDangerBarDark());
        $this->assertNotEmpty($configuration->coverageHtmlColorBreadcrumbs());
        $this->assertNotEmpty($configuration->coverageHtmlColorBreadcrumbsDark());
    }

    public function testReturnsDefaultValuesForTimeouts(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertGreaterThan(0, $configuration->timeoutForSmallTests());
        $this->assertGreaterThan(0, $configuration->timeoutForMediumTests());
        $this->assertGreaterThan(0, $configuration->timeoutForLargeTests());
    }

    public function testCoverageXmlIncludeSourceDefaultsToTrue(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertTrue($configuration->coverageXmlIncludeSource());
    }

    public function testIncludeGitInformationDefaultsToFalse(): void
    {
        $configuration = $this->defaultConfiguration();

        $this->assertFalse($configuration->includeGitInformation());
    }

    public function testReturnsConfiguredCoverageReports(): void
    {
        $configuration = $this->configurationFromXml('configuration_full.xml');

        $this->assertTrue($configuration->hasConfigurationFile());
        $this->assertSame(realpath(TEST_FILES_PATH . 'configuration_full.xml'), $configuration->configurationFile());

        $this->assertTrue($configuration->hasCoverageClover());
        $this->assertStringEndsWith('clover.xml', $configuration->coverageClover());

        $this->assertTrue($configuration->hasCoverageCobertura());
        $this->assertStringEndsWith('cobertura.xml', $configuration->coverageCobertura());

        $this->assertTrue($configuration->hasCoverageCrap4j());
        $this->assertStringEndsWith('crap4j.xml', $configuration->coverageCrap4j());
        $this->assertSame(42, $configuration->coverageCrap4jThreshold());

        $this->assertTrue($configuration->hasCoverageHtml());
        $this->assertStringEndsWith('coverage', $configuration->coverageHtml());
        $this->assertSame(50, $configuration->coverageHtmlLowUpperBound());
        $this->assertSame(90, $configuration->coverageHtmlHighLowerBound());

        $this->assertTrue($configuration->hasCoverageHtmlCustomCssFile());
        $this->assertStringEndsWith('custom.css', $configuration->coverageHtmlCustomCssFile());

        $this->assertTrue($configuration->hasCoverageOpenClover());
        $this->assertStringEndsWith('openclover.xml', $configuration->coverageOpenClover());

        $this->assertTrue($configuration->hasCoveragePhp());
        $this->assertStringEndsWith('coverage.php', $configuration->coveragePhp());

        $this->assertTrue($configuration->hasCoverageText());
        $this->assertStringEndsWith('coverage.txt', $configuration->coverageText());

        $this->assertTrue($configuration->hasCoverageXml());
        $this->assertStringEndsWith('coverage-xml', $configuration->coverageXml());
        $this->assertTrue($configuration->coverageXmlIncludeSource());
    }

    public function testReturnsConfiguredLogfiles(): void
    {
        $configuration = $this->configurationFromXml('configuration_full.xml');

        $this->assertTrue($configuration->hasLogfileTeamcity());
        $this->assertStringEndsWith('teamcity.txt', $configuration->logfileTeamcity());

        $this->assertTrue($configuration->hasLogfileJunit());
        $this->assertStringEndsWith('junit.xml', $configuration->logfileJunit());

        $this->assertTrue($configuration->hasLogfileOtr());
        $this->assertStringEndsWith('otr.xml', $configuration->logfileOtr());

        $this->assertTrue($configuration->hasLogfileTestdoxHtml());
        $this->assertStringEndsWith('testdox.html', $configuration->logfileTestdoxHtml());

        $this->assertTrue($configuration->hasLogfileTestdoxText());
        $this->assertStringEndsWith('testdox.txt', $configuration->logfileTestdoxText());
    }

    public function testReturnsConfiguredDefaultTestSuite(): void
    {
        $configuration = $this->configurationFromXml('configuration_full.xml');

        $this->assertTrue($configuration->hasDefaultTestSuite());
        $this->assertSame('default', $configuration->defaultTestSuite());
    }

    public function testReturnsConfiguredPharExtensionDirectory(): void
    {
        $configuration = $this->configurationFromXml('configuration_full.xml');

        $this->assertTrue($configuration->hasPharExtensionDirectory());
        $this->assertStringEndsWith('extensions', $configuration->pharExtensionDirectory());
    }

    public function testReturnsConfiguredTimeouts(): void
    {
        $configuration = $this->configurationFromXml('configuration_full.xml');

        $this->assertSame(2, $configuration->timeoutForSmallTests());
        $this->assertSame(20, $configuration->timeoutForMediumTests());
        $this->assertSame(120, $configuration->timeoutForLargeTests());
    }

    public function testReturnsValuesProvidedViaCommandLineArguments(): void
    {
        $configuration = (new Merger)->merge(
            (new CliBuilder)->fromParameters([
                '--test-files-file', 'tests.txt',
                '--bootstrap', 'bootstrap.php',
                '--filter', 'foo',
                '--exclude-filter', 'bar',
                '--test-id-filter-file', 'ids.txt',
                '--run-test-id', 'My::test',
                '--group', 'g1',
                '--exclude-group', 'g2',
                '--covers', 'Foo',
                '--uses', 'Bar',
                '--requires-php-extension', 'pcre',
                '--stop-on-deprecation=Some specific deprecation',
                '--include-git-information',
            ]),
            DefaultConfiguration::create(),
        );

        $this->assertTrue($configuration->hasTestFilesFile());
        $this->assertSame('tests.txt', $configuration->testFilesFile());

        $this->assertTrue($configuration->hasBootstrap());
        $this->assertSame('bootstrap.php', $configuration->bootstrap());

        $this->assertTrue($configuration->hasFilter());
        $this->assertSame('foo', $configuration->filter());

        $this->assertTrue($configuration->hasExcludeFilter());
        $this->assertSame('bar', $configuration->excludeFilter());

        $this->assertTrue($configuration->hasTestIdFilterFile());
        $this->assertSame('ids.txt', $configuration->testIdFilterFile());

        $this->assertTrue($configuration->hasTestIdFilter());
        $this->assertSame('My::test', $configuration->testIdFilter());

        $this->assertTrue($configuration->hasGroups());
        $this->assertSame(['g1'], $configuration->groups());

        $this->assertTrue($configuration->hasExcludeGroups());
        $this->assertSame(['g2'], $configuration->excludeGroups());

        $this->assertTrue($configuration->hasTestsCovering());
        $this->assertSame(['foo'], $configuration->testsCovering());

        $this->assertTrue($configuration->hasTestsUsing());
        $this->assertSame(['bar'], $configuration->testsUsing());

        $this->assertTrue($configuration->hasTestsRequiringPhpExtension());
        $this->assertSame(['pcre'], $configuration->testsRequiringPhpExtension());

        $this->assertTrue($configuration->hasSpecificDeprecationToStopOn());
        $this->assertSame('Some specific deprecation', $configuration->specificDeprecationToStopOn());

        $this->assertTrue($configuration->includeGitInformation());
    }

    public function testReturnsCacheDirectoryProvidedViaCommandLineArguments(): void
    {
        $cacheDirectory = sys_get_temp_dir() . '/' . uniqid('phpunit-test-cache-');

        try {
            $configuration = (new Merger)->merge(
                (new CliBuilder)->fromParameters(['--cache-directory', $cacheDirectory]),
                DefaultConfiguration::create(),
            );

            $this->assertTrue($configuration->hasCacheDirectory());
            $this->assertSame(realpath($cacheDirectory), $configuration->cacheDirectory());

            $this->assertTrue($configuration->hasCoverageCacheDirectory());
            $this->assertSame(
                realpath($cacheDirectory) . DIRECTORY_SEPARATOR . 'code-coverage',
                $configuration->coverageCacheDirectory(),
            );
        } finally {
            if (is_dir($cacheDirectory)) {
                @rmdir($cacheDirectory);
            }
        }
    }

    public function testReturnsBaselineProvidedViaCommandLineArguments(): void
    {
        $configuration = (new Merger)->merge(
            (new CliBuilder)->fromParameters(['--generate-baseline', 'baseline.xml']),
            DefaultConfiguration::create(),
        );

        $this->assertTrue($configuration->hasGenerateBaseline());
        $this->assertStringEndsWith('baseline.xml', $configuration->generateBaseline());
    }

    private function defaultConfiguration(): Configuration
    {
        return (new Merger)->merge(
            (new CliBuilder)->fromParameters([]),
            DefaultConfiguration::create(),
        );
    }

    private function configurationFromXml(string $filename): Configuration
    {
        return (new Merger)->merge(
            (new CliBuilder)->fromParameters([]),
            (new Loader)->load(TEST_FILES_PATH . $filename),
        );
    }
}
