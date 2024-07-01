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
use const PHP_EOL;
use const PHP_VERSION;
use function file_put_contents;
use function iterator_to_array;
use function realpath;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\Configuration\Configuration;
use SebastianBergmann\CodeCoverage\Report\Html\Colors;
use SebastianBergmann\CodeCoverage\Report\Thresholds;

#[CoversClass(Loader::class)]
#[Medium]
final class LoaderTest extends TestCase
{
    public static function configurationRootOptionsProvider(): array
    {
        return [
            'executionOrder default'       => ['executionOrder', 'default', TestSuiteSorter::ORDER_DEFAULT],
            'executionOrder random'        => ['executionOrder', 'random', TestSuiteSorter::ORDER_RANDOMIZED],
            'executionOrder reverse'       => ['executionOrder', 'reverse', TestSuiteSorter::ORDER_REVERSED],
            'executionOrder size'          => ['executionOrder', 'size', TestSuiteSorter::ORDER_SIZE],
            'cacheDirectory absolute path' => ['cacheDirectory', '/path/to/cache', '/path/to/cache'],
            'cacheResult=false'            => ['cacheResult', 'false', false],
            'cacheResult=true'             => ['cacheResult', 'true', true],
            'columns'                      => ['columns', 'max', 'max'],
            'stopOnFailure'                => ['stopOnFailure', 'true', true],
            'stopOnWarning'                => ['stopOnWarning', 'true', true],
            'stopOnIncomplete'             => ['stopOnIncomplete', 'true', true],
            'stopOnRisky'                  => ['stopOnRisky', 'true', true],
            'stopOnSkipped'                => ['stopOnSkipped', 'true', true],
            'failOnEmptyTestSuite'         => ['failOnEmptyTestSuite', 'true', true],
            'failOnWarning'                => ['failOnWarning', 'true', true],
            'failOnRisky'                  => ['failOnRisky', 'true', true],
            'processIsolation'             => ['processIsolation', 'true', true],
            'reverseDefectList'            => ['reverseDefectList', 'true', true],
        ];
    }

    public function testExceptionIsThrownForNotExistingConfigurationFile(): void
    {
        $this->expectException(Exception::class);

        /* @noinspection UnusedFunctionResultInspection */
        $this->configuration('not_existing_file.xml');
    }

    public function testShouldReadColorsWhenTrueInConfigurationFile(): void
    {
        $phpunit = $this->configuration('configuration.colors.true.xml')->phpunit();

        $this->assertEquals(Configuration::COLOR_AUTO, $phpunit->colors());
    }

    public function testShouldReadColorsWhenFalseInConfigurationFile(): void
    {
        $phpunit = $this->configuration('configuration.colors.false.xml')->phpunit();

        $this->assertEquals(Configuration::COLOR_NEVER, $phpunit->colors());
    }

    public function testShouldReadColorsWhenEmptyInConfigurationFile(): void
    {
        $phpunit = $this->configuration('configuration.colors.empty.xml')->phpunit();

        $this->assertEquals(Configuration::COLOR_NEVER, $phpunit->colors());
    }

    public function testShouldReadColorsWhenInvalidInConfigurationFile(): void
    {
        $phpunit = $this->configuration('configuration.colors.invalid.xml')->phpunit();

        $this->assertEquals(Configuration::COLOR_NEVER, $phpunit->colors());
    }

    public function testInvalidConfigurationGeneratesValidationErrors(): void
    {
        $configuration = $this->configuration('configuration.colors.invalid.xml');

        $this->assertTrue($configuration->hasValidationErrors());
    }

    public function testShouldUseDefaultValuesForInvalidIntegers(): void
    {
        $phpunit = $this->configuration('configuration.columns.default.xml')->phpunit();

        $this->assertEquals(80, $phpunit->columns());
    }

