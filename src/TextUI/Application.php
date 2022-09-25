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
use function getcwd;
use function ini_get;
use function ini_set;
use function is_dir;
use function is_file;
use function is_readable;
use function printf;
use function realpath;
use function sprintf;
use function trim;
use PHPUnit\Event;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Extension\ExtensionBootstrapper;
use PHPUnit\Runner\Extension\Facade as ExtensionFacade;
use PHPUnit\Runner\Version;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\CliArguments\Exception as ArgumentsException;
use PHPUnit\TextUI\Command\AtLeastVersionCommand;
use PHPUnit\TextUI\Command\GenerateConfigurationCommand;
use PHPUnit\TextUI\Command\ListGroupsCommand;
use PHPUnit\TextUI\Command\ListTestsAsTextCommand;
use PHPUnit\TextUI\Command\ListTestsAsXmlCommand;
use PHPUnit\TextUI\Command\ListTestSuitesCommand;
use PHPUnit\TextUI\Command\MigrateConfigurationCommand;
use PHPUnit\TextUI\Command\ShowHelpCommand;
use PHPUnit\TextUI\Command\VersionCheckCommand;
use PHPUnit\TextUI\Command\WarmCodeCoverageCacheCommand;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\Configuration\TestSuiteBuilder;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use PHPUnit\TextUI\XmlConfiguration\PhpHandler;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Application
{
    private const SUCCESS_EXIT = 0;

    private const FAILURE_EXIT = 1;

    private const EXCEPTION_EXIT = 2;

    private const CRASH_EXIT = 255;

    /**
     * @psalm-var array<string,mixed>
     */
    private array $longOptions         = [];
    private bool $versionStringPrinted = false;

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

        $suite = $this->handleArguments($argv);

        Event\Facade::emitter()->testSuiteLoaded(Event\TestSuite\TestSuite::fromTestSuite($suite));

        $runner = new TestRunner;

        try {
            $result = $runner->run($suite);

            $returnCode = $this->returnCode($result);
        } catch (Throwable $t) {
            $message = $t->getMessage();

            if (empty(trim($message))) {
                $message = '(no message)';
            }

            printf(
                '%s%sAn error occurred inside PHPUnit.%s%sMessage:  %s%sLocation: %s:%d%s%s%s%s',
                PHP_EOL,
                PHP_EOL,
                PHP_EOL,
                PHP_EOL,
                $message,
                PHP_EOL,
                $t->getFile(),
                $t->getLine(),
                PHP_EOL,
                PHP_EOL,
                $t->getTraceAsString(),
                PHP_EOL
            );

            exit(self::CRASH_EXIT);
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
    private function handleArguments(array $argv): TestSuite
    {
        try {
            $cliConfiguration = (new Builder)->fromParameters($argv, array_keys($this->longOptions));
        } catch (ArgumentsException $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }

        if ($cliConfiguration->hasGenerateConfiguration() && $cliConfiguration->generateConfiguration()) {
            $this->execute(new GenerateConfigurationCommand);
        }

        if ($cliConfiguration->hasAtLeastVersion()) {
            $this->execute(new AtLeastVersionCommand($cliConfiguration->atLeastVersion()));
        }

        if ($cliConfiguration->hasVersion() && $cliConfiguration->version()) {
            $this->printVersionString();

            exit(self::SUCCESS_EXIT);
        }

        if ($cliConfiguration->hasCheckVersion() && $cliConfiguration->checkVersion()) {
            $this->execute(new VersionCheckCommand);
        }

        if ($cliConfiguration->hasHelp()) {
            $this->execute(new ShowHelpCommand(true));
        }

        if ($cliConfiguration->hasUnrecognizedOrderBy()) {
            $this->exitWithErrorMessage(
                sprintf(
                    'unrecognized --order-by option: %s',
                    $cliConfiguration->unrecognizedOrderBy()
                )
            );
        }

        if ($cliConfiguration->hasIniSettings()) {
            foreach ($cliConfiguration->iniSettings() as $name => $value) {
                ini_set($name, $value);
            }
        }

        if ($cliConfiguration->hasIncludePath()) {
            ini_set(
                'include_path',
                $cliConfiguration->includePath() . PATH_SEPARATOR . ini_get('include_path')
            );
        }

        $configurationFile = $this->configurationFilePath($cliConfiguration);

        if ($configurationFile) {
            try {
                $xmlConfiguration = (new Loader)->load($configurationFile);
            } catch (Throwable $e) {
                print $e->getMessage() . PHP_EOL;

                exit(self::FAILURE_EXIT);
            }
        }

        if ($cliConfiguration->hasMigrateConfiguration() && $cliConfiguration->migrateConfiguration()) {
            if (!$configurationFile) {
                print 'No configuration file found to migrate.' . PHP_EOL;

                exit(self::EXCEPTION_EXIT);
            }

            $this->execute(new MigrateConfigurationCommand(realpath($configurationFile)));
        }

        $xmlConfiguration = $xmlConfiguration ?? DefaultConfiguration::create();

        (new PhpHandler)->handle($xmlConfiguration->php());

        $configuration = Registry::init(
            $cliConfiguration,
            $xmlConfiguration
        );

        Event\Facade::emitter()->testRunnerConfigured($configuration);

        try {
            if ($configuration->hasBootstrap()) {
                $this->handleBootstrap($configuration->bootstrap());
            }

            $testSuite = (new TestSuiteBuilder)->build($cliConfiguration, $xmlConfiguration);
        } catch (Exception $e) {
            $this->printVersionString();

            print $e->getMessage() . PHP_EOL;

            exit(self::EXCEPTION_EXIT);
        }

        $extensionBootstrapper = new ExtensionBootstrapper(
            $configuration,
            new ExtensionFacade
        );

        foreach ($configuration->extensionBootstrappers() as $bootstrapper) {
            $extensionBootstrapper->bootstrap(
                $bootstrapper['className'],
                $bootstrapper['parameters']
            );
        }

        if ($configuration->hasCoverageReport() || $cliConfiguration->hasWarmCoverageCache()) {
            CodeCoverageFilterRegistry::init($cliConfiguration, $xmlConfiguration);
        }

        if ($cliConfiguration->hasWarmCoverageCache() && $cliConfiguration->warmCoverageCache()) {
            $this->execute(new WarmCodeCoverageCacheCommand);
        }

        if ($cliConfiguration->hasListGroups() && $cliConfiguration->listGroups()) {
            $this->execute(new ListGroupsCommand($testSuite));
        }

        if ($cliConfiguration->hasListSuites() && $cliConfiguration->listSuites()) {
            $this->execute(new ListTestSuitesCommand($xmlConfiguration->testSuite()));
        }

        if ($cliConfiguration->hasListTests() && $cliConfiguration->listTests()) {
            $this->execute(new ListTestsAsTextCommand($testSuite));
        }

        if ($cliConfiguration->hasListTestsXml() && $cliConfiguration->listTestsXml()) {
            $this->execute(
                new ListTestsAsXmlCommand(
                    $cliConfiguration->listTestsXml(),
                    $testSuite
                )
            );
        }

        if ($testSuite->isEmpty() && !$cliConfiguration->hasArgument() && !$xmlConfiguration->phpunit()->hasDefaultTestSuite()) {
            $this->execute(new ShowHelpCommand(false));
        }

        return $testSuite;
    }

    private function printVersionString(): void
    {
        if ($this->versionStringPrinted) {
            return;
        }

        print Version::getVersionString() . PHP_EOL . PHP_EOL;

        $this->versionStringPrinted = true;
    }

    private function exitWithErrorMessage(string $message): never
    {
        $this->printVersionString();

        print $message . PHP_EOL;

        exit(self::FAILURE_EXIT);
    }

    private function execute(Command\Command $command): never
    {
        $this->printVersionString();

        $result = $command->execute();

        print $result->output();

        if ($result->wasSuccessful()) {
            exit(self::SUCCESS_EXIT);
        }

        exit(self::EXCEPTION_EXIT);
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

        $configuration = Registry::get();

        if ($configuration->failOnEmptyTestSuite() && $result->numberOfTests() === 0) {
            $returnCode = self::FAILURE_EXIT;
        }

        if ($result->wasSuccessfulIgnoringPhpunitWarnings()) {
            if ($configuration->failOnRisky() && $result->hasTestConsideredRiskyEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($configuration->failOnWarning() && $result->hasWarningEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($configuration->failOnIncomplete() && $result->hasTestMarkedIncompleteEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($configuration->failOnSkipped() && $result->hasTestSkippedEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }
        }

        if ($result->hasTestErroredEvents()) {
            $returnCode = self::EXCEPTION_EXIT;
        }

        return $returnCode;
    }

    private function handleBootstrap(string $filename): void
    {
        if (!is_readable($filename)) {
            throw new InvalidBootstrapException($filename);
        }

        try {
            include_once $filename;
        } catch (Throwable $t) {
            throw new BootstrapException($t);
        }

        Facade::emitter()->testRunnerBootstrapFinished($filename);
    }
}
