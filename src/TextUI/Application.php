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
use function getcwd;
use function ini_get;
use function ini_set;
use function is_dir;
use function is_file;
use function realpath;
use function sprintf;
use function trim;
use PHPUnit\Event;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Extension\ExtensionHandler;
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
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;
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
    private array $longOptions                  = [];
    private bool $versionStringPrinted          = false;
    private ?XmlConfiguration $xmlConfiguration = null;

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

        if ($suite->isEmpty()) {
            $this->execute(new ShowHelpCommand(false));
        }

        Event\Facade::emitter()->testSuiteLoaded(Event\TestSuite\TestSuite::fromTestSuite($suite));

        $runner = new TestRunner;

        foreach ($this->xmlConfiguration->extensions() as $extension) {
            (new ExtensionHandler)->registerExtension($extension, $runner);
        }

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
            $arguments = (new Builder)->fromParameters($argv, array_keys($this->longOptions));
        } catch (ArgumentsException $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }

        assert(isset($arguments) && $arguments instanceof CliConfiguration);

        if ($arguments->hasGenerateConfiguration() && $arguments->generateConfiguration()) {
            $this->execute(new GenerateConfigurationCommand);
        }

        if ($arguments->hasAtLeastVersion()) {
            $this->execute(new AtLeastVersionCommand($arguments->atLeastVersion()));
        }

        if ($arguments->hasVersion() && $arguments->version()) {
            $this->printVersionString();

            exit(self::SUCCESS_EXIT);
        }

        if ($arguments->hasCheckVersion() && $arguments->checkVersion()) {
            $this->execute(new VersionCheckCommand);
        }

        if ($arguments->hasHelp()) {
            $this->execute(new ShowHelpCommand(true));
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

        $configurationFile = $this->configurationFilePath($arguments);

        if ($configurationFile) {
            try {
                $this->xmlConfiguration = (new Loader)->load($configurationFile);
            } catch (Throwable $e) {
                print $e->getMessage() . PHP_EOL;

                exit(self::FAILURE_EXIT);
            }
        }

        if ($arguments->hasMigrateConfiguration() && $arguments->migrateConfiguration()) {
            if (!$configurationFile) {
                print 'No configuration file found to migrate.' . PHP_EOL;

                exit(self::EXCEPTION_EXIT);
            }

            $this->execute(new MigrateConfigurationCommand(realpath($configurationFile)));
        }

        $this->xmlConfiguration = $this->xmlConfiguration ?? DefaultConfiguration::create();

        (new PhpHandler)->handle($this->xmlConfiguration->php());

        $configuration = Registry::init(
            $arguments,
            $this->xmlConfiguration
        );

        Event\Facade::emitter()->testRunnerConfigured($configuration);

        try {
            if ($configuration->hasBootstrap()) {
                $this->handleBootstrap($configuration->bootstrap());
            }

            $testSuite = (new TestSuiteBuilder)->build($arguments, $this->xmlConfiguration);
        } catch (Exception $e) {
            $this->printVersionString();

            print $e->getMessage() . PHP_EOL;

            exit(self::EXCEPTION_EXIT);
        }

        if ($configuration->hasCoverageReport() || $arguments->hasWarmCoverageCache()) {
            CodeCoverageFilterRegistry::init($arguments, $this->xmlConfiguration);
        }

        if ($arguments->hasWarmCoverageCache() && $arguments->warmCoverageCache()) {
            $this->execute(new WarmCodeCoverageCacheCommand);
        }

        if ($arguments->hasListGroups() && $arguments->listGroups()) {
            $this->execute(new ListGroupsCommand($testSuite));
        }

        if ($arguments->hasListSuites() && $arguments->listSuites()) {
            $this->execute(new ListTestSuitesCommand($this->xmlConfiguration->testSuite()));
        }

        if ($arguments->hasListTests() && $arguments->listTests()) {
            $this->execute(new ListTestsAsTextCommand($testSuite));
        }

        if ($arguments->hasListTestsXml() && $arguments->listTestsXml()) {
            $this->execute(
                new ListTestsAsXmlCommand(
                    $arguments->listTestsXml(),
                    $testSuite
                )
            );
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

        if ($result->wasSuccessfulIgnoringWarnings()) {
            if ($configuration->failOnRisky() && $result->hasTestConsideredRiskyEvents()) {
                $returnCode = self::FAILURE_EXIT;
            }

            if ($configuration->failOnWarning() && $result->hasTestPassedWithWarningEvents()) {
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

        Facade::emitter()->bootstrapFinished($filename);
    }
}