    #[DataProvider('configurationRootOptionsProvider')]
    public function testShouldParseXmlConfigurationRootAttributes(string $optionName, string $optionValue, bool|int|string $expected): void
    {
        $tmpFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit.' . $optionName . uniqid('', true) . '.xml';
        $xml         = "<phpunit {$optionName}='{$optionValue}'></phpunit>" . PHP_EOL;
        file_put_contents($tmpFilename, $xml);

        $configuration = (new Loader)->load($tmpFilename);

        $this->assertFalse($configuration->hasValidationErrors());

        $this->assertEquals($expected, $configuration->phpunit()->{$optionName}());

        @unlink($tmpFilename);
    }

    public function testShouldParseXmlConfigurationExecutionOrderCombined(): void
    {
        $tmpFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit.' . uniqid('', true) . '.xml';
        $xml         = "<phpunit executionOrder='depends,defects'></phpunit>" . PHP_EOL;
        file_put_contents($tmpFilename, $xml);

        $configuration = (new Loader)->load($tmpFilename);

        $this->assertFalse($configuration->hasValidationErrors());

        $this->assertTrue($configuration->phpunit()->defectsFirst());
        $this->assertTrue($configuration->phpunit()->resolveDependencies());

        @unlink($tmpFilename);
    }

    public function testSourceConfigurationIsReadCorrectly(): void
    {
        $source = $this->configuration('configuration_codecoverage.xml')->source();

        $this->assertTrue($source->hasBaseline());
        $this->assertSame(realpath(__DIR__ . '/../../../../_files') . DIRECTORY_SEPARATOR . '.phpunit/baseline.xml', $source->baseline());

        $directory = iterator_to_array($source->includeDirectories(), false)[0];

        $this->assertSame('/path/to/files', $directory->path());
        $this->assertSame('', $directory->prefix());
        $this->assertSame('.php', $directory->suffix());

        $file = iterator_to_array($source->includeFiles(), false)[0];
        $this->assertSame('/path/to/file', $file->path());

        $file = iterator_to_array($source->includeFiles(), false)[1];
        $this->assertSame('/path/to/file', $file->path());

        $directory = iterator_to_array($source->excludeDirectories(), false)[0];
        $this->assertSame('/path/to/files', $directory->path());
        $this->assertSame('', $directory->prefix());
        $this->assertSame('.php', $directory->suffix());

        $file = iterator_to_array($source->excludeFiles(), false)[0];
        $this->assertSame('/path/to/file', $file->path());

        $this->assertSame(
            [
                'functions' => [
                    'PHPUnit\TestFixture\DeprecationTrigger\trigger_deprecation',
                ],
                'methods' => [
                    'PHPUnit\TestFixture\DeprecationTrigger\DeprecationTrigger::triggerDeprecation',
                ],
            ],
            $source->deprecationTriggers(),
        );

        $this->assertTrue($source->ignoreSelfDeprecations());
        $this->assertTrue($source->ignoreDirectDeprecations());
        $this->assertTrue($source->ignoreIndirectDeprecations());
    }

