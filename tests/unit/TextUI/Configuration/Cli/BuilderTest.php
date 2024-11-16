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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestSuiteSorter;

#[CoversClass(Builder::class)]
#[CoversClass(Configuration::class)]
#[Small]
#[TestDox('CLI Options Parser')]
final class BuilderTest extends TestCase
{
    #[TestDox('--colors')]
    public function testColorsImplicitAuto(): void
    {
        $configuration = (new Builder)->fromParameters(['--colors']);

        $this->assertTrue($configuration->hasColors());
        $this->assertSame('auto', $configuration->colors());
    }

    #[TestDox('--colors=auto')]
    public function testColorsExplicitAuto(): void
    {
        $configuration = (new Builder)->fromParameters(['--colors=auto']);

        $this->assertTrue($configuration->hasColors());
        $this->assertSame('auto', $configuration->colors());
    }

    #[TestDox('--colors=always')]
    public function testColorsAlways(): void
    {
        $configuration = (new Builder)->fromParameters(['--colors=always']);

        $this->assertTrue($configuration->hasColors());
        $this->assertSame('always', $configuration->colors());
    }

    #[TestDox('--colors=never')]
    public function testColorsNever(): void
    {
        $configuration = (new Builder)->fromParameters(['--colors=never']);

        $this->assertTrue($configuration->hasColors());
        $this->assertSame('never', $configuration->colors());
    }

