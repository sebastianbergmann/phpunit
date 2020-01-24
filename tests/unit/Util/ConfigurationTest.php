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

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\StandardTestSuiteLoader;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;

/**
 * @small
 */
final class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    protected function setUp(): void
    {
        $this->configuration = Registry::getInstance()->get(
            TEST_FILES_PATH . 'configuration.xml'
        );
    }

    public function testExceptionIsThrownForNotExistingConfigurationFile(): void
    {
        $this->expectException(Exception::class);

        Registry::getInstance()->get('not_existing_file.xml');
    }

    public function testShouldReadColorsWhenTrueInConfigurationFile(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.true.xml';
        $configurationInstance = Registry::getInstance()->get($configurationFilename);
        $configurationValues   = $configurationInstance->phpunit();

        $this->assertEquals(DefaultResultPrinter::COLOR_AUTO, $configurationValues->colors());
    }

    public function testShouldReadColorsWhenFalseInConfigurationFile(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.false.xml';
        $configurationInstance = Registry::getInstance()->get($configurationFilename);
        $configurationValues   = $configurationInstance->phpunit();

        $this->assertEquals(DefaultResultPrinter::COLOR_NEVER, $configurationValues->colors());
    }

    public function testShouldReadColorsWhenEmptyInConfigurationFile(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.empty.xml';
        $configurationInstance = Registry::getInstance()->get($configurationFilename);
        $configurationValues   = $configurationInstance->phpunit();

        $this->assertEquals(DefaultResultPrinter::COLOR_NEVER, $configurationValues->colors());
    }

    public function testShouldReadColorsWhenInvalidInConfigurationFile(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.invalid.xml';
        $configurationInstance = Registry::getInstance()->get($configurationFilename);
        $configurationValues   = $configurationInstance->phpunit();

        $this->assertEquals(DefaultResultPrinter::COLOR_NEVER, $configurationValues->colors());
    }

    public function testInvalidConfigurationGeneratesValidationErrors(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.invalid.xml';
        $configurationInstance = Registry::getInstance()->get($configurationFilename);

        $this->assertTrue($configurationInstance->hasValidationErrors());
    }

    public function testShouldUseDefaultValuesForInvalidIntegers(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.columns.default.xml';
        $configurationInstance = Registry::getInstance()->get($configurationFilename);
        $configurationValues   = $configurationInstance->phpunit();

        $this->assertEquals(80, $configurationValues->columns());
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
        $tmpFilename = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'phpunit.' . $optionName . \uniqid() . '.xml';
        $xml         = "<phpunit $optionName='$optionValue'></phpunit>" . \PHP_EOL;
        \file_put_contents($tmpFilename, $xml);

        $configurationInstance = Registry::getInstance()->get($tmpFilename);
        $this->assertFalse($configurationInstance->hasValidationErrors(), 'option causes validation error');

        $configurationValues   = $configurationInstance->phpunit();
        $this->assertEquals($expected, $configurationValues->$optionName());

        @\unlink($tmpFilename);
    }

    public function configurationRootOptionsProvider(): array
    {
        return [
            'executionOrder default'                         => ['executionOrder', 'default', TestSuiteSorter::ORDER_DEFAULT],
            'executionOrder random'                          => ['executionOrder', 'random', TestSuiteSorter::ORDER_RANDOMIZED],
            'executionOrder reverse'                         => ['executionOrder', 'reverse', TestSuiteSorter::ORDER_REVERSED],
            'executionOrder size'                            => ['executionOrder', 'size', TestSuiteSorter::ORDER_SIZE],
            'cacheResult=false'                              => ['cacheResult', 'false', false],
            'cacheResult=true'                               => ['cacheResult', 'true', true],
            'cacheResultFile absolute path'                  => ['cacheResultFile', '/path/to/result/cache', '/path/to/result/cache'],
            'columns'                                        => ['columns', 'max', 'max'],
            'stopOnFailure'                                  => ['stopOnFailure', 'true', true],
            'stopOnWarning'                                  => ['stopOnWarning', 'true', true],
            'stopOnIncomplete'                               => ['stopOnIncomplete', 'true', true],
            'stopOnRisky'                                    => ['stopOnRisky', 'true', true],
            'stopOnSkipped'                                  => ['stopOnSkipped', 'true', true],
            'failOnWarning'                                  => ['failOnWarning', 'true', true],
            'failOnRisky'                                    => ['failOnRisky', 'true', true],
            'disableCodeCoverageIgnore'                      => ['disableCodeCoverageIgnore', 'true', true],
            'processIsolation'                               => ['processIsolation', 'true', true],
            'testSuiteLoaderFile absolute path'              => ['testSuiteLoaderFile', '/path/to/file', '/path/to/file'],
            'reverseDefectList'                              => ['reverseDefectList', 'true', true],
            'registerMockObjectsFromTestArgumentsRecursively'=> ['registerMockObjectsFromTestArgumentsRecursively', 'true', true],
        ];
    }

    public function testShouldParseXmlConfigurationExecutionOrderCombined(): void
    {
        $tmpFilename = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'phpunit.' . \uniqid() . '.xml';
        $xml         = "<phpunit executionOrder='depends,defects'></phpunit>" . \PHP_EOL;
        \file_put_contents($tmpFilename, $xml);

        $configurationInstance = Registry::getInstance()->get($tmpFilename);
        $this->assertFalse($configurationInstance->hasValidationErrors(), 'option causes validation error');

        $configurationValues = $configurationInstance->phpunit();
        $this->assertTrue($configurationValues->defectsFirst());
        $this->assertTrue($configurationValues->resolveDependencies());

        @\unlink($tmpFilename);
    }

    public function testFilterConfigurationIsReadCorrectly(): void
    {
        $filter = $this->configuration->filter();

        $this->assertTrue($filter->addUncoveredFilesFromWhitelist());
        $this->assertFalse($filter->processUncoveredFilesFromWhitelist());

        /** @var FilterDirectory $directory */
        $directory = \iterator_to_array($filter->directories(), false)[0];
        $this->assertSame('/path/to/files', $directory->path());
        $this->assertSame('', $directory->prefix());
        $this->assertSame('.php', $directory->suffix());
        $this->assertSame('DEFAULT', $directory->group());

        /** @var FilterFile $file */
        $file = \iterator_to_array($filter->files(), false)[0];
        $this->assertSame('/path/to/file', $file->path());

        /** @var FilterFile $file */
        $file = \iterator_to_array($filter->files(), false)[1];
        $this->assertSame('/path/to/file', $file->path());

        /** @var FilterDirectory $directory */
        $directory = \iterator_to_array($filter->excludeDirectories(), false)[0];
        $this->assertSame('/path/to/files', $directory->path());
        $this->assertSame('', $directory->prefix());
        $this->assertSame('.php', $directory->suffix());
        $this->assertSame('DEFAULT', $directory->group());

        /** @var FilterFile $file */
        $file = \iterator_to_array($filter->excludeFiles(), false)[0];
        $this->assertSame('/path/to/file', $file->path());
    }

    public function testGroupConfigurationIsReadCorrectly(): void
    {
        $groupConfiguration = $this->configuration->groups();

        $this->assertTrue($groupConfiguration->hasInclude());
        $this->assertSame(['name'], $groupConfiguration->include()->asArrayOfStrings());

        $this->assertTrue($groupConfiguration->hasExclude());
        $this->assertSame(['name'], $groupConfiguration->exclude()->asArrayOfStrings());
    }

    public function testTestdoxGroupConfigurationIsReadCorrectly(): void
    {
        $testDoxGroupConfiguration = $this->configuration->testdoxGroups();

        $this->assertTrue($testDoxGroupConfiguration->hasInclude());
        $this->assertSame(['name'], $testDoxGroupConfiguration->include()->asArrayOfStrings());

        $this->assertTrue($testDoxGroupConfiguration->hasExclude());
        $this->assertSame(['name'], $testDoxGroupConfiguration->exclude()->asArrayOfStrings());
    }

    public function testListenerConfigurationIsReadCorrectly(): void
    {
        $dir         = __DIR__;
        $includePath = \ini_get('include_path');

        \ini_set('include_path', $dir . \PATH_SEPARATOR . $includePath);

        $i = 1;

        foreach ($this->configuration->listeners() as $listener) {
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
                            5 => new \stdClass,
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

        \ini_set('include_path', $includePath);
    }

    public function testExtensionConfigurationIsReadCorrectly(): void
    {
        $dir         = __DIR__;
        $includePath = \ini_get('include_path');

        \ini_set('include_path', $dir . \PATH_SEPARATOR . $includePath);

        $i = 1;

        foreach ($this->configuration->extensions() as $extension) {
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
                            5 => new \stdClass,
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

        \ini_set('include_path', $includePath);
    }

    public function testLoggingConfigurationIsReadCorrectly(): void
    {
        $loggingConfiguration = $this->configuration->logging();

        $this->assertTrue($loggingConfiguration->hasCodeCoverageHtml());
        $this->assertSame('/tmp/report', $loggingConfiguration->codeCoverageHtml()->target()->path());
        $this->assertSame(50, $loggingConfiguration->codeCoverageHtml()->lowUpperBound());
        $this->assertSame(90, $loggingConfiguration->codeCoverageHtml()->highLowerBound());

        $this->assertTrue($loggingConfiguration->hasCodeCoverageClover());
        $this->assertSame('/tmp/clover.xml', $loggingConfiguration->codeCoverageClover()->target()->path());

        $this->assertTrue($loggingConfiguration->hasCodeCoverageCrap4j());
        $this->assertSame('/tmp/crap4j.xml', $loggingConfiguration->codeCoverageCrap4j()->target()->path());
        $this->assertSame(50, $loggingConfiguration->codeCoverageCrap4j()->threshold());

        $this->assertTrue($loggingConfiguration->hasCodeCoverageText());
        $this->assertSame('/tmp/coverage.txt', $loggingConfiguration->codeCoverageText()->target()->path());
        $this->assertTrue($loggingConfiguration->codeCoverageText()->showUncoveredFiles());
        $this->assertTrue($loggingConfiguration->codeCoverageText()->showOnlySummary());

        $this->assertTrue($loggingConfiguration->hasPlainText());
        $this->assertSame('/tmp/logfile.txt', $loggingConfiguration->plainText()->target()->path());

        $this->assertTrue($loggingConfiguration->hasJunit());
        $this->assertSame('/tmp/logfile.xml', $loggingConfiguration->junit()->target()->path());

        $this->assertTrue($loggingConfiguration->hasTestDoxHtml());
        $this->assertSame('/tmp/testdox.html', $loggingConfiguration->testDoxHtml()->target()->path());

        $this->assertTrue($loggingConfiguration->hasTestDoxText());
        $this->assertSame('/tmp/testdox.txt', $loggingConfiguration->testDoxText()->target()->path());

        $this->assertTrue($loggingConfiguration->hasTestDoxXml());
        $this->assertSame('/tmp/testdox.xml', $loggingConfiguration->testDoxXml()->target()->path());
    }

    /**
     * @testdox PHP configuration is read correctly
     */
    public function testPHPConfigurationIsReadCorrectly(): void
    {
        $configuration = $this->configuration->php();

        $this->assertSame(TEST_FILES_PATH . '.', $configuration->includePaths()->asArray()[0]->path());
        $this->assertSame('/path/to/lib', $configuration->includePaths()->asArray()[1]->path());

        $this->assertSame('foo', $configuration->iniSettings()->asArray()[0]->name());
        $this->assertSame('bar', $configuration->iniSettings()->asArray()[0]->value());
        $this->assertSame('highlight.keyword', $configuration->iniSettings()->asArray()[1]->name());
        $this->assertSame('#123456', $configuration->iniSettings()->asArray()[1]->value());

        $this->assertSame('FOO', $configuration->constants()->asArray()[0]->name());
        $this->assertFalse($configuration->constants()->asArray()[0]->value());
        $this->assertSame('BAR', $configuration->constants()->asArray()[1]->name());
        $this->assertTrue($configuration->constants()->asArray()[1]->value());

        $this->assertSame('foo', $configuration->globalVariables()->asArray()[0]->name());
        $this->assertFalse($configuration->globalVariables()->asArray()[0]->value());

        $this->assertSame('foo', $configuration->postVariables()->asArray()[0]->name());
        $this->assertSame('bar', $configuration->postVariables()->asArray()[0]->value());

        $this->assertSame('foo', $configuration->getVariables()->asArray()[0]->name());
        $this->assertSame('bar', $configuration->getVariables()->asArray()[0]->value());

        $this->assertSame('foo', $configuration->cookieVariables()->asArray()[0]->name());
        $this->assertSame('bar', $configuration->cookieVariables()->asArray()[0]->value());

        $this->assertSame('foo', $configuration->serverVariables()->asArray()[0]->name());
        $this->assertSame('bar', $configuration->serverVariables()->asArray()[0]->value());

        $this->assertSame('foo', $configuration->filesVariables()->asArray()[0]->name());
        $this->assertSame('bar', $configuration->filesVariables()->asArray()[0]->value());

        $this->assertSame('foo', $configuration->requestVariables()->asArray()[0]->name());
        $this->assertSame('bar', $configuration->requestVariables()->asArray()[0]->value());

        $this->assertSame('foo', $configuration->envVariables()->asArray()[0]->name());
        $this->assertTrue($configuration->envVariables()->asArray()[0]->value());
        $this->assertFalse($configuration->envVariables()->asArray()[0]->force());

        $this->assertSame('foo_force', $configuration->envVariables()->asArray()[1]->name());
        $this->assertSame('forced', $configuration->envVariables()->asArray()[1]->value());
        $this->assertTrue($configuration->envVariables()->asArray()[1]->force());

        $this->assertSame('bar', $configuration->envVariables()->asArray()[2]->name());
        $this->assertSame('true', $configuration->envVariables()->asArray()[2]->value());
        $this->assertFalse($configuration->envVariables()->asArray()[2]->force());
    }

    /**
     * @testdox PHP configuration is handled correctly
     * @backupGlobals enabled
     */
    public function testPHPConfigurationIsHandledCorrectly(): void
    {
        $savedIniHighlightKeyword = \ini_get('highlight.keyword');

        (new PhpHandler)->handle($this->configuration->php());

        $path = TEST_FILES_PATH . '.' . \PATH_SEPARATOR . '/path/to/lib';
        $this->assertStringStartsWith($path, \ini_get('include_path'));
        $this->assertEquals('#123456', \ini_get('highlight.keyword'));
        $this->assertFalse(\FOO);
        $this->assertTrue(\BAR);
        $this->assertFalse($GLOBALS['foo']);
        $this->assertTrue((bool) $_ENV['foo']);
        $this->assertEquals(1, \getenv('foo'));
        $this->assertEquals('bar', $_POST['foo']);
        $this->assertEquals('bar', $_GET['foo']);
        $this->assertEquals('bar', $_COOKIE['foo']);
        $this->assertEquals('bar', $_SERVER['foo']);
        $this->assertEquals('bar', $_FILES['foo']);
        $this->assertEquals('bar', $_REQUEST['foo']);

        \ini_set('highlight.keyword', $savedIniHighlightKeyword);
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

        (new PhpHandler)->handle($this->configuration->php());

        $this->assertFalse($_ENV['foo']);
        $this->assertEquals('forced', \getenv('foo_force'));
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

        (new PhpHandler)->handle($this->configuration->php());

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', \getenv('foo_force'));
    }

    /**
     * @testdox handlePHPConfiguration() does not overwrite variables from putenv()
     * @backupGlobals enabled
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1181
     */
    public function testHandlePHPConfigurationDoesNotOverwriteVariablesFromPutEnv(): void
    {
        $backupFoo = \getenv('foo');

        \putenv('foo=putenv');

        (new PhpHandler)->handle($this->configuration->php());

        $this->assertEquals('putenv', $_ENV['foo']);
        $this->assertEquals('putenv', \getenv('foo'));

        if ($backupFoo === false) {
            \putenv('foo');     // delete variable from environment
        } else {
            \putenv("foo=$backupFoo");
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
        \putenv('foo_force=putenv');

        (new PhpHandler)->handle($this->configuration->php());

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', \getenv('foo_force'));
    }

    /**
     * @testdox PHPUnit configuration is read correctly
     */
    public function testPHPUnitConfigurationIsReadCorrectly(): void
    {
        $configuration = $this->configuration->phpunit();

        $this->assertTrue($configuration->backupGlobals());
        $this->assertFalse($configuration->backupStaticAttributes());
        $this->assertFalse($configuration->beStrictAboutChangesToGlobalState());
        $this->assertSame('/path/to/bootstrap.php', $configuration->bootstrap());
        $this->assertFalse($configuration->cacheTokens());
        $this->assertSame(80, $configuration->columns());
        $this->assertSame('never', $configuration->colors());
        $this->assertFalse($configuration->stderr());
        $this->assertTrue($configuration->convertDeprecationsToExceptions());
        $this->assertTrue($configuration->convertErrorsToExceptions());
        $this->assertTrue($configuration->convertNoticesToExceptions());
        $this->assertTrue($configuration->convertWarningsToExceptions());
        $this->assertFalse($configuration->forceCoversAnnotation());
        $this->assertFalse($configuration->stopOnFailure());
        $this->assertFalse($configuration->stopOnWarning());
        $this->assertFalse($configuration->beStrictAboutTestsThatDoNotTestAnything());
        $this->assertFalse($configuration->beStrictAboutCoversAnnotation());
        $this->assertFalse($configuration->beStrictAboutOutputDuringTests());
        $this->assertSame(123, $configuration->defaultTimeLimit());
        $this->assertFalse($configuration->enforceTimeLimit());
        $this->assertSame('/tmp', $configuration->extensionsDirectory());
        $this->assertSame(DefaultResultPrinter::class, $configuration->printerClass());
        $this->assertSame(StandardTestSuiteLoader::class, $configuration->testSuiteLoaderClass());
        $this->assertSame('My Test Suite', $configuration->defaultTestSuite());
        $this->assertFalse($configuration->verbose());
        $this->assertSame(1, $configuration->timeoutForSmallTests());
        $this->assertSame(10, $configuration->timeoutForMediumTests());
        $this->assertSame(60, $configuration->timeoutForLargeTests());
        $this->assertFalse($configuration->beStrictAboutResourceUsageDuringSmallTests());
        $this->assertFalse($configuration->beStrictAboutTodoAnnotatedTests());
        $this->assertFalse($configuration->failOnWarning());
        $this->assertFalse($configuration->failOnRisky());
        $this->assertFalse($configuration->ignoreDeprecatedCodeUnitsFromCodeCoverage());
        $this->assertSame(TestSuiteSorter::ORDER_DEFAULT, $configuration->executionOrder());
        $this->assertFalse($configuration->defectsFirst());
        $this->assertTrue($configuration->resolveDependencies());
        $this->assertTrue($configuration->noInteraction());
    }

    public function test_TestDox_configuration_is_parsed_correctly(): void
    {
        $configuration = Registry::getInstance()->get(
            TEST_FILES_PATH . 'configuration_testdox.xml'
        );

        $config = $configuration->phpunit();

        $this->assertSame(CliTestDoxPrinter::class, $config->printerClass());
    }

    public function test_Conflict_between_testdox_and_printerClass_is_detected(): void
    {
        $configuration = Registry::getInstance()->get(
            TEST_FILES_PATH . 'configuration_testdox_printerClass.xml'
        );

        $config = $configuration->phpunit();

        $this->assertSame(CliTestDoxPrinter::class, $config->printerClass());
        $this->assertTrue($config->conflictBetweenPrinterClassAndTestdox());
    }

    public function testConfigurationForSingleTestSuiteCanBeLoaded(): void
    {
        $configuration = Registry::getInstance()->get(
            TEST_FILES_PATH . 'configuration_testsuite.xml'
        )->testSuite();

        $this->assertCount(1, $configuration);

        $first = $configuration->asArray()[0];
        $this->assertSame('first', $first->name());
        $this->assertCount(1, $first->directories());
        $this->assertSame(TEST_FILES_PATH . 'tests/first', $first->directories()->asArray()[0]->path());
        $this->assertSame('', $first->directories()->asArray()[0]->prefix());
        $this->assertSame('Test.php', $first->directories()->asArray()[0]->suffix());
        $this->assertSame(\PHP_VERSION, $first->directories()->asArray()[0]->phpVersion());
        $this->assertSame('>=', $first->directories()->asArray()[0]->phpVersionOperator());
        $this->assertCount(0, $first->files());
        $this->assertCount(0, $first->exclude());
    }

    public function testConfigurationForMultipleTestSuitesCanBeLoaded(): void
    {
        $configuration = Registry::getInstance()->get(
            TEST_FILES_PATH . 'configuration_testsuites.xml'
        )->testSuite();

        $this->assertCount(2, $configuration);

        $first = $configuration->asArray()[0];
        $this->assertSame('first', $first->name());
        $this->assertCount(1, $first->directories());
        $this->assertSame(TEST_FILES_PATH . 'tests/first', $first->directories()->asArray()[0]->path());
        $this->assertSame('', $first->directories()->asArray()[0]->prefix());
        $this->assertSame('Test.php', $first->directories()->asArray()[0]->suffix());
        $this->assertSame(\PHP_VERSION, $first->directories()->asArray()[0]->phpVersion());
        $this->assertSame('>=', $first->directories()->asArray()[0]->phpVersionOperator());
        $this->assertCount(0, $first->files());
        $this->assertCount(0, $first->exclude());

        $second = $configuration->asArray()[1];
        $this->assertSame('second', $second->name());
        $this->assertSame(TEST_FILES_PATH . 'tests/second', $second->directories()->asArray()[0]->path());
        $this->assertSame('test', $second->directories()->asArray()[0]->prefix());
        $this->assertSame('.phpt', $second->directories()->asArray()[0]->suffix());
        $this->assertSame('1.2.3', $second->directories()->asArray()[0]->phpVersion());
        $this->assertSame('==', $second->directories()->asArray()[0]->phpVersionOperator());
        $this->assertCount(1, $second->files());
        $this->assertSame(TEST_FILES_PATH . 'tests/file.php', $second->files()->asArray()[0]->path());
        $this->assertSame('4.5.6', $second->files()->asArray()[0]->phpVersion());
        $this->assertSame('!=', $second->files()->asArray()[0]->phpVersionOperator());
        $this->assertCount(1, $second->exclude());
        $this->assertSame(TEST_FILES_PATH . 'tests/second/_files', $second->exclude()->asArray()[0]->path());
    }
}