    public function testCodeCoverageConfigurationIsReadCorrectly(): void
    {
        $codeCoverage = $this->configuration('configuration_codecoverage.xml')->codeCoverage();

        $this->assertTrue($codeCoverage->pathCoverage());
        $this->assertTrue($codeCoverage->includeUncoveredFiles());
        $this->assertTrue($codeCoverage->ignoreDeprecatedCodeUnits());
        $this->assertTrue($codeCoverage->disableCodeCoverageIgnore());

        $this->assertTrue($codeCoverage->hasClover());
        $this->assertSame(TEST_FILES_PATH . 'clover.xml', $codeCoverage->clover()->target()->path());

        $this->assertTrue($codeCoverage->hasCobertura());
        $this->assertSame(TEST_FILES_PATH . 'cobertura.xml', $codeCoverage->cobertura()->target()->path());

        $this->assertTrue($codeCoverage->hasCrap4j());
        $this->assertSame(TEST_FILES_PATH . 'crap4j.xml', $codeCoverage->crap4j()->target()->path());

        $defaultColors     = Colors::default();
        $defaultThresholds = Thresholds::default();

        $this->assertTrue($codeCoverage->hasHtml());
        $this->assertSame(TEST_FILES_PATH . 'coverage', $codeCoverage->html()->target()->path());
        $this->assertSame($defaultThresholds->lowUpperBound(), $codeCoverage->html()->lowUpperBound());
        $this->assertSame($defaultThresholds->highLowerBound(), $codeCoverage->html()->highLowerBound());
        $this->assertSame($defaultColors->successLow(), $codeCoverage->html()->colorSuccessLow());
        $this->assertSame($defaultColors->successMedium(), $codeCoverage->html()->colorSuccessMedium());
        $this->assertSame($defaultColors->successHigh(), $codeCoverage->html()->colorSuccessHigh());
        $this->assertSame($defaultColors->warning(), $codeCoverage->html()->colorWarning());
        $this->assertSame($defaultColors->danger(), $codeCoverage->html()->colorDanger());
        $this->assertFalse($codeCoverage->html()->hasCustomCssFile());

        $this->assertTrue($codeCoverage->hasPhp());
        $this->assertSame(TEST_FILES_PATH . 'coverage.php', $codeCoverage->php()->target()->path());

        $this->assertTrue($codeCoverage->hasText());
        $this->assertSame(TEST_FILES_PATH . 'coverage.txt', $codeCoverage->text()->target()->path());
        $this->assertFalse($codeCoverage->text()->showUncoveredFiles());
        $this->assertTrue($codeCoverage->text()->showOnlySummary());

        $this->assertTrue($codeCoverage->hasXml());
        $this->assertSame(TEST_FILES_PATH . 'coverage', $codeCoverage->xml()->target()->path());
    }

    public function testGroupConfigurationIsReadCorrectly(): void
    {
        $groups = $this->configuration('configuration.xml')->groups();

        $this->assertTrue($groups->hasInclude());
        $this->assertSame(['name'], $groups->include()->asArrayOfStrings());

        $this->assertTrue($groups->hasExclude());
        $this->assertSame(['name'], $groups->exclude()->asArrayOfStrings());
    }

    public function testExtensionConfigurationIsReadCorrectly(): void
    {
        $extensions = $this->configuration('configuration.xml')->extensions();

        $this->assertCount(1, $extensions->asArray());

        $extension = $extensions->asArray()[0];

        $this->assertSame('MyExtension', $extension->className());

        $this->assertSame(
            [
                'foo' => 'bar',
                'bar' => 'foo',
            ],
            $extension->parameters(),
        );
    }

    public function testLoggingConfigurationIsReadCorrectly(): void
    {
        $logging = $this->configuration('configuration_logging.xml')->logging();

        $this->assertTrue($logging->hasJunit());
        $this->assertSame(TEST_FILES_PATH . 'junit.xml', $logging->junit()->target()->path());

        $this->assertTrue($logging->hasTeamCity());
        $this->assertSame(TEST_FILES_PATH . 'teamcity.txt', $logging->teamCity()->target()->path());

        $this->assertTrue($logging->hasTestDoxHtml());
        $this->assertSame(TEST_FILES_PATH . 'testdox.html', $logging->testDoxHtml()->target()->path());

        $this->assertTrue($logging->hasTestDoxText());
        $this->assertSame(TEST_FILES_PATH . 'testdox.txt', $logging->testDoxText()->target()->path());
    }