    public function testColorsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasColors());

        $this->expectException(Exception::class);

        $configuration->colors();
    }

    #[TestDox('--bootstrap script.php')]
    public function testBootstrap(): void
    {
        $configuration = (new Builder)->fromParameters(['--bootstrap', 'script.php']);

        $this->assertTrue($configuration->hasBootstrap());
        $this->assertSame('script.php', $configuration->bootstrap());
    }

    public function testBootstrapMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasBootstrap());

        $this->expectException(Exception::class);

        $configuration->bootstrap();
    }

    #[TestDox('--cache-directory directory')]
    public function testCacheDirectory(): void
    {
        $configuration = (new Builder)->fromParameters(['--cache-directory', 'directory']);

        $this->assertTrue($configuration->hasCacheDirectory());
        $this->assertSame('directory', $configuration->cacheDirectory());
    }

    public function testCacheDirectoryMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCacheDirectory());

        $this->expectException(Exception::class);

        $configuration->cacheDirectory();
    }

    #[TestDox('--cache-result')]
    public function testCacheResult(): void
    {
        $configuration = (new Builder)->fromParameters(['--cache-result']);

        $this->assertTrue($configuration->hasCacheResult());
        $this->assertTrue($configuration->cacheResult());
    }

    #[TestDox('--do-not-cache-result')]
    public function testDoNotCacheResult(): void
    {
        $configuration = (new Builder)->fromParameters(['--do-not-cache-result']);

        $this->assertTrue($configuration->hasCacheResult());
        $this->assertFalse($configuration->cacheResult());
    }

    public function testCacheResultMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCacheResult());

        $this->expectException(Exception::class);

        $configuration->cacheResult();
    }

    #[TestDox('--columns <n>')]
    public function testColumnsNumber(): void
    {
        $configuration = (new Builder)->fromParameters(['--columns', '100']);

        $this->assertTrue($configuration->hasColumns());
        $this->assertSame(100, $configuration->columns());
    }

    #[TestDox('--columns max')]
    public function testColumnsMax(): void
    {
        $configuration = (new Builder)->fromParameters(['--columns', 'max']);

        $this->assertTrue($configuration->hasColumns());
        $this->assertSame('max', $configuration->columns());
    }

    public function testColumnsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasColumns());

        $this->expectException(Exception::class);

        $configuration->columns();
    }

    #[TestDox('-c file')]
    public function testConfigurationShort(): void
    {
        $configuration = (new Builder)->fromParameters(['-c', 'file']);

        $this->assertTrue($configuration->hasConfigurationFile());
        $this->assertSame('file', $configuration->configurationFile());
    }

    #[TestDox('--configuration file')]
    public function testConfiguration(): void
    {
        $configuration = (new Builder)->fromParameters(['--configuration', 'file']);

        $this->assertTrue($configuration->hasConfigurationFile());
        $this->assertSame('file', $configuration->configurationFile());
    }

    public function testConfigurationMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasConfigurationFile());

        $this->expectException(Exception::class);

        $configuration->configurationFile();
    }

    #[TestDox('--warm-coverage-cache')]
    public function testWarmCoverageCache(): void
    {
        $configuration = (new Builder)->fromParameters(['--warm-coverage-cache']);

        $this->assertTrue($configuration->warmCoverageCache());
    }

    public function testWarmCoverageCacheMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->warmCoverageCache());
    }

    #[TestDox('--coverage-clover file')]
    public function testCoverageClover(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-clover', 'file']);

        $this->assertTrue($configuration->hasCoverageClover());
        $this->assertSame('file', $configuration->coverageClover());
    }

    public function testCoverageCloverMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoverageClover());

        $this->expectException(Exception::class);

        $configuration->coverageClover();
    }

    #[TestDox('--coverage-cobertura file')]
    public function testCoverageCobertura(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-cobertura', 'file']);

        $this->assertTrue($configuration->hasCoverageCobertura());
        $this->assertSame('file', $configuration->coverageCobertura());
    }

    public function testCoverageCoberturaMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoverageCobertura());

        $this->expectException(Exception::class);

        $configuration->coverageCobertura();
    }

    #[TestDox('--coverage-crap4j file')]
    public function testCoverageCrap4j(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-crap4j', 'file']);

        $this->assertTrue($configuration->hasCoverageCrap4J());
        $this->assertSame('file', $configuration->coverageCrap4J());
    }

    public function testCoverageCrap4jMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoverageCrap4J());

        $this->expectException(Exception::class);

        $configuration->coverageCrap4J();
    }

    #[TestDox('--coverage-html directory')]
    public function testCoverageHtml(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-html', 'directory']);

        $this->assertTrue($configuration->hasCoverageHtml());
        $this->assertSame('directory', $configuration->coverageHtml());
    }

    public function testCoverageHtmlMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoverageHtml());

        $this->expectException(Exception::class);

        $configuration->coverageHtml();
    }

    #[TestDox('--coverage-php file')]
    public function testCoveragePhp(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-php', 'file']);

        $this->assertTrue($configuration->hasCoveragePhp());
        $this->assertSame('file', $configuration->coveragePhp());
    }

    public function testCoveragePhpMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoveragePhp());

        $this->expectException(Exception::class);

        $configuration->coveragePhp();
    }

    #[TestDox('--coverage-text')]
    public function testCoverageText(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-text']);

        $this->assertTrue($configuration->hasCoverageText());
        $this->assertSame('php://stdout', $configuration->coverageText());
    }

    #[TestDox('--coverage-text=file')]
    public function testCoverageTextFile(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-text=file']);

        $this->assertTrue($configuration->hasCoverageText());
        $this->assertSame('file', $configuration->coverageText());
    }

    public function testCoverageTextMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoverageText());

        $this->expectException(Exception::class);

        $configuration->coverageText();
    }

    #[TestDox('--only-summary-for-coverage-text')]
    public function testOnlySummaryForCoverageText(): void
    {
        $configuration = (new Builder)->fromParameters(['--only-summary-for-coverage-text']);

        $this->assertTrue($configuration->hasCoverageTextShowOnlySummary());
        $this->assertTrue($configuration->coverageTextShowOnlySummary());
    }

    public function testOnlySummaryForCoverageTextMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoverageTextShowOnlySummary());

        $this->expectException(Exception::class);

        $configuration->coverageTextShowOnlySummary();
    }

    #[TestDox('--show-uncovered-for-coverage-text')]
    public function testShowUncoveredForCoverageText(): void
    {
        $configuration = (new Builder)->fromParameters(['--show-uncovered-for-coverage-text']);

        $this->assertTrue($configuration->hasCoverageTextShowUncoveredFiles());
        $this->assertTrue($configuration->coverageTextShowUncoveredFiles());
    }

    public function testShowUncoveredForCoverageTextMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoverageTextShowUncoveredFiles());

        $this->expectException(Exception::class);

        $configuration->coverageTextShowUncoveredFiles();
    }

    #[TestDox('--coverage-xml directory')]
    public function testCoverageXml(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-xml', 'directory']);

        $this->assertTrue($configuration->hasCoverageXml());
        $this->assertSame('directory', $configuration->coverageXml());
    }

    public function testCoverageXmlMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoverageXml());

        $this->expectException(Exception::class);

        $configuration->coverageXml();
    }

    #[TestDox('--path-coverage')]
    public function testPathCoverage(): void
    {
        $configuration = (new Builder)->fromParameters(['--path-coverage']);

        $this->assertTrue($configuration->hasPathCoverage());
        $this->assertTrue($configuration->pathCoverage());
    }

    public function testPathCoverageMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasPathCoverage());

        $this->expectException(Exception::class);

        $configuration->pathCoverage();
    }

    #[TestDox('-d foo=bar')]
    public function testIniSetting(): void
    {
        $configuration = (new Builder)->fromParameters(['-d', 'foo=bar']);

        $this->assertTrue($configuration->hasIniSettings());
        $this->assertSame(['foo' => 'bar'], $configuration->iniSettings());
    }

    #[TestDox('-d foo')]
    public function testIniSetting2(): void
    {
        $configuration = (new Builder)->fromParameters(['-d', 'foo']);

        $this->assertTrue($configuration->hasIniSettings());
        $this->assertSame(['foo' => '1'], $configuration->iniSettings());
    }

    public function testIniSettingMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasIniSettings());

        $this->expectException(Exception::class);

        $configuration->iniSettings();
    }

    #[TestDox('-h')]
    public function testHelpShort(): void
    {
        $configuration = (new Builder)->fromParameters(['-h']);

        $this->assertTrue($configuration->help());
    }

    #[TestDox('--help')]
    public function testHelp(): void
    {
        $configuration = (new Builder)->fromParameters(['--help']);

        $this->assertTrue($configuration->help());
    }

    public function testHelpMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->help());
    }

    #[TestDox('--filter string')]
    public function testFilter(): void
    {
        $configuration = (new Builder)->fromParameters(['--filter', 'string']);

        $this->assertTrue($configuration->hasFilter());
        $this->assertSame('string', $configuration->filter());
    }

    public function testFilterMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasFilter());

        $this->expectException(Exception::class);

        $configuration->filter();
    }

    #[TestDox('--exclude-filter string')]
    public function testExcludeFilter(): void
    {
        $configuration = (new Builder)->fromParameters(['--exclude-filter', 'string']);

        $this->assertTrue($configuration->hasExcludeFilter());
        $this->assertSame('string', $configuration->excludeFilter());
    }

    public function testExcludeFilterMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasExcludeFilter());

        $this->expectException(Exception::class);

        $configuration->excludeFilter();
    }

    #[TestDox('--testsuite string')]
    public function testTestSuite(): void
    {
        $configuration = (new Builder)->fromParameters(['--testsuite', 'string']);

        $this->assertTrue($configuration->hasTestSuite());
        $this->assertSame('string', $configuration->testSuite());
    }

    public function testTestSuiteMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTestSuite());

        $this->expectException(Exception::class);

        $configuration->testSuite();
    }

    #[TestDox('--exclude-testsuite string')]
    public function testExcludeTestSuite(): void
    {
        $configuration = (new Builder)->fromParameters(['--exclude-testsuite', 'string']);

        $this->assertTrue($configuration->hasExcludedTestSuite());
        $this->assertSame('string', $configuration->excludedTestSuite());
    }

    public function testExcludeTestSuiteMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasExcludedTestSuite());

        $this->expectException(Exception::class);

        $configuration->excludedTestSuite();
    }

    #[TestDox('--generate-baseline file')]
    public function testGenerateBaseline(): void
    {
        $configuration = (new Builder)->fromParameters(['--generate-baseline', 'file']);

        $this->assertTrue($configuration->hasGenerateBaseline());
        $this->assertStringEndsWith('file', $configuration->generateBaseline());
    }

    #[TestDox('--generate-baseline /path/to/file')]
    public function testGenerateBaselineWithPathToFile(): void
    {
        $configuration = (new Builder)->fromParameters(['--generate-baseline', '/path/to/file']);

        $this->assertTrue($configuration->hasGenerateBaseline());
        $this->assertSame('/path/to/file', $configuration->generateBaseline());
    }

    public function testGenerateBaselineMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasGenerateBaseline());

        $this->expectException(Exception::class);

        $configuration->generateBaseline();
    }

    #[TestDox('--use-baseline file')]
    public function testUseBaseline(): void
    {
        $configuration = (new Builder)->fromParameters(['--use-baseline', 'file']);

        $this->assertTrue($configuration->hasUseBaseline());
        $this->assertStringEndsWith('file', $configuration->useBaseline());
    }

    #[TestDox('--use-baseline /path/to/file')]
    public function testUseBaselineWithPathToFile(): void
    {
        $configuration = (new Builder)->fromParameters(['--use-baseline', '/path/to/file']);

        $this->assertTrue($configuration->hasUseBaseline());
        $this->assertSame('/path/to/file', $configuration->useBaseline());
    }

    public function testUseBaselineMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasUseBaseline());

        $this->expectException(Exception::class);

        $configuration->useBaseline();
    }

    #[TestDox('--ignore-baseline')]
    public function testIgnoreBaseline(): void
    {
        $configuration = (new Builder)->fromParameters(['--ignore-baseline']);

        $this->assertTrue($configuration->ignoreBaseline());
    }

    #[TestDox('--generate-configuration')]
    public function testGenerateConfiguration(): void
    {
        $configuration = (new Builder)->fromParameters(['--generate-configuration']);

        $this->assertTrue($configuration->generateConfiguration());
    }

    #[TestDox('--migrate-configuration')]
    public function testMigrateConfiguration(): void
    {
        $configuration = (new Builder)->fromParameters(['--migrate-configuration']);

        $this->assertTrue($configuration->migrateConfiguration());
    }

    #[TestDox('--group string')]
    public function testGroup(): void
    {
        $configuration = (new Builder)->fromParameters(['--group', 'string']);

        $this->assertTrue($configuration->hasGroups());
        $this->assertSame(['string'], $configuration->groups());
    }

    #[TestDox('--group string --group another-string')]
    public function testGroups(): void
    {
        $configuration = (new Builder)->fromParameters(['--group', 'string', '--group', 'another-string']);

        $this->assertTrue($configuration->hasGroups());
        $this->assertSame(['string', 'another-string'], $configuration->groups());
    }

    public function testGroupMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasGroups());

        $this->expectException(Exception::class);

        $configuration->groups();
    }

    #[TestDox('--exclude-group string')]
    public function testExcludeGroup(): void
    {
        $configuration = (new Builder)->fromParameters(['--exclude-group', 'string']);

        $this->assertTrue($configuration->hasExcludeGroups());
        $this->assertSame(['string'], $configuration->excludeGroups());
    }

    #[TestDox('--exclude-group string --exclude-group another-string')]
    public function testExcludeGroups(): void
    {
        $configuration = (new Builder)->fromParameters(['--exclude-group', 'string', '--exclude-group', 'another-string']);

        $this->assertTrue($configuration->hasExcludeGroups());
        $this->assertSame(['string', 'another-string'], $configuration->excludeGroups());
    }

    public function testExcludeGroupMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasGroups());

        $this->expectException(Exception::class);

        $configuration->excludeGroups();
    }

    #[TestDox('--covers Foo\\Bar\\Baz')]
    public function testCovers(): void
    {
        $configuration = (new Builder)->fromParameters(['--covers', 'Foo\\Bar\\Baz']);

        $this->assertTrue($configuration->hasTestsCovering());
        $this->assertSame(['foo\\bar\\baz'], $configuration->testsCovering());
    }

    public function testCoversMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTestsCovering());

        $this->expectException(Exception::class);

        $configuration->testsCovering();
    }

    #[TestDox('--uses Foo\\Bar\\Baz')]
    public function testUses(): void
    {
        $configuration = (new Builder)->fromParameters(['--uses', 'Foo\\Bar\\Baz']);

        $this->assertTrue($configuration->hasTestsUsing());
        $this->assertSame(['foo\\bar\\baz'], $configuration->testsUsing());
    }

    public function testUsesMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTestsUsing());

        $this->expectException(Exception::class);

        $configuration->testsUsing();
    }

    #[TestDox('--requires-php-extension extension')]
    public function testRequiresPhpExtension(): void
    {
        $configuration = (new Builder)->fromParameters(['--requires-php-extension', 'extension']);

        $this->assertTrue($configuration->hasTestsRequiringPhpExtension());
        $this->assertSame(['extension'], $configuration->testsRequiringPhpExtension());
    }

    public function testRequiresPhpExtensionMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTestsRequiringPhpExtension());

        $this->expectException(Exception::class);

        $configuration->testsRequiringPhpExtension();
    }

    #[TestDox('--test-suffix string')]
    public function testTestSuffix(): void
    {
        $configuration = (new Builder)->fromParameters(['--test-suffix', 'string']);

        $this->assertTrue($configuration->hasTestSuffixes());
        $this->assertSame(['string'], $configuration->testSuffixes());
    }

    #[TestDox('--test-suffix string --test-suffix another-string')]
    public function testTestSuffixes(): void
    {
        $configuration = (new Builder)->fromParameters(['--test-suffix', 'string', '--test-suffix', 'another-string']);

        $this->assertTrue($configuration->hasTestSuffixes());
        $this->assertSame(['string', 'another-string'], $configuration->testSuffixes());
    }

    public function testTestSuffixMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTestSuffixes());

        $this->expectException(Exception::class);

        $configuration->testSuffixes();
    }

    #[TestDox('--include-path string')]
    public function testIncludePath(): void
    {
        $configuration = (new Builder)->fromParameters(['--include-path', 'string']);

        $this->assertTrue($configuration->hasIncludePath());
        $this->assertSame('string', $configuration->includePath());
    }

    public function testIncludePathMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasIncludePath());

        $this->expectException(Exception::class);

        $configuration->includePath();
    }

    #[TestDox('--list-groups')]
    public function testListGroups(): void
    {
        $configuration = (new Builder)->fromParameters(['--list-groups']);

        $this->assertTrue($configuration->listGroups());
    }

    #[TestDox('--list-suites')]
    public function testListSuites(): void
    {
        $configuration = (new Builder)->fromParameters(['--list-suites']);

        $this->assertTrue($configuration->listSuites());
    }

    #[TestDox('--list-test-files')]
    public function testListTestFiles(): void
    {
        $configuration = (new Builder)->fromParameters(['--list-test-files']);

        $this->assertTrue($configuration->listTestFiles());
    }

    #[TestDox('--list-tests')]
    public function testListTests(): void
    {
        $configuration = (new Builder)->fromParameters(['--list-tests']);

        $this->assertTrue($configuration->listTests());
    }

    #[TestDox('--list-tests-xml file')]
    public function testListTestsXml(): void
    {
        $configuration = (new Builder)->fromParameters(['--list-tests-xml', 'file']);

        $this->assertTrue($configuration->hasListTestsXml());
        $this->assertSame('file', $configuration->listTestsXml());
    }

    public function testListTestsXmlMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasListTestsXml());

        $this->expectException(Exception::class);

        $configuration->listTestsXml();
    }

    #[TestDox('--log-events-text file')]
    public function testEventsText(): void
    {
        $configuration = (new Builder)->fromParameters(['--log-events-text', 'file']);

        $this->assertTrue($configuration->hasLogEventsText());
        $this->assertStringEndsWith('file', $configuration->logEventsText());
    }

    #[TestDox('--log-events-text /invalid/path')]
    public function testEventsTextInvalidPath(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The path "/invalid/path" specified for the --log-events-text option could not be resolved');

        (new Builder)->fromParameters(['--log-events-text', '/invalid/path']);
    }

    public function testEventsTextMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasLogEventsText());

        $this->expectException(Exception::class);

        $configuration->logEventsText();
    }

    #[TestDox('--log-events-verbose-text file')]
    public function testEventsVerboseText(): void
    {
        $configuration = (new Builder)->fromParameters(['--log-events-verbose-text', 'file']);

        $this->assertTrue($configuration->hasLogEventsVerboseText());
        $this->assertStringEndsWith('file', $configuration->logEventsVerboseText());
    }

    #[TestDox('--log-events-verbose-text /invalid/path')]
    public function testEventsVerboseTextInvalidPath(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The path "/invalid/path" specified for the --log-events-verbose-text option could not be resolved');

        (new Builder)->fromParameters(['--log-events-verbose-text', '/invalid/path']);
    }

    public function testEventsVerboseTextMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasLogEventsVerboseText());

        $this->expectException(Exception::class);

        $configuration->logEventsVerboseText();
    }

    #[TestDox('--log-junit file')]
    public function testLogJunit(): void
    {
        $configuration = (new Builder)->fromParameters(['--log-junit', 'file']);

        $this->assertTrue($configuration->hasJunitLogfile());
        $this->assertSame('file', $configuration->junitLogfile());
    }

    public function testLogJunitMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasJunitLogfile());

        $this->expectException(Exception::class);

        $configuration->junitLogfile();
    }

    #[TestDox('--log-teamcity file')]
    public function testLogTeamcity(): void
    {
        $configuration = (new Builder)->fromParameters(['--log-teamcity', 'file']);

        $this->assertTrue($configuration->hasTeamcityLogfile());
        $this->assertSame('file', $configuration->teamcityLogfile());
    }

    public function testLogTeamcityMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTeamcityLogfile());

        $this->expectException(Exception::class);

        $configuration->teamcityLogfile();
    }

    #[TestDox('--order-by default')]
    public function testOrderByDefault(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'default']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_DEFAULT, $configuration->executionOrder());
        $this->assertTrue($configuration->hasExecutionOrderDefects());
        $this->assertSame(TestSuiteSorter::ORDER_DEFAULT, $configuration->executionOrderDefects());
        $this->assertTrue($configuration->resolveDependencies());
    }

    #[TestDox('--order-by defects')]
    public function testOrderByDefects(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'defects']);

        $this->assertFalse($configuration->hasExecutionOrder());
        $this->assertTrue($configuration->hasExecutionOrderDefects());
        $this->assertSame(TestSuiteSorter::ORDER_DEFECTS_FIRST, $configuration->executionOrderDefects());
        $this->assertFalse($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by depends')]
    public function testOrderByDepends(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'depends']);

        $this->assertFalse($configuration->hasExecutionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by duration')]
    public function testOrderByDuration(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'duration']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_DURATION, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertFalse($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by random')]
    public function testOrderByRandom(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'random']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_RANDOMIZED, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertFalse($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by reverse')]
    public function testOrderByReverse(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'reverse']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_REVERSED, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertFalse($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by size')]
    public function testOrderBySize(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'size']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_SIZE, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertFalse($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by depends,defects')]
    public function testOrderByDependsDefects(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'depends,defects']);

        $this->assertFalse($configuration->hasExecutionOrder());
        $this->assertTrue($configuration->hasExecutionOrderDefects());
        $this->assertSame(TestSuiteSorter::ORDER_DEFECTS_FIRST, $configuration->executionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by depends,duration')]
    public function testOrderByDependsDuration(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'depends,duration']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_DURATION, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by depends,random')]
    public function testOrderByDependsRandom(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'depends,random']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_RANDOMIZED, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by depends,reverse')]
    public function testOrderByDependsReverse(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'depends,reverse']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_REVERSED, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by depends,size')]
    public function testOrderByDependsSize(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'depends,size']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_SIZE, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
    }

    #[TestDox('--order-by no-depends')]
    public function testOrderByNoDepends(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'no-depends']);

        $this->assertFalse($configuration->hasExecutionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
        $this->assertFalse($configuration->resolveDependencies());
    }

    #[TestDox('--order-by no-depends,defects')]
    public function testOrderByNoDependsDefects(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'no-depends,defects']);

        $this->assertFalse($configuration->hasExecutionOrder());
        $this->assertTrue($configuration->hasExecutionOrderDefects());
        $this->assertSame(TestSuiteSorter::ORDER_DEFECTS_FIRST, $configuration->executionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
        $this->assertFalse($configuration->resolveDependencies());
    }

    #[TestDox('--order-by no-depends,duration')]
    public function testOrderByNoDependsDuration(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'no-depends,duration']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_DURATION, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
        $this->assertFalse($configuration->resolveDependencies());
    }

    #[TestDox('--order-by no-depends,random')]
    public function testOrderByNoDependsRandom(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'no-depends,random']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_RANDOMIZED, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
        $this->assertFalse($configuration->resolveDependencies());
    }

    #[TestDox('--order-by no-depends,reverse')]
    public function testOrderByNoDependsReverse(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'no-depends,reverse']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_REVERSED, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
        $this->assertFalse($configuration->resolveDependencies());
    }

    #[TestDox('--order-by no-depends,size')]
    public function testOrderByNoDependsSize(): void
    {
        $configuration = (new Builder)->fromParameters(['--order-by', 'no-depends,size']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_SIZE, $configuration->executionOrder());
        $this->assertFalse($configuration->hasExecutionOrderDefects());
        $this->assertTrue($configuration->hasResolveDependencies());
        $this->assertFalse($configuration->resolveDependencies());
    }

    #[TestDox('--order-by invalid')]
    public function testOrderByInvalid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('unrecognized --order-by option: invalid');

        (new Builder)->fromParameters(['--order-by', 'invalid']);
    }

    public function testExecutionOrderMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasExecutionOrder());

        $this->expectException(Exception::class);

        $configuration->executionOrder();
    }

    public function testExecutionOrderDefectsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasExecutionOrderDefects());

        $this->expectException(Exception::class);

        $configuration->executionOrderDefects();
    }

    public function testResolveDependenciesMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasResolveDependencies());

        $this->expectException(Exception::class);

        $configuration->resolveDependencies();
    }

    #[TestDox('--process-isolation')]
    public function testProcessIsolation(): void
    {
        $configuration = (new Builder)->fromParameters(['--process-isolation']);

        $this->assertTrue($configuration->hasProcessIsolation());
        $this->assertTrue($configuration->processIsolation());
    }

    public function testProcessIsolationMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasProcessIsolation());

        $this->expectException(Exception::class);

        $configuration->processIsolation();
    }

    #[TestDox('--stderr')]
    public function testStderr(): void
    {
        $configuration = (new Builder)->fromParameters(['--stderr']);

        $this->assertTrue($configuration->hasStderr());
        $this->assertTrue($configuration->stderr());
    }

    public function testStderrMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStderr());

        $this->expectException(Exception::class);

        $configuration->stderr();
    }

    #[TestDox('--fail-on-deprecation')]
    public function testFailOnDeprecation(): void
    {
        $configuration = (new Builder)->fromParameters(['--fail-on-deprecation']);

        $this->assertTrue($configuration->hasFailOnDeprecation());
        $this->assertTrue($configuration->failOnDeprecation());
    }

    public function testFailOnDeprecationMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasFailOnDeprecation());

        $this->expectException(Exception::class);

        $configuration->failOnDeprecation();
    }

    #[TestDox('--fail-on-phpunit-deprecation')]
    public function testFailOnPhpunitDeprecation(): void
    {
        $configuration = (new Builder)->fromParameters(['--fail-on-phpunit-deprecation']);

        $this->assertTrue($configuration->hasFailOnPhpunitDeprecation());
        $this->assertTrue($configuration->failOnPhpunitDeprecation());
    }

    public function testFailOnPhpunitDeprecationMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasFailOnPhpunitDeprecation());

        $this->expectException(Exception::class);

        $configuration->failOnPhpunitDeprecation();
    }

    #[TestDox('--fail-on-empty-test-suite')]
    public function testFailOnEmptyTestSuite(): void
    {
        $configuration = (new Builder)->fromParameters(['--fail-on-empty-test-suite']);

        $this->assertTrue($configuration->hasFailOnEmptyTestSuite());
        $this->assertTrue($configuration->failOnEmptyTestSuite());
    }

    public function testFailOnEmptyTestSuiteMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasFailOnEmptyTestSuite());

        $this->expectException(Exception::class);

        $configuration->failOnEmptyTestSuite();
    }

    #[TestDox('--fail-on-incomplete')]
    public function testFailOnIncomplete(): void
    {
        $configuration = (new Builder)->fromParameters(['--fail-on-incomplete']);

        $this->assertTrue($configuration->hasFailOnIncomplete());
        $this->assertTrue($configuration->failOnIncomplete());
    }

    public function testFailOnIncompleteMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasFailOnIncomplete());

        $this->expectException(Exception::class);

        $configuration->failOnIncomplete();
    }

    #[TestDox('--fail-on-notice')]
    public function testFailOnNotice(): void
    {
        $configuration = (new Builder)->fromParameters(['--fail-on-notice']);

        $this->assertTrue($configuration->hasFailOnNotice());
        $this->assertTrue($configuration->failOnNotice());
    }

    public function testFailOnNoticeMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasFailOnNotice());

        $this->expectException(Exception::class);

        $configuration->failOnNotice();
    }

    #[TestDox('--fail-on-risky')]
    public function testFailOnRisky(): void
    {
        $configuration = (new Builder)->fromParameters(['--fail-on-risky']);

        $this->assertTrue($configuration->hasFailOnRisky());
        $this->assertTrue($configuration->failOnRisky());
    }

    public function testFailOnRiskyMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasFailOnRisky());

        $this->expectException(Exception::class);

        $configuration->failOnRisky();
    }

    #[TestDox('--fail-on-skipped')]
    public function testFailOnSkipped(): void
    {
        $configuration = (new Builder)->fromParameters(['--fail-on-skipped']);

        $this->assertTrue($configuration->hasFailOnSkipped());
        $this->assertTrue($configuration->failOnSkipped());
    }

    public function testFailOnSkippedMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasFailOnSkipped());

        $this->expectException(Exception::class);

        $configuration->failOnSkipped();
    }

    #[TestDox('--fail-on-warning')]
    public function testFailOnWarning(): void
    {
        $configuration = (new Builder)->fromParameters(['--fail-on-warning']);

        $this->assertTrue($configuration->hasFailOnWarning());
        $this->assertTrue($configuration->failOnWarning());
    }

    public function testFailOnWarningMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasFailOnWarning());

        $this->expectException(Exception::class);

        $configuration->failOnWarning();
    }

    #[TestDox('--stop-on-defect')]
    public function testStopOnDefect(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-defect']);

        $this->assertTrue($configuration->hasStopOnDefect());
        $this->assertTrue($configuration->stopOnDefect());
    }

    public function testStopOnDefectMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStopOnDefect());

        $this->expectException(Exception::class);

        $configuration->stopOnDefect();
    }

    #[TestDox('--stop-on-deprecation')]
    public function testStopOnDeprecation(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-deprecation']);

        $this->assertTrue($configuration->hasStopOnDeprecation());
        $this->assertTrue($configuration->stopOnDeprecation());
    }

    #[TestDox('--stop-on-deprecation=message')]
    public function testStopOnDeprecationMessage(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-deprecation=message']);

        $this->assertTrue($configuration->hasStopOnDeprecation());
        $this->assertTrue($configuration->stopOnDeprecation());
        $this->assertTrue($configuration->hasSpecificDeprecationToStopOn());
        $this->assertSame('message', $configuration->specificDeprecationToStopOn());
    }

    public function testStopOnDeprecationMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStopOnDeprecation());

        $this->expectException(Exception::class);

        $configuration->stopOnDeprecation();
    }

    #[TestDox('--stop-on-error')]
    public function testStopOnError(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-error']);

        $this->assertTrue($configuration->hasStopOnError());
        $this->assertTrue($configuration->stopOnError());
    }

    public function testStopOnErrorMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStopOnError());

        $this->expectException(Exception::class);

        $configuration->stopOnError();
    }

    #[TestDox('--stop-on-failure')]
    public function testStopOnFailure(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-failure']);

        $this->assertTrue($configuration->hasStopOnFailure());
        $this->assertTrue($configuration->stopOnFailure());
    }

    public function testStopOnFailureMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStopOnFailure());

        $this->expectException(Exception::class);

        $configuration->stopOnFailure();
    }

    #[TestDox('--stop-on-incomplete')]
    public function testStopOnIncomplete(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-incomplete']);

        $this->assertTrue($configuration->hasStopOnIncomplete());
        $this->assertTrue($configuration->stopOnIncomplete());
    }

    public function testStopOnIncompleteMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStopOnIncomplete());

        $this->expectException(Exception::class);

        $configuration->stopOnIncomplete();
    }

    #[TestDox('--stop-on-notice')]
    public function testStopOnNotice(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-notice']);

        $this->assertTrue($configuration->hasStopOnNotice());
        $this->assertTrue($configuration->stopOnNotice());
    }

    public function testStopOnNoticeMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStopOnNotice());

        $this->expectException(Exception::class);

        $configuration->stopOnNotice();
    }

    #[TestDox('--stop-on-risky')]
    public function testStopOnRisky(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-risky']);

        $this->assertTrue($configuration->hasStopOnRisky());
        $this->assertTrue($configuration->stopOnRisky());
    }

    public function testStopOnRiskyMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStopOnRisky());

        $this->expectException(Exception::class);

        $configuration->stopOnRisky();
    }

    #[TestDox('--stop-on-skipped')]
    public function testStopOnSkipped(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-skipped']);

        $this->assertTrue($configuration->hasStopOnSkipped());
        $this->assertTrue($configuration->stopOnSkipped());
    }

    public function testStopOnSkippedMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStopOnSkipped());

        $this->expectException(Exception::class);

        $configuration->stopOnSkipped();
    }

    #[TestDox('--stop-on-warning')]
    public function testStopOnWarning(): void
    {
        $configuration = (new Builder)->fromParameters(['--stop-on-warning']);

        $this->assertTrue($configuration->hasStopOnWarning());
        $this->assertTrue($configuration->stopOnWarning());
    }

    public function testStopOnWarningMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStopOnWarning());

        $this->expectException(Exception::class);

        $configuration->stopOnWarning();
    }

    #[TestDox('--teamcity')]
    public function testTeamcity(): void
    {
        $configuration = (new Builder)->fromParameters(['--teamcity']);

        $this->assertTrue($configuration->hasTeamCityPrinter());
        $this->assertTrue($configuration->teamCityPrinter());
    }

    public function testTeamcityMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTeamCityPrinter());

        $this->expectException(Exception::class);

        $configuration->teamCityPrinter();
    }

    #[TestDox('--testdox')]
    public function testTestDox(): void
    {
        $configuration = (new Builder)->fromParameters(['--testdox']);

        $this->assertTrue($configuration->hasTestDoxPrinter());
        $this->assertTrue($configuration->testdoxPrinter());
    }

    public function testTestDoxMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTestDoxPrinter());

        $this->expectException(Exception::class);

        $configuration->testdoxPrinter();
    }

    #[TestDox('--testdox-html file')]
    public function testTestDoxHtml(): void
    {
        $configuration = (new Builder)->fromParameters(['--testdox-html', 'file']);

        $this->assertTrue($configuration->hasTestdoxHtmlFile());
        $this->assertSame('file', $configuration->testdoxHtmlFile());
    }

    public function testTestDoxHtmlMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTestdoxHtmlFile());

        $this->expectException(Exception::class);

        $configuration->testdoxHtmlFile();
    }

    #[TestDox('--testdox-text file')]
    public function testTestDoxText(): void
    {
        $configuration = (new Builder)->fromParameters(['--testdox-text', 'file']);

        $this->assertTrue($configuration->hasTestdoxTextFile());
        $this->assertSame('file', $configuration->testdoxTextFile());
    }

    public function testTestDoxTextMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasTestdoxTextFile());

        $this->expectException(Exception::class);

        $configuration->testdoxTextFile();
    }

    #[TestDox('--no-configuration')]
    public function testNoConfiguration(): void
    {
        $configuration = (new Builder)->fromParameters(['--no-configuration']);

        $this->assertFalse($configuration->useDefaultConfiguration());
    }

    #[TestDox('--no-extensions')]
    public function testNoExtensions(): void
    {
        $configuration = (new Builder)->fromParameters(['--no-extensions']);

        $this->assertTrue($configuration->hasNoExtensions());
        $this->assertTrue($configuration->noExtensions());
    }

    public function testNoExtensionsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasNoExtensions());

        $this->expectException(Exception::class);

        $configuration->noExtensions();
    }

    #[TestDox('--no-coverage')]
    public function testNoCoverage(): void
    {
        $configuration = (new Builder)->fromParameters(['--no-coverage']);

        $this->assertTrue($configuration->hasNoCoverage());
        $this->assertTrue($configuration->noCoverage());
    }

    public function testNoCoverageMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasNoCoverage());

        $this->expectException(Exception::class);

        $configuration->noCoverage();
    }

    #[TestDox('--no-logging')]
    public function testNoLogging(): void
    {
        $configuration = (new Builder)->fromParameters(['--no-logging']);

        $this->assertTrue($configuration->hasNoLogging());
        $this->assertTrue($configuration->noLogging());
    }

    public function testNoLoggingMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasNoLogging());

        $this->expectException(Exception::class);

        $configuration->noLogging();
    }

    #[TestDox('--no-output')]
    public function testNoOutput(): void
    {
        $configuration = (new Builder)->fromParameters(['--no-output']);

        $this->assertTrue($configuration->hasNoOutput());
        $this->assertTrue($configuration->noOutput());
    }

    public function testNoOutputMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasNoOutput());

        $this->expectException(Exception::class);

        $configuration->noOutput();
    }

    #[TestDox('--no-progress')]
    public function testNoProgress(): void
    {
        $configuration = (new Builder)->fromParameters(['--no-progress']);

        $this->assertTrue($configuration->hasNoProgress());
        $this->assertTrue($configuration->noProgress());
    }

    public function testNoProgressMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasNoProgress());

        $this->expectException(Exception::class);

        $configuration->noProgress();
    }

    #[TestDox('--no-results')]
    public function testNoResults(): void
    {
        $configuration = (new Builder)->fromParameters(['--no-results']);

        $this->assertTrue($configuration->hasNoResults());
        $this->assertTrue($configuration->noResults());
    }

    public function testNoResultsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasNoResults());

        $this->expectException(Exception::class);

        $configuration->noResults();
    }

    #[TestDox('--globals-backup')]
    public function testGlobalsBackup(): void
    {
        $configuration = (new Builder)->fromParameters(['--globals-backup']);

        $this->assertTrue($configuration->hasBackupGlobals());
        $this->assertTrue($configuration->backupGlobals());
    }

    public function testGlobalsBackupMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasBackupGlobals());

        $this->expectException(Exception::class);

        $configuration->backupGlobals();
    }

    #[TestDox('--static-backup')]
    public function testStaticBackup(): void
    {
        $configuration = (new Builder)->fromParameters(['--static-backup']);

        $this->assertTrue($configuration->hasBackupStaticProperties());
        $this->assertTrue($configuration->backupStaticProperties());
    }

    public function testStaticBackupMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasBackupStaticProperties());

        $this->expectException(Exception::class);

        $configuration->backupStaticProperties();
    }

    #[TestDox('--atleast-version string')]
    public function testAtLeastVersion(): void
    {
        $configuration = (new Builder)->fromParameters(['--atleast-version', 'string']);

        $this->assertTrue($configuration->hasAtLeastVersion());
        $this->assertSame('string', $configuration->atLeastVersion());
    }

    public function testAtLeastVersionMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasAtLeastVersion());

        $this->expectException(Exception::class);

        $configuration->atLeastVersion();
    }

    #[TestDox('--version')]
    public function testVersion(): void
    {
        $configuration = (new Builder)->fromParameters(['--version']);

        $this->assertTrue($configuration->version());
    }

    #[TestDox('--dont-report-useless-tests')]
    public function testDontReportUselessTests(): void
    {
        $configuration = (new Builder)->fromParameters(['--dont-report-useless-tests']);

        $this->assertTrue($configuration->hasReportUselessTests());
        $this->assertFalse($configuration->reportUselessTests());
    }

    public function testDontReportUselessTestsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasReportUselessTests());

        $this->expectException(Exception::class);

        $configuration->reportUselessTests();
    }

    #[TestDox('--strict-coverage')]
    public function testStrictCoverage(): void
    {
        $configuration = (new Builder)->fromParameters(['--strict-coverage']);

        $this->assertTrue($configuration->hasStrictCoverage());
        $this->assertTrue($configuration->strictCoverage());
    }

    public function testStrictCoverageMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasStrictCoverage());

        $this->expectException(Exception::class);

        $configuration->strictCoverage();
    }

    #[TestDox('--disable-coverage-ignore')]
    public function testDisableCoverageIgnore(): void
    {
        $configuration = (new Builder)->fromParameters(['--disable-coverage-ignore']);

        $this->assertTrue($configuration->hasDisableCodeCoverageIgnore());
        $this->assertTrue($configuration->disableCodeCoverageIgnore());
    }

    public function testDisableCoverageIgnoreMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDisableCodeCoverageIgnore());

        $this->expectException(Exception::class);

        $configuration->disableCodeCoverageIgnore();
    }

    #[TestDox('--strict-global-state')]
    public function testStrictGlobalState(): void
    {
        $configuration = (new Builder)->fromParameters(['--strict-global-state']);

        $this->assertTrue($configuration->hasBeStrictAboutChangesToGlobalState());
        $this->assertTrue($configuration->beStrictAboutChangesToGlobalState());
    }

    public function testStrictGlobalStateMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasBeStrictAboutChangesToGlobalState());

        $this->expectException(Exception::class);

        $configuration->beStrictAboutChangesToGlobalState();
    }

    #[TestDox('--disallow-test-output')]
    public function testDisallowTestOutput(): void
    {
        $configuration = (new Builder)->fromParameters(['--disallow-test-output']);

        $this->assertTrue($configuration->hasDisallowTestOutput());
        $this->assertTrue($configuration->disallowTestOutput());
    }

    public function testDisallowTestOutputMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDisallowTestOutput());

        $this->expectException(Exception::class);

        $configuration->disallowTestOutput();
    }

    #[TestDox('--display-incomplete')]
    public function testDisplayIncomplete(): void
    {
        $configuration = (new Builder)->fromParameters(['--display-incomplete']);

        $this->assertTrue($configuration->hasDisplayDetailsOnIncompleteTests());
        $this->assertTrue($configuration->displayDetailsOnIncompleteTests());
    }

    public function testDisplayIncompleteMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDisplayDetailsOnIncompleteTests());

        $this->expectException(Exception::class);

        $configuration->displayDetailsOnIncompleteTests();
    }

    #[TestDox('--display-skipped')]
    public function testDisplaySkipped(): void
    {
        $configuration = (new Builder)->fromParameters(['--display-skipped']);

        $this->assertTrue($configuration->hasDisplayDetailsOnSkippedTests());
        $this->assertTrue($configuration->displayDetailsOnSkippedTests());
    }

    public function testDisplaySkippedMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDisplayDetailsOnSkippedTests());

        $this->expectException(Exception::class);

        $configuration->displayDetailsOnSkippedTests();
    }

    #[TestDox('--display-deprecations')]
    public function testDisplayDeprecations(): void
    {
        $configuration = (new Builder)->fromParameters(['--display-deprecations']);

        $this->assertTrue($configuration->hasDisplayDetailsOnTestsThatTriggerDeprecations());
        $this->assertTrue($configuration->displayDetailsOnTestsThatTriggerDeprecations());
    }

    public function testDisplayDeprecationsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDisplayDetailsOnTestsThatTriggerDeprecations());

        $this->expectException(Exception::class);

        $configuration->displayDetailsOnTestsThatTriggerDeprecations();
    }

    #[TestDox('--display-phpunit-deprecations')]
    public function testDisplayPhpunitDeprecations(): void
    {
        $configuration = (new Builder)->fromParameters(['--display-phpunit-deprecations']);

        $this->assertTrue($configuration->hasDisplayDetailsOnPhpunitDeprecations());
        $this->assertTrue($configuration->displayDetailsOnPhpunitDeprecations());
    }

    public function testDisplayPhpunitDeprecationsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDisplayDetailsOnPhpunitDeprecations());

        $this->expectException(Exception::class);

        $configuration->displayDetailsOnPhpunitDeprecations();
    }

    #[TestDox('--display-errors')]
    public function testDisplayErrors(): void
    {
        $configuration = (new Builder)->fromParameters(['--display-errors']);

        $this->assertTrue($configuration->hasDisplayDetailsOnTestsThatTriggerErrors());
        $this->assertTrue($configuration->displayDetailsOnTestsThatTriggerErrors());
    }

    public function testDisplayErrorsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDisplayDetailsOnTestsThatTriggerErrors());

        $this->expectException(Exception::class);

        $configuration->displayDetailsOnTestsThatTriggerErrors();
    }

    #[TestDox('--display-notices')]
    public function testDisplayNotices(): void
    {
        $configuration = (new Builder)->fromParameters(['--display-notices']);

        $this->assertTrue($configuration->hasDisplayDetailsOnTestsThatTriggerNotices());
        $this->assertTrue($configuration->displayDetailsOnTestsThatTriggerNotices());
    }

    public function testDisplayNoticesMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDisplayDetailsOnTestsThatTriggerNotices());

        $this->expectException(Exception::class);

        $configuration->displayDetailsOnTestsThatTriggerNotices();
    }

    #[TestDox('--display-warnings')]
    public function testDisplayWarnings(): void
    {
        $configuration = (new Builder)->fromParameters(['--display-warnings']);

        $this->assertTrue($configuration->hasDisplayDetailsOnTestsThatTriggerWarnings());
        $this->assertTrue($configuration->displayDetailsOnTestsThatTriggerWarnings());
    }

    public function testDisplayWarningsMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDisplayDetailsOnTestsThatTriggerWarnings());

        $this->expectException(Exception::class);

        $configuration->displayDetailsOnTestsThatTriggerWarnings();
    }

    #[TestDox('--default-time-limit <n>')]
    public function testDefaultTimeLimit(): void
    {
        $configuration = (new Builder)->fromParameters(['--default-time-limit', '10']);

        $this->assertTrue($configuration->hasDefaultTimeLimit());
        $this->assertSame(10, $configuration->defaultTimeLimit());
    }

    public function testDefaultTimeLimitMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasDefaultTimeLimit());

        $this->expectException(Exception::class);

        $configuration->defaultTimeLimit();
    }

    #[TestDox('--enforce-time-limit')]
    public function testEnforceTimeLimit(): void
    {
        $configuration = (new Builder)->fromParameters(['--enforce-time-limit']);

        $this->assertTrue($configuration->hasEnforceTimeLimit());
        $this->assertTrue($configuration->enforceTimeLimit());
    }

    public function testEnforceTimeLimitMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasEnforceTimeLimit());

        $this->expectException(Exception::class);

        $configuration->enforceTimeLimit();
    }

    #[TestDox('--reverse-list')]
    public function testReverseList(): void
    {
        $configuration = (new Builder)->fromParameters(['--reverse-list']);

        $this->assertTrue($configuration->hasReverseList());
        $this->assertTrue($configuration->reverseList());
    }

    public function testReverseListMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasReverseList());

        $this->expectException(Exception::class);

        $configuration->reverseList();
    }

    #[TestDox('--check-version')]
    public function testCheckVersion(): void
    {
        $configuration = (new Builder)->fromParameters(['--check-version']);

        $this->assertTrue($configuration->checkVersion());
    }

    #[TestDox('--coverage-filter directory')]
    public function testCoverageFilterDirectory(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-filter', 'directory']);

        $this->assertTrue($configuration->hasCoverageFilter());
        $this->assertSame(['directory'], $configuration->coverageFilter());
    }

    #[TestDox('--coverage-filter directory --coverage-filter another-directory')]
    public function testCoverageFilterDirectories(): void
    {
        $configuration = (new Builder)->fromParameters(['--coverage-filter', 'directory', '--coverage-filter', 'another-directory']);

        $this->assertTrue($configuration->hasCoverageFilter());
        $this->assertSame(['directory', 'another-directory'], $configuration->coverageFilter());
    }

    public function testCoverageFilterMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasCoverageFilter());

        $this->expectException(Exception::class);

        $configuration->coverageFilter();
    }

    #[TestDox('--random-order')]
    public function testRandomOrder(): void
    {
        $configuration = (new Builder)->fromParameters(['--random-order']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_RANDOMIZED, $configuration->executionOrder());
    }

    #[TestDox('--resolve-dependencies')]
    public function testResolveDependencies(): void
    {
        $configuration = (new Builder)->fromParameters(['--resolve-dependencies']);

        $this->assertTrue($configuration->hasResolveDependencies());
        $this->assertTrue($configuration->resolveDependencies());
    }

    #[TestDox('--ignore-dependencies')]
    public function testIgnoreDependencies(): void
    {
        $configuration = (new Builder)->fromParameters(['--ignore-dependencies']);

        $this->assertTrue($configuration->hasResolveDependencies());
        $this->assertFalse($configuration->resolveDependencies());
    }

    #[TestDox('--reverse-order')]
    public function testReverseOrder(): void
    {
        $configuration = (new Builder)->fromParameters(['--reverse-order']);

        $this->assertTrue($configuration->hasExecutionOrder());
        $this->assertSame(TestSuiteSorter::ORDER_REVERSED, $configuration->executionOrder());
    }

    #[TestDox('--random-order-seed')]
    public function testRandomOrderSeed(): void
    {
        $configuration = (new Builder)->fromParameters(['--random-order-seed', '1234']);

        $this->assertTrue($configuration->hasRandomOrderSeed());
        $this->assertSame(1234, $configuration->randomOrderSeed());
    }

    public function testRandomOrderSeedMayNotBeConfigured(): void
    {
        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse($configuration->hasRandomOrderSeed());

        $this->expectException(Exception::class);

        $configuration->randomOrderSeed();
    }

    #[TestDox('--debug')]
    public function testDebug(): void
    {
        $configuration = (new Builder)->fromParameters(['--debug']);

        $this->assertTrue($configuration->debug());
    }

    public function testInvalidOption(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown option "--invalid-option"');

        (new Builder)->fromParameters(['--invalid-option']);
    }
}
