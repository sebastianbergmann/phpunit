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

use const BAR;
use const DIRECTORY_SEPARATOR;
use const FOO;
use const PATH_SEPARATOR;
use const PHP_EOL;
use const PHP_VERSION;
use function file_put_contents;
use function getenv;
use function ini_get;
use function ini_set;
use function iterator_to_array;
use function putenv;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\StandardTestSuiteLoader;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Filter\Directory;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use stdClass;

/**
 * @medium
 */
final class XmlConfigurationTest extends TestCase
{
    public function testExceptionIsThrownForNotExistingConfigurationFile(): void
    {
        $this->expectException(Exception::class);

        /* @noinspection UnusedFunctionResultInspection */
        $this->configuration('not_existing_file.xml');
    }

    public function testShouldReadColorsWhenTrueInConfigurationFile(): void
    {
        $phpunit = $this->configuration('configuration.colors.true.xml')->phpunit();

        $this->assertEquals(DefaultResultPrinter::COLOR_AUTO, $phpunit->colors());
    }

    public function testShouldReadColorsWhenFalseInConfigurationFile(): void
    {
        $phpunit = $this->configuration('configuration.colors.false.xml')->phpunit();

        $this->assertEquals(DefaultResultPrinter::COLOR_NEVER, $phpunit->colors());
    }

    public function testShouldReadColorsWhenEmptyInConfigurationFile(): void
    {
        $phpunit = $this->configuration('configuration.colors.empty.xml')->phpunit();

        $this->assertEquals(DefaultResultPrinter::COLOR_NEVER, $phpunit->colors());
    }