    public function testPHPConfigurationIsReadCorrectly(): void
    {
        $php = $this->configuration('configuration.xml')->php();

        $this->assertSame(TEST_FILES_PATH . '.', $php->includePaths()->asArray()[0]->path());
        $this->assertSame('/path/to/lib', $php->includePaths()->asArray()[1]->path());

        $this->assertSame('foo', $php->iniSettings()->asArray()[0]->name());
        $this->assertSame('bar', $php->iniSettings()->asArray()[0]->value());
        $this->assertSame('highlight.keyword', $php->iniSettings()->asArray()[1]->name());
        $this->assertSame('#123456', $php->iniSettings()->asArray()[1]->value());

        $this->assertSame('FOO', $php->constants()->asArray()[0]->name());
        $this->assertFalse($php->constants()->asArray()[0]->value());
        $this->assertSame('BAR', $php->constants()->asArray()[1]->name());
        $this->assertTrue($php->constants()->asArray()[1]->value());

        $this->assertSame('foo', $php->globalVariables()->asArray()[0]->name());
        $this->assertFalse($php->globalVariables()->asArray()[0]->value());

        $this->assertSame('foo', $php->postVariables()->asArray()[0]->name());
        $this->assertSame('bar', $php->postVariables()->asArray()[0]->value());

        $this->assertSame('foo', $php->getVariables()->asArray()[0]->name());
        $this->assertSame('bar', $php->getVariables()->asArray()[0]->value());

        $this->assertSame('foo', $php->cookieVariables()->asArray()[0]->name());
        $this->assertSame('bar', $php->cookieVariables()->asArray()[0]->value());

        $this->assertSame('foo', $php->serverVariables()->asArray()[0]->name());
        $this->assertSame('bar', $php->serverVariables()->asArray()[0]->value());

        $this->assertSame('foo', $php->filesVariables()->asArray()[0]->name());
        $this->assertSame('bar', $php->filesVariables()->asArray()[0]->value());

        $this->assertSame('foo', $php->requestVariables()->asArray()[0]->name());
        $this->assertSame('bar', $php->requestVariables()->asArray()[0]->value());

        $this->assertSame('foo', $php->envVariables()->asArray()[0]->name());
        $this->assertTrue($php->envVariables()->asArray()[0]->value());
        $this->assertFalse($php->envVariables()->asArray()[0]->force());

        $this->assertSame('foo_force', $php->envVariables()->asArray()[1]->name());
        $this->assertSame('forced', $php->envVariables()->asArray()[1]->value());
        $this->assertTrue($php->envVariables()->asArray()[1]->force());

        $this->assertSame('bar', $php->envVariables()->asArray()[2]->name());
        $this->assertSame('true', $php->envVariables()->asArray()[2]->value());
        $this->assertFalse($php->envVariables()->asArray()[2]->force());
    }

    public function testPHPUnitConfigurationIsReadCorrectly(): void
    {
        $phpunit = $this->configuration('configuration.xml')->phpunit();

        $this->assertTrue($phpunit->backupGlobals());
        $this->assertFalse($phpunit->backupStaticProperties());
        $this->assertFalse($phpunit->beStrictAboutChangesToGlobalState());
        $this->assertSame('/path/to/bootstrap.php', $phpunit->bootstrap());
        $this->assertSame(80, $phpunit->columns());
        $this->assertSame('never', $phpunit->colors());
        $this->assertFalse($phpunit->stderr());
        $this->assertFalse($phpunit->requireCoverageMetadata());
        $this->assertFalse($phpunit->stopOnFailure());
        $this->assertFalse($phpunit->stopOnWarning());
        $this->assertFalse($phpunit->beStrictAboutTestsThatDoNotTestAnything());
        $this->assertFalse($phpunit->beStrictAboutCoverageMetadata());
        $this->assertFalse($phpunit->beStrictAboutOutputDuringTests());
        $this->assertSame(123, $phpunit->defaultTimeLimit());
        $this->assertFalse($phpunit->enforceTimeLimit());
        $this->assertSame('/tmp', $phpunit->extensionsDirectory());
        $this->assertSame('My Test Suite', $phpunit->defaultTestSuite());
        $this->assertSame(1, $phpunit->timeoutForSmallTests());
        $this->assertSame(10, $phpunit->timeoutForMediumTests());
        $this->assertSame(60, $phpunit->timeoutForLargeTests());
        $this->assertFalse($phpunit->failOnEmptyTestSuite());
        $this->assertFalse($phpunit->failOnIncomplete());
        $this->assertFalse($phpunit->failOnRisky());
        $this->assertFalse($phpunit->failOnSkipped());
        $this->assertFalse($phpunit->failOnWarning());
        $this->assertSame(TestSuiteSorter::ORDER_DEFAULT, $phpunit->executionOrder());
        $this->assertFalse($phpunit->defectsFirst());
        $this->assertTrue($phpunit->resolveDependencies());
        $this->assertTrue($phpunit->controlGarbageCollector());
        $this->assertSame(1000, $phpunit->numberOfTestsBeforeGarbageCollection());
        $this->assertSame(10, $phpunit->shortenArraysForExportThreshold());
    }

