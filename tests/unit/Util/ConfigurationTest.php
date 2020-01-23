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
    protected $configuration;

    protected function setUp(): void
    {
        $this->configuration = Configuration::getInstance(
            TEST_FILES_PATH . 'configuration.xml'
        );
    }

    public function testExceptionIsThrownForNotExistingConfigurationFile(): void
    {
        $this->expectException(Exception::class);

        Configuration::getInstance('not_existing_file.xml');
    }

    public function testShouldReadColorsWhenTrueInConfigurationFile(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.true.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

        $this->assertEquals(DefaultResultPrinter::COLOR_AUTO, $configurationValues->colors());
    }

    public function testShouldReadColorsWhenFalseInConfigurationFile(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.false.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

        $this->assertEquals(DefaultResultPrinter::COLOR_NEVER, $configurationValues->colors());
    }

    public function testShouldReadColorsWhenEmptyInConfigurationFile(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.empty.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

        $this->assertEquals(DefaultResultPrinter::COLOR_NEVER, $configurationValues->colors());
    }

    public function testShouldReadColorsWhenInvalidInConfigurationFile(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.invalid.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

        $this->assertEquals(DefaultResultPrinter::COLOR_NEVER, $configurationValues->colors());
    }

    public function testInvalidConfigurationGeneratesValidationErrors(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.colors.invalid.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);

        $this->assertTrue($configurationInstance->hasValidationErrors());
    }

    public function testShouldUseDefaultValuesForInvalidIntegers(): void
    {
        $configurationFilename = TEST_FILES_PATH . 'configuration.columns.default.xml';
        $configurationInstance = Configuration::getInstance($configurationFilename);
        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();

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

        $configurationInstance = Configuration::getInstance($tmpFilename);
        $this->assertFalse($configurationInstance->hasValidationErrors(), 'option causes validation error');

        $configurationValues   = $configurationInstance->getPHPUnitConfiguration();
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

        $configurationInstance = Configuration::getInstance($tmpFilename);
        $this->assertFalse($configurationInstance->hasValidationErrors(), 'option causes validation error');

        $configurationValues = $configurationInstance->getPHPUnitConfiguration();
        $this->assertTrue($configurationValues->defectsFirst());
        $this->assertTrue($configurationValues->resolveDependencies());

        @\unlink($tmpFilename);
    }

    public function testFilterConfigurationIsReadCorrectly(): void
    {
        $filter = $this->configuration->getFilterConfiguration();

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
        $groupConfiguration = $this->configuration->getGroupConfiguration();

        $this->assertTrue($groupConfiguration->hasInclude());
        $this->assertSame(['name'], $groupConfiguration->include()->asArrayOfStrings());

        $this->assertTrue($groupConfiguration->hasExclude());
        $this->assertSame(['name'], $groupConfiguration->exclude()->asArrayOfStrings());
    }

    public function testTestdoxGroupConfigurationIsReadCorrectly(): void
    {
        $testDoxGroupConfiguration = $this->configuration->getTestdoxGroupConfiguration();

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

        foreach ($this->configuration->getListenerConfiguration() as $listener) {
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
                    $this->assertSame(__FILE__, $listener->sourceFile());
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

        foreach ($this->configuration->getExtensionConfiguration() as $extension) {
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
                    $this->assertSame(__FILE__, $extension->sourceFile());
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
        $loggingConfiguration = $this->configuration->getLoggingConfiguration();

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
        $this->assertEquals(
            [
                'include_path' => [
                    TEST_FILES_PATH . '.',
                    '/path/to/lib',
                ],
                'ini'    => ['foo' => ['value' => 'bar'], 'highlight.keyword' => ['value' => '#123456'], 'highlight.string' => ['value' => 'TEST_FILES_PATH']],
                'const'  => ['FOO' => ['value' => false], 'BAR' => ['value' => true]],
                'var'    => ['foo' => ['value' => false]],
                'env'    => ['foo' => ['value' => true], 'bar' => ['value' => 'true', 'verbatim' => true], 'foo_force' => ['value' => 'forced', 'force' => true]],
                'post'   => ['foo' => ['value' => 'bar']],
                'get'    => ['foo' => ['value' => 'bar']],
                'cookie' => ['foo' => ['value' => 'bar']],
                'server' => ['foo' => ['value' => 'bar']],
                'files'  => ['foo' => ['value' => 'bar']],
                'request'=> ['foo' => ['value' => 'bar']],
            ],
            $this->configuration->getPHPConfiguration()
        );
    }

    /**
     * @testdox PHP configuration is handled correctly
     * @backupGlobals enabled
     */
    public function testPHPConfigurationIsHandledCorrectly(): void
    {
        $savedIniHighlightKeyword = \ini_get('highlight.keyword');
        $savedIniHighlightString  = \ini_get('highlight.string');

        $this->configuration->handlePHPConfiguration();

        $path = TEST_FILES_PATH . '.' . \PATH_SEPARATOR . '/path/to/lib';
        $this->assertStringStartsWith($path, \ini_get('include_path'));
        $this->assertEquals('#123456', \ini_get('highlight.keyword'));
        $this->assertEquals(TEST_FILES_PATH, \ini_get('highlight.string'));
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
        \ini_set('highlight.string', $savedIniHighlightString);
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
        $this->configuration->handlePHPConfiguration();

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
        $this->configuration->handlePHPConfiguration();

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
        $this->configuration->handlePHPConfiguration();

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
        $this->configuration->handlePHPConfiguration();

        $this->assertEquals('forced', $_ENV['foo_force']);
        $this->assertEquals('forced', \getenv('foo_force'));
    }

    /**
     * @testdox PHPUnit configuration is read correctly
     */
    public function testPHPUnitConfigurationIsReadCorrectly(): void
    {
        $configuration = $this->configuration->getPHPUnitConfiguration();

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

    public function testXincludeInConfiguration(): void
    {
        $configurationWithXinclude = Configuration::getInstance(
            TEST_FILES_PATH . 'configuration_xinclude.xml'
        );

        $this->assertConfigurationEquals(
            $this->configuration,
            $configurationWithXinclude
        );
    }

    public function testGetTestSuiteNamesReturnsTheNamesIfDefined(): void
    {
        $configuration = Configuration::getInstance(
            TEST_FILES_PATH . 'configuration.suites.xml'
        );

        $names = $configuration->getTestSuiteNames();

        $this->assertEquals(['Suite One', 'Suite Two'], $names);
    }

    public function testTestSuiteConfigurationForASingleFileInASuite(): void
    {
        $configuration = Configuration::getInstance(
            TEST_FILES_PATH . 'configuration.one-file-suite.xml'
        );

        $config = $configuration->getTestSuiteConfiguration();
        $tests  = $config->tests();

        $this->assertCount(1, $tests);
    }

    public function test_TestDox_configuration_is_parsed_correctly(): void
    {
        $configuration = Configuration::getInstance(
            TEST_FILES_PATH . 'configuration_testdox.xml'
        );

        $config = $configuration->getPHPUnitConfiguration();

        $this->assertSame(CliTestDoxPrinter::class, $config->printerClass());
    }

    public function test_Conflict_between_testdox_and_printerClass_is_detected(): void
    {
        $configuration = Configuration::getInstance(
            TEST_FILES_PATH . 'configuration_testdox_printerClass.xml'
        );

        $config = $configuration->getPHPUnitConfiguration();

        $this->assertSame(CliTestDoxPrinter::class, $config->printerClass());
        $this->assertTrue($config->conflictBetweenPrinterClassAndTestdox());
    }

    /**
     * Asserts that the values in $actualConfiguration equal $expectedConfiguration.
     *
     * @throws Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertConfigurationEquals(Configuration $expectedConfiguration, Configuration $actualConfiguration): void
    {
        $this->assertEquals(
            $expectedConfiguration->getFilterConfiguration(),
            $actualConfiguration->getFilterConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getGroupConfiguration(),
            $actualConfiguration->getGroupConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getListenerConfiguration(),
            $actualConfiguration->getListenerConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getLoggingConfiguration(),
            $actualConfiguration->getLoggingConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getPHPConfiguration(),
            $actualConfiguration->getPHPConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getPHPUnitConfiguration(),
            $actualConfiguration->getPHPUnitConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getTestSuiteConfiguration()->tests(),
            $actualConfiguration->getTestSuiteConfiguration()->tests()
        );
    }
}
