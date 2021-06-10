<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use const PATH_SEPARATOR;
use const PHP_EOL;
use function array_keys;
use function assert;
use function extension_loaded;
use function getcwd;
use function ini_get;
use function ini_set;
use function is_dir;
use function is_file;
use function realpath;
use function sprintf;
use function version_compare;
use PHPUnit\Event;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\CliArguments\Exception as ArgumentsException;
use PHPUnit\TextUI\CliArguments\Mapper;
use PHPUnit\TextUI\Command\GenerateConfigurationCommand;
use PHPUnit\TextUI\Command\ListGroupsCommand;
use PHPUnit\TextUI\Command\ListTestsAsTextCommand;
use PHPUnit\TextUI\Command\ListTestsAsXmlCommand;
use PHPUnit\TextUI\Command\ListTestSuitesCommand;
use PHPUnit\TextUI\Command\MigrateConfigurationCommand;
use PHPUnit\TextUI\Command\VersionCheckCommand;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\FilterMapper;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use PHPUnit\TextUI\XmlConfiguration\PhpHandler;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\StaticAnalysis\CacheWarmer;
use SebastianBergmann\Timer\Timer;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Command
{
    private const SUCCESS_EXIT = 0;

    private const FAILURE_EXIT = 1;

    private const EXCEPTION_EXIT = 2;

    /**
     * @psalm-var array<string,mixed>
     */
    private array $arguments = [];

    /**
     * @psalm-var array<string,mixed>
     */
    private array $longOptions = [];

    private bool $versionStringPrinted = false;

    /**
     * @psalm-var list<string>
     */
    private array $warnings = [];

    /**
     * @throws Exception
     */
    public static function main(bool $exit = true): int
    {
        try {
            return (new self)->run($_SERVER['argv'], $exit);
        } catch (Throwable $t) {
            throw new RuntimeException(
                $t->getMessage(),
                (int) $t->getCode(),
                $t
            );
        }
    }

    /**
     * @throws Exception
     */
    public function run(array $argv, bool $exit = true): int
    {
        Event\Facade::emitter()->testRunnerStarted();

        $this->handleArguments($argv);

        $runner = new TestRunner;

        if (!Configuration::get()->hasTestSuite()) {
            $this->showHelp();

            exit(self::EXCEPTION_EXIT);
        }

        $suite = Configuration::get()->testSuite();

        Event\Facade::emitter()->testSuiteLoaded($suite);

        try {
            $result = $runner->run($suite, $this->arguments, $this->warnings);

            $returnCode = $this->returnCode($result);
        } catch (Throwable $t) {
            $returnCode = self::EXCEPTION_EXIT;

            print $t->getMessage() . PHP_EOL;
        }

        Event\Facade::emitter()->testRunnerFinished();

        if ($exit) {
            exit($returnCode);
        }

        return $returnCode;
    }

    /**
     * @throws Exception
     */
    private function handleArguments(array $argv): void
    {
        try {
            $arguments = (new Builder)->fromParameters($argv, array_keys($this->longOptions));
        } catch (ArgumentsException $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }

        assert(isset($arguments) && $arguments instanceof CliConfiguration);

        Event\Facade::emitter()->testRunnerCliConfigurationParsed($arguments);

        if ($arguments->hasGenerateConfiguration() && $arguments->generateConfiguration()) {
            $this->execute(new GenerateConfigurationCommand);
        }

        if ($arguments->hasAtLeastVersion()) {
            if (version_compare(Version::id(), $arguments->atLeastVersion(), '>=')) {
                exit(self::SUCCESS_EXIT);
            }

            exit(self::FAILURE_EXIT);
        }

        if ($arguments->hasVersion() && $arguments->version()) {
            $this->printVersionString();

            exit(self::SUCCESS_EXIT);
        }

        if ($arguments->hasCheckVersion() && $arguments->checkVersion()) {
            $this->execute(new VersionCheckCommand);
        }

        if ($arguments->hasHelp()) {
            $this->showHelp();

            exit(self::SUCCESS_EXIT);
        }

        if ($arguments->hasUnrecognizedOrderBy()) {
            $this->exitWithErrorMessage(
                sprintf(
                    'unrecognized --order-by option: %s',
                    $arguments->unrecognizedOrderBy()
                )
            );
        }

        if ($arguments->hasIniSettings()) {
            foreach ($arguments->iniSettings() as $name => $value) {
                ini_set($name, $value);
            }
        }

        if ($arguments->hasIncludePath()) {
            ini_set(
                'include_path',
                $arguments->includePath() . PATH_SEPARATOR . ini_get('include_path')
            );
        }

        $this->arguments   = (new Mapper)->mapToLegacyArray($arguments);
        $configurationFile = $this->configurationFilePath($arguments);

        if ($configurationFile) {
            try {
                $configurationObject = (new Loader)->load($configurationFile);
            } catch (Throwable $e) {
                print $e->getMessage() . PHP_EOL;

                exit(self::FAILURE_EXIT);
            }

            $this->arguments['configuration']       = $configurationFile;
            $this->arguments['configurationObject'] = $configurationObject;

            Event\Facade::emitter()->testRunnerXmlConfigurationParsed($configurationObject);
        }

        if ($arguments->hasMigrateConfiguration() && $arguments->migrateConfiguration()) {
            if (!$configurationFile) {
                print 'No configuration file found to migrate.' . PHP_EOL;

                exit(self::EXCEPTION_EXIT);
            }

            $this->execute(new MigrateConfigurationCommand(realpath($configurationFile)));
        }

        if (isset($configurationObject)) {
            $phpunitConfiguration = $configurationObject->phpunit();

            (new PhpHandler)->handle($configurationObject->php());

            if (!isset($this->arguments['stderr'])) {
                $this->arguments['stderr'] = $phpunitConfiguration->stderr();
            }

            if (!isset($this->arguments['noExtensions']) && $phpunitConfiguration->hasExtensionsDirectory() && extension_loaded('phar')) {
                $result = (new PharLoader())->loadPharExtensionsInDirectory($phpunitConfiguration->extensionsDirectory());

                $this->arguments['loadedExtensions']    = $result['loadedExtensions'];
                $this->arguments['notLoadedExtensions'] = $result['notLoadedExtensions'];

                unset($result);
            }

            if (!isset($this->arguments['columns'])) {
                $this->arguments['columns'] = $phpunitConfiguration->columns();
            }
        }

        if (isset($configurationObject)) {
            try {
                Configuration::initFromCliAndXml(
                    $arguments,
                    $configurationObject
                );
            } catch (Exception $e) {
                $this->printVersionString();

                print $e->getMessage() . PHP_EOL;

                exit(self::EXCEPTION_EXIT);
            }
        } else {
            try {
                Configuration::initFromCli($arguments);
            } catch (Exception $e) {
                $this->printVersionString();

                print $e->getMessage() . PHP_EOL;

                exit(self::EXCEPTION_EXIT);
            }
        }

        Event\Facade::emitter()->testRunnerConfigurationCombined(Configuration::get());

        if (isset($configurationObject) && isset($this->arguments['warmCoverageCache'])) {
            $this->handleWarmCoverageCache($configurationObject);
        }

        if ($arguments->hasListGroups() && $arguments->listGroups()) {
            $this->execute(new ListGroupsCommand(Configuration::get()->testSuite()));
        }

        if (isset($configurationObject) && $arguments->hasListSuites() && $arguments->listSuites()) {
            $this->execute(new ListTestSuitesCommand($configurationObject->testSuite()));
        }

        if ($arguments->hasListTests() && $arguments->listTests()) {
            $this->execute(new ListTestsAsTextCommand(Configuration::get()->testSuite()));
        }

        if ($arguments->hasListTestsXml() && $arguments->listTestsXml()) {
            $this->execute(
                new ListTestsAsXmlCommand(
                    $arguments->listTestsXml(),
                    Configuration::get()->testSuite()
                )
            );
        }
    }

    private function showHelp(): void
    {
        $this->printVersionString();
        (new Help)->writeToConsole();
    }

    private function printVersionString(): void
    {
        if ($this->versionStringPrinted) {
            return;
        }

        print Version::getVersionString() . PHP_EOL . PHP_EOL;

        $this->versionStringPrinted = true;
    }

    private function exitWithErrorMessage(string $message): void
    {
        $this->printVersionString();

        print $message . PHP_EOL;

        exit(self::FAILURE_EXIT);
    }

    private function execute(Command\Command $command): void
    {
        $this->printVersionString();

        $result = $command->execute();

        print $result->output();

        if ($result->wasSuccessful()) {
            exit(self::SUCCESS_EXIT);
        }

        exit(self::EXCEPTION_EXIT);
    }

    private function handleWarmCoverageCache(XmlConfiguration $configuration): void
    {
        $this->printVersionString();

        if (!Configuration::get()->hasCoverageCacheDirectory()) {
            print 'Cache for static analysis has not been configured' . PHP_EOL;

            exit(self::EXCEPTION_EXIT);
        }

        $filter = new Filter;

        if ($configuration->codeCoverage()->hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()) {
            (new FilterMapper)->map(
                $filter,
                $configuration->codeCoverage()
            );
        } elseif (isset($this->arguments['coverageFilter'])) {
            if (!is_array($this->arguments['coverageFilter'])) {
                $coverageFilterDirectories = [$this->arguments['coverageFilter']];
            } else {
                $coverageFilterDirectories = $this->arguments['coverageFilter'];
            }

            foreach ($coverageFilterDirectories as $coverageFilterDirectory) {
                $filter->includeDirectory($coverageFilterDirectory);
            }
        } else {
            print 'Filter for code coverage has not been configured' . PHP_EOL;

            exit(self::EXCEPTION_EXIT);
        }

        $timer = new Timer;
        $timer->start();

        print 'Warming cache for static analysis ... ';

        (new CacheWarmer)->warmCache(
            Configuration::get()->coverageCacheDirectory(),
            !$configuration->codeCoverage()->disableCodeCoverageIgnore(),
            $configuration->codeCoverage()->ignoreDeprecatedCodeUnits(),
            $filter
        );

        print 'done [' . $timer->stop()->asString() . ']' . PHP_EOL;

        exit(self::SUCCESS_EXIT);
    }

    private function configurationFilePath(CliConfiguration $cliConfiguration): string|false
    {
        $useDefaultConfiguration = true;

        if ($cliConfiguration->hasUseDefaultConfiguration()) {
            $useDefaultConfiguration = $cliConfiguration->useDefaultConfiguration();
        }

        if ($cliConfiguration->hasConfiguration()) {
            if (is_dir($cliConfiguration->configuration())) {
                $candidate = $this->configurationFileInDirectory($cliConfiguration->configuration());

                if ($candidate) {
                    return $candidate;
                }

                return false;
            }

            return $cliConfiguration->configuration();
        }

        if ($useDefaultConfiguration) {
            $candidate = $this->configurationFileInDirectory(getcwd());

            if ($candidate) {
                return $candidate;
            }
        }

        return false;
    }

    private function configurationFileInDirectory(string $directory): string|false
    {
        $candidates = [
            $directory . '/phpunit.xml',
            $directory . '/phpunit.dist.xml',
            $directory . '/phpunit.xml.dist',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return realpath($candidate);
            }
        }

        return false;
    }

    private function returnCode(TestResult $result): int
    {
        $returnCode = self::FAILURE_EXIT;

        if ($result->wasSuccessful()) {
            $returnCode = self::SUCCESS_EXIT;
        }

        if (isset($this->arguments['failOnEmptyTestSuite']) && $this->arguments['failOnEmptyTestSuite'] === true && count($result) === 0) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($result->wasSuccessfulIgnoringWarnings()) {
            if ($this->arguments['failOnRisky'] && !$result->allHarmless()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($this->arguments['failOnWarning'] && $result->warningCount() > 0) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($this->arguments['failOnIncomplete'] && $result->notImplementedCount() > 0) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($this->arguments['failOnSkipped'] && $result->skippedCount() > 0) {
                $returnCode = self::FAILURE_EXIT;
            }
        }

        if ($result->errorCount() > 0) {
            $returnCode = self::EXCEPTION_EXIT;
        }

        return $returnCode;
    }
}