    public function testShouldReadColorsWhenInvalidInConfigurationFile(): void
    {
        $phpunit = $this->configuration('configuration.colors.invalid.xml')->phpunit();

        $this->assertEquals(DefaultResultPrinter::COLOR_NEVER, $phpunit->colors());
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

    /**
     * @testdox Parse XML configuration root attribute $optionName = $optionValue
     * @dataProvider configurationRootOptionsProvider
     *
     * @group test-reorder
     *
     * @param bool|int|string $expected
     */
    public function testShouldParseXmlConfigurationRootAttributes(string $optionName, string $optionValue, $expected): void
    {
        $tmpFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit.' . $optionName . uniqid('', true) . '.xml';
        $xml         = "<phpunit {$optionName}='{$optionValue}'></phpunit>" . PHP_EOL;
        file_put_contents($tmpFilename, $xml);

        $configuration = (new Loader)->load($tmpFilename);

        $this->assertFalse($configuration->hasValidationErrors());

        $this->assertEquals($expected, $configuration->phpunit()->{$optionName}());

        @unlink($tmpFilename);
    }

    public function configurationRootOptionsProvider(): array
    {
        return [
            'executionOrder default'                          => ['executionOrder', 'default', TestSuiteSorter::ORDER_DEFAULT],
            'executionOrder random'                           => ['executionOrder', 'random', TestSuiteSorter::ORDER_RANDOMIZED],
            'executionOrder reverse'                          => ['executionOrder', 'reverse', TestSuiteSorter::ORDER_REVERSED],
            'executionOrder size'                             => ['executionOrder', 'size', TestSuiteSorter::ORDER_SIZE],
            'cacheResult=false'                               => ['cacheResult', 'false', false],
            'cacheResult=true'                                => ['cacheResult', 'true', true],
            'cacheResultFile absolute path'                   => ['cacheResultFile', '/path/to/result/cache', '/path/to/result/cache'],
            'columns'                                         => ['columns', 'max', 'max'],
            'stopOnFailure'                                   => ['stopOnFailure', 'true', true],
            'stopOnWarning'                                   => ['stopOnWarning', 'true', true],
            'stopOnIncomplete'                                => ['stopOnIncomplete', 'true', true],
            'stopOnRisky'                                     => ['stopOnRisky', 'true', true],
            'stopOnSkipped'                                   => ['stopOnSkipped', 'true', true],
            'failOnEmptyTestSuite'                            => ['failOnEmptyTestSuite', 'true', true],
            'failOnWarning'                                   => ['failOnWarning', 'true', true],
            'failOnRisky'                                     => ['failOnRisky', 'true', true],
            'processIsolation'                                => ['processIsolation', 'true', true],
            'testSuiteLoaderFile absolute path'               => ['testSuiteLoaderFile', '/path/to/file', '/path/to/file'],
            'reverseDefectList'                               => ['reverseDefectList', 'true', true],
            'registerMockObjectsFromTestArgumentsRecursively' => ['registerMockObjectsFromTestArgumentsRecursively', 'true', true],
        ];
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

    public function testCodeCoverageConfigurationIsReadCorrectly(): void
    {
        $codeCoverage = $this->configuration('configuration_codecoverage.xml')->codeCoverage();

        $this->assertSame('/tmp/cache', $codeCoverage->cacheDirectory()->path());

        $this->assertTrue($codeCoverage->pathCoverage());
        $this->assertTrue($codeCoverage->includeUncoveredFiles());
        $this->assertTrue($codeCoverage->processUncoveredFiles());
        $this->assertTrue($codeCoverage->ignoreDeprecatedCodeUnits());
        $this->assertTrue($codeCoverage->disableCodeCoverageIgnore());

        /** @var Directory $directory */
        $directory = iterator_to_array($codeCoverage->directories(), false)[0];
        $this->assertSame('/path/to/files', $directory->path());
        $this->assertSame('', $directory->prefix());
        $this->assertSame('.php', $directory->suffix());
        $this->assertSame('DEFAULT', $directory->group());

        /** @var File $file */
        $file = iterator_to_array($codeCoverage->files(), false)[0];
        $this->assertSame('/path/to/file', $file->path());

        /** @var File $file */
        $file = iterator_to_array($codeCoverage->files(), false)[1];
        $this->assertSame('/path/to/file', $file->path());

        /** @var Directory $directory */
        $directory = iterator_to_array($codeCoverage->excludeDirectories(), false)[0];
        $this->assertSame('/path/to/files', $directory->path());
        $this->assertSame('', $directory->prefix());
        $this->assertSame('.php', $directory->suffix());
        $this->assertSame('DEFAULT', $directory->group());

        /** @var File $file */
        $file = iterator_to_array($codeCoverage->excludeFiles(), false)[0];
        $this->assertSame('/path/to/file', $file->path());

        $this->assertTrue($codeCoverage->hasClover());
        $this->assertSame(TEST_FILES_PATH . 'clover.xml', $codeCoverage->clover()->target()->path());

        $this->assertTrue($codeCoverage->hasCobertura());
        $this->assertSame(TEST_FILES_PATH . 'cobertura.xml', $codeCoverage->cobertura()->target()->path());

        $this->assertTrue($codeCoverage->hasCrap4j());
        $this->assertSame(TEST_FILES_PATH . 'crap4j.xml', $codeCoverage->crap4j()->target()->path());

        $this->assertTrue($codeCoverage->hasHtml());
        $this->assertSame(TEST_FILES_PATH . 'coverage', $codeCoverage->html()->target()->path());
        $this->assertSame(50, $codeCoverage->html()->lowUpperBound());
        $this->assertSame(90, $codeCoverage->html()->highLowerBound());

        $this->assertTrue($codeCoverage->hasPhp());
        $this->assertSame(TEST_FILES_PATH . 'coverage.php', $codeCoverage->php()->target()->path());

        $this->assertTrue($codeCoverage->hasText());
        $this->assertSame(TEST_FILES_PATH . 'coverage.txt', $codeCoverage->text()->target()->path());
        $this->assertFalse($codeCoverage->text()->showUncoveredFiles());
        $this->assertTrue($codeCoverage->text()->showOnlySummary());

        $this->assertTrue($codeCoverage->hasXml());
        $this->assertSame(TEST_FILES_PATH . 'coverage', $codeCoverage->xml()->target()->path());
    }

    public function testLegacyCodeCoverageConfigurationIsReadCorrectly(): void
    {
        $codeCoverage = $this->configuration('configuration_legacy_codecoverage.xml')->codeCoverage();

        $this->assertTrue($codeCoverage->includeUncoveredFiles());
        $this->assertTrue($codeCoverage->processUncoveredFiles());
        $this->assertTrue($codeCoverage->ignoreDeprecatedCodeUnits());
        $this->assertTrue($codeCoverage->disableCodeCoverageIgnore());

        /** @var Directory $directory */
        $directory = iterator_to_array($codeCoverage->directories(), false)[0];
        $this->assertSame('/path/to/files', $directory->path());
        $this->assertSame('', $directory->prefix());
        $this->assertSame('.php', $directory->suffix());
        $this->assertSame('DEFAULT', $directory->group());

        /** @var File $file */
        $file = iterator_to_array($codeCoverage->files(), false)[0];
        $this->assertSame('/path/to/file', $file->path());

        /** @var File $file */
        $file = iterator_to_array($codeCoverage->files(), false)[1];
        $this->assertSame('/path/to/file', $file->path());

        /** @var Directory $directory */
        $directory = iterator_to_array($codeCoverage->excludeDirectories(), false)[0];
        $this->assertSame('/path/to/files', $directory->path());
        $this->assertSame('', $directory->prefix());
        $this->assertSame('.php', $directory->suffix());
        $this->assertSame('DEFAULT', $directory->group());

        /** @var File $file */
        $file = iterator_to_array($codeCoverage->excludeFiles(), false)[0];
        $this->assertSame('/path/to/file', $file->path());

        $this->assertTrue($codeCoverage->hasClover());
        $this->assertSame(TEST_FILES_PATH . 'clover.xml', $codeCoverage->clover()->target()->path());

        $this->assertTrue($codeCoverage->hasCobertura());
        $this->assertSame(TEST_FILES_PATH . 'cobertura.xml', $codeCoverage->cobertura()->target()->path());

        $this->assertTrue($codeCoverage->hasCrap4j());
        $this->assertSame(TEST_FILES_PATH . 'crap4j.xml', $codeCoverage->crap4j()->target()->path());

        $this->assertTrue($codeCoverage->hasHtml());
        $this->assertSame(TEST_FILES_PATH . 'coverage', $codeCoverage->html()->target()->path());
        $this->assertSame(50, $codeCoverage->html()->lowUpperBound());
        $this->assertSame(90, $codeCoverage->html()->highLowerBound());

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

    public function testTestdoxGroupConfigurationIsReadCorrectly(): void
    {
        $testdox = $this->configuration('configuration.xml')->testdoxGroups();

        $this->assertTrue($testdox->hasInclude());
        $this->assertSame(['name'], $testdox->include()->asArrayOfStrings());

        $this->assertTrue($testdox->hasExclude());
        $this->assertSame(['name'], $testdox->exclude()->asArrayOfStrings());
    }

    public function testListenerConfigurationIsReadCorrectly(): void
    {
        $dir         = __DIR__;
        $includePath = ini_get('include_path');

        ini_set('include_path', $dir . PATH_SEPARATOR . $includePath);

        $i = 1;

        foreach ($this->configuration('configuration.xml')->listeners() as $listener) {
            switch ($i) {
                case 1:
                    $this->assertSame('MyListener', $listener->className());
                    $this->assertTrue($listener->hasSourceFile());
                    $this->assertSame('/optional/path/to/MyListener.php', $listener->sourceFile());
                    $this->assertTrue($listener->hasArguments());
                    $this->assertEquals(
                        [
                            0 => [
                                0 => 'Sebastian',
                            ],
                            1 => 22,
                            2 => 'April',
                            3 => 19.78,
                            4 => null,
                            5 => new stdClass,
                            6 => TEST_FILES_PATH . 'MyTestFile.php',
                            7 => TEST_FILES_PATH . 'MyRelativePath',
                            8 => true,
                        ],
                        $listener->arguments()
                    );

                    break;

                case 2:
                    $this->assertSame('IncludePathListener', $listener->className());
                    $this->assertTrue($listener->hasSourceFile());
                    $this->assertSame(TEST_FILES_PATH . 'ConfigurationTest.php', $listener->sourceFile());
                    $this->assertFalse($listener->hasArguments());
                    $this->assertSame([], $listener->arguments());

                    break;

                case 3:
                    $this->assertSame('CompactArgumentsListener', $listener->className());
                    $this->assertTrue($listener->hasSourceFile());
                    $this->assertSame('/CompactArgumentsListener.php', $listener->sourceFile());
                    $this->assertTrue($listener->hasArguments());
                    $this->assertSame([0 => 42, 1 => false], $listener->arguments());

                    break;
            }

            $i++;
        }

        ini_set('include_path', $includePath);
    }

    public function testExtensionConfigurationIsReadCorrectly(): void
    {
        $dir         = __DIR__;
        $includePath = ini_get('include_path');

        ini_set('include_path', $dir . PATH_SEPARATOR . $includePath);

        $i = 1;

        foreach ($this->configuration('configuration.xml')->extensions() as $extension) {
            switch ($i) {
                case 1:
                    $this->assertSame('MyExtension', $extension->className());
                    $this->assertTrue($extension->hasSourceFile());
                    $this->assertSame('/optional/path/to/MyExtension.php', $extension->sourceFile());
                    $this->assertTrue($extension->hasArguments());
                    $this->assertEquals(
                        [
                            0 => [
                                0 => 'Sebastian',
                            ],
                            1 => 22,
                            2 => 'April',
                            3 => 19.78,
                            4 => null,
                            5 => new stdClass,
                            6 => TEST_FILES_PATH . 'MyTestFile.php',
                            7 => TEST_FILES_PATH . 'MyRelativePath',
                        ],
                        $extension->arguments()
                    );

                    break;

                case 2:
                    $this->assertSame('IncludePathExtension', $extension->className());
                    $this->assertTrue($extension->hasSourceFile());
                    $this->assertSame(TEST_FILES_PATH . 'ConfigurationTest.php', $extension->sourceFile());
                    $this->assertFalse($extension->hasArguments());
                    $this->assertSame([], $extension->arguments());

                    break;

                case 3:
                    $this->assertSame('CompactArgumentsExtension', $extension->className());
                    $this->assertTrue($extension->hasSourceFile());
                    $this->assertSame('/CompactArgumentsExtension.php', $extension->sourceFile());
                    $this->assertTrue($extension->hasArguments());
                    $this->assertSame([0 => 42], $extension->arguments());

                    break;
            }

            $i++;
        }

        ini_set('include_path', $includePath);
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

        $this->assertTrue($logging->hasTestDoxXml());
        $this->assertSame(TEST_FILES_PATH . 'testdox.xml', $logging->testDoxXml()->target()->path());

        $this->assertTrue($logging->hasText());
        $this->assertSame(TEST_FILES_PATH . 'logfile.txt', $logging->text()->target()->path());
    }

    public function testLegacyLoggingConfigurationIsReadCorrectly(): void
    {
        $logging = $this->configuration('configuration_legacy_logging.xml')->logging();

        $this->assertTrue($logging->hasJunit());
        $this->assertSame(TEST_FILES_PATH . 'junit.xml', $logging->junit()->target()->path());

        $this->assertTrue($logging->hasTeamCity());
        $this->assertSame(TEST_FILES_PATH . 'teamcity.txt', $logging->teamCity()->target()->path());

        $this->assertTrue($logging->hasTestDoxHtml());
        $this->assertSame(TEST_FILES_PATH . 'testdox.html', $logging->testDoxHtml()->target()->path());

        $this->assertTrue($logging->hasTestDoxText());
        $this->assertSame(TEST_FILES_PATH . 'testdox.txt', $logging->testDoxText()->target()->path());

        $this->assertTrue($logging->hasTestDoxXml());
        $this->assertSame(TEST_FILES_PATH . 'testdox.xml', $logging->testDoxXml()->target()->path());

        $this->assertTrue($logging->hasText());
        $this->assertSame(TEST_FILES_PATH . 'logfile.txt', $logging->text()->target()->path());
    }

    /**
     * @testdox PHP configuration is read correctly
     */
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

    /**
     * @testdox PHP configuration is handled correctly
     * @backupGlobals enabled
     */
    public function testPHPConfigurationIsHandledCorrectly(): void
    {
        $savedIniHighlightKeyword = ini_get('highlight.keyword');

        (new PhpHandler)->handle($this->configuration('configuration.xml')->php());

        $path = TEST_FILES_PATH . '.' . PATH_SEPARATOR . '/path/to/lib';
        $this->assertStringStartsWith($path, ini_get('include_path'));
        $this->assertEquals('#123456', ini_get('highlight.keyword'));
        $this->assertFalse(FOO);
        $this->assertTrue(BAR);
        $this->assertFalse($GLOBALS['foo']);
        $this->assertTrue((bool) $_ENV['foo']);
        $this->assertEquals(1, getenv('foo'));
        $this->assertEquals('bar', $_POST['foo']);
        $this->assertEquals('bar', $_GET['foo']);
        $this->assertEquals('bar', $_COOKIE['foo']);
        $this->assertEquals('bar', $_SERVER['foo']);
        $this->assertEquals('bar', $_FILES['foo']);
        $this->assertEquals('bar', $_REQUEST['foo']);

        ini_set('highlight.keyword', $savedIniHighlightKeyword);
    }

    /**
     * @testdox handlePHPConfiguration() does not overwrite existing $ENV[] variables
     * @backupGlobals enabled
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1181
     */
    public function testHandlePHPConfigurationDoesNotOverwriteExistingEnvArrayVariables(): void
    {
        $_ENV['foo'] = false;

        (new PhpHandler)->handle($this->configuration('configuration.xml')->php());

        $this->assertFalse($_ENV['foo']);
        $this->assertEquals('forced', getenv('foo_force'));
    }

    /**
     * @testdox handlePHPConfiguration() does force overwritten existing $ENV[] variables
     * @backupGlobals enabled
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/2353
     */
    public function testHandlePHPConfigurationDoesForceOverwrittenExistingEnvArrayVariables(): void
    {
        $_ENV['foo_force'] = false;

        (new PhpHandler)->handle($this->configuration('configuration.xml')->php());

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', getenv('foo_force'));
    }

    /**
     * @testdox handlePHPConfiguration() does not overwrite variables from putenv()
     * @backupGlobals enabled
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1181
     */
    public function testHandlePHPConfigurationDoesNotOverwriteVariablesFromPutEnv(): void
    {
        $backupFoo = getenv('foo');

        putenv('foo=putenv');

        (new PhpHandler)->handle($this->configuration('configuration.xml')->php());

        $this->assertEquals('putenv', $_ENV['foo']);
        $this->assertEquals('putenv', getenv('foo'));

        if ($backupFoo === false) {
            putenv('foo');     // delete variable from environment
        } else {
            putenv("foo={$backupFoo}");
        }
    }

    /**
     * @testdox handlePHPConfiguration() does overwrite variables from putenv() when forced
     * @backupGlobals enabled
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1181
     */
    public function testHandlePHPConfigurationDoesOverwriteVariablesFromPutEnvWhenForced(): void
    {
        putenv('foo_force=putenv');

        (new PhpHandler)->handle($this->configuration('configuration.xml')->php());

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', getenv('foo_force'));
    }

    /**
     * @testdox PHPUnit configuration is read correctly
     */
    public function testPHPUnitConfigurationIsReadCorrectly(): void
    {
        $phpunit = $this->configuration('configuration.xml')->phpunit();

        $this->assertTrue($phpunit->backupGlobals());
        $this->assertFalse($phpunit->backupStaticAttributes());
        $this->assertFalse($phpunit->beStrictAboutChangesToGlobalState());
        $this->assertSame('/path/to/bootstrap.php', $phpunit->bootstrap());
        $this->assertSame(80, $phpunit->columns());
        $this->assertSame('never', $phpunit->colors());
        $this->assertFalse($phpunit->stderr());
        $this->assertTrue($phpunit->convertDeprecationsToExceptions());
        $this->assertTrue($phpunit->convertErrorsToExceptions());
        $this->assertTrue($phpunit->convertNoticesToExceptions());
        $this->assertTrue($phpunit->convertWarningsToExceptions());
        $this->assertFalse($phpunit->forceCoversAnnotation());
        $this->assertFalse($phpunit->stopOnFailure());
        $this->assertFalse($phpunit->stopOnWarning());
        $this->assertFalse($phpunit->beStrictAboutTestsThatDoNotTestAnything());
        $this->assertFalse($phpunit->beStrictAboutCoversAnnotation());
        $this->assertFalse($phpunit->beStrictAboutOutputDuringTests());
        $this->assertSame(123, $phpunit->defaultTimeLimit());
        $this->assertFalse($phpunit->enforceTimeLimit());
        $this->assertSame('/tmp', $phpunit->extensionsDirectory());
        $this->assertSame(DefaultResultPrinter::class, $phpunit->printerClass());
        $this->assertSame(StandardTestSuiteLoader::class, $phpunit->testSuiteLoaderClass());
        $this->assertSame('My Test Suite', $phpunit->defaultTestSuite());
        $this->assertFalse($phpunit->verbose());
        $this->assertSame(1, $phpunit->timeoutForSmallTests());
        $this->assertSame(10, $phpunit->timeoutForMediumTests());
        $this->assertSame(60, $phpunit->timeoutForLargeTests());
        $this->assertFalse($phpunit->beStrictAboutResourceUsageDuringSmallTests());
        $this->assertFalse($phpunit->beStrictAboutTodoAnnotatedTests());
        $this->assertFalse($phpunit->failOnEmptyTestSuite());
        $this->assertFalse($phpunit->failOnIncomplete());
        $this->assertFalse($phpunit->failOnRisky());
        $this->assertFalse($phpunit->failOnSkipped());
        $this->assertFalse($phpunit->failOnWarning());
        $this->assertSame(TestSuiteSorter::ORDER_DEFAULT, $phpunit->executionOrder());
        $this->assertFalse($phpunit->defectsFirst());
        $this->assertTrue($phpunit->resolveDependencies());
        $this->assertTrue($phpunit->noInteraction());
    }

    public function test_TestDox_configuration_is_parsed_correctly(): void
    {
        $this->assertSame(
            CliTestDoxPrinter::class,
            $this->configuration('configuration_testdox.xml')->phpunit()->printerClass()
        );
    }

    public function test_Conflict_between_testdox_and_printerClass_is_detected(): void
    {
        $phpunit = $this->configuration('configuration_testdox_printerClass.xml')->phpunit();

        $this->assertSame(CliTestDoxPrinter::class, $phpunit->printerClass());
        $this->assertTrue($phpunit->conflictBetweenPrinterClassAndTestdox());
    }

    public function testConfigurationForSingleTestSuiteCanBeLoaded(): void
    {
        $testsuites = $this->configuration('configuration_testsuite.xml')->testSuite();

        $this->assertCount(1, $testsuites);

        $first = $testsuites->asArray()[0];
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
        $testsuites = $this->configuration('configuration_testsuites.xml')->testSuite();

        $this->assertCount(2, $testsuites);

        $first = $testsuites->asArray()[0];
        $this->assertSame('first', $first->name());
        $this->assertCount(1, $first->directories());
        $this->assertSame(TEST_FILES_PATH . 'tests/first', $first->directories()->asArray()[0]->path());
        $this->assertSame('', $first->directories()->asArray()[0]->prefix());
        $this->assertSame('Test.php', $first->directories()->asArray()[0]->suffix());
        $this->assertSame(PHP_VERSION, $first->directories()->asArray()[0]->phpVersion());
        $this->assertSame('>=', $first->directories()->asArray()[0]->phpVersionOperator()->asString());
        $this->assertCount(0, $first->files());
        $this->assertCount(0, $first->exclude());

        $second = $testsuites->asArray()[1];
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
    }

    private function configuration(string $filename): Configuration
    {
        return (new Loader)->load(TEST_FILES_PATH . $filename);
    }
}