    public function test_TestDox_configuration_is_parsed_correctly(): void
    {
        $configuration = $this->configuration('configuration_testdox.xml')->phpunit();

        $this->assertTrue($configuration->testdoxPrinter());
        $this->assertTrue($configuration->testdoxPrinterSummary());
    }

    public function testConfigurationForSingleTestSuiteCanBeLoaded(): void
    {
        $testSuites = $this->configuration('configuration_testsuite.xml')->testSuite();

        $this->assertCount(1, $testSuites);

        $first = $testSuites->asArray()[0];
        $this->assertSame('first', $first->name());
        $this->assertCount(1, $first->directories());
        $this->assertSame(TEST_FILES_PATH . 'tests/first', $first->directories()->asArray()[0]->path());
        $this->assertSame('', $first->directories()->asArray()[0]->prefix());
        $this->assertSame('Test.php', $first->directories()->asArray()[0]->suffix());
        $this->assertSame(PHP_VERSION, $first->directories()->asArray()[0]->phpVersion());
        $this->assertSame('>=', $first->directories()->asArray()[0]->phpVersionOperator()->asString());
        $this->assertCount(0, $first->files());
        $this->assertCount(0, $first->exclude());
    }

    public function testConfigurationForMultipleTestSuitesCanBeLoaded(): void
    {
        $testSuites = $this->configuration('configuration_testsuites.xml')->testSuite();

        $this->assertCount(2, $testSuites);

        $first = $testSuites->asArray()[0];
        $this->assertSame('first', $first->name());
        $this->assertCount(1, $first->directories());
        $this->assertSame(TEST_FILES_PATH . 'tests/first', $first->directories()->asArray()[0]->path());
        $this->assertSame('', $first->directories()->asArray()[0]->prefix());
        $this->assertSame('Test.php', $first->directories()->asArray()[0]->suffix());
        $this->assertSame(PHP_VERSION, $first->directories()->asArray()[0]->phpVersion());
        $this->assertSame('>=', $first->directories()->asArray()[0]->phpVersionOperator()->asString());
        $this->assertCount(0, $first->files());
        $this->assertCount(0, $first->exclude());
        $this->assertSame(['foo'], $first->directories()->asArray()[0]->groups());

        $second = $testSuites->asArray()[1];
        $this->assertSame('second', $second->name());
        $this->assertSame(TEST_FILES_PATH . 'tests/second', $second->directories()->asArray()[0]->path());
        $this->assertSame('test', $second->directories()->asArray()[0]->prefix());
        $this->assertSame('.phpt', $second->directories()->asArray()[0]->suffix());
        $this->assertSame('1.2.3', $second->directories()->asArray()[0]->phpVersion());
        $this->assertSame('==', $second->directories()->asArray()[0]->phpVersionOperator()->asString());
        $this->assertCount(1, $second->files());
        $this->assertSame(TEST_FILES_PATH . 'tests/file.php', $second->files()->asArray()[0]->path());
        $this->assertSame('4.5.6', $second->files()->asArray()[0]->phpVersion());
        $this->assertSame('!=', $second->files()->asArray()[0]->phpVersionOperator()->asString());
        $this->assertCount(1, $second->exclude());
        $this->assertSame(TEST_FILES_PATH . 'tests/second/_files', $second->exclude()->asArray()[0]->path());
        $this->assertSame(['bar'], $second->directories()->asArray()[0]->groups());
        $this->assertSame(['baz'], $second->files()->asArray()[0]->groups());
    }

    private function configuration(string $filename): LoadedFromFileConfiguration
    {
        return (new Loader)->load(TEST_FILES_PATH . $filename);
    }
}
