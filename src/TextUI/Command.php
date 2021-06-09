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
use const STDIN;
use function array_keys;
use function assert;
use function copy;
use function extension_loaded;
use function fgets;
use function file_get_contents;
use function file_put_contents;
use function fopen;
use function getcwd;
use function ini_get;
use function ini_set;
use function is_dir;
use function is_file;
use function printf;
use function realpath;
use function sort;
use function sprintf;
use function str_starts_with;
use function trim;
use function version_compare;
use PHPUnit\Event;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\CliArguments\Exception as ArgumentsException;
use PHPUnit\TextUI\CliArguments\Mapper;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\FilterMapper;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Generator;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use PHPUnit\TextUI\XmlConfiguration\Migrator;
use PHPUnit\TextUI\XmlConfiguration\PhpHandler;
use PHPUnit\Util\TextTestListRenderer;
use PHPUnit\Util\XmlTestListRenderer;
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

        if ($this->arguments['listGroups']) {
            return $this->handleListGroups($suite, $exit);
        }

        if ($this->arguments['listSuites']) {
            return $this->handleListSuites($exit);
        }

        if ($this->arguments['listTests']) {
            return $this->handleListTests($suite, $exit);
        }

        if ($this->arguments['listTestsXml']) {
            return $this->handleListTestsXml($suite, $this->arguments['listTestsXml'], $exit);
        }

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
            $this->generateConfiguration();
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
            $this->handleVersionCheck();
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

        $this->arguments = (new Mapper)->mapToLegacyArray($arguments);

        if (isset($this->arguments['configuration'])) {
            if (is_dir($this->arguments['configuration'])) {
                $candidate = $this->configurationFileInDirectory($this->arguments['configuration']);

                if ($candidate !== null) {
                    $this->arguments['configuration'] = $candidate;
                }
            }
        } elseif ($this->arguments['useDefaultConfiguration']) {
            $candidate = $this->configurationFileInDirectory(getcwd());

            if ($candidate !== null) {
                $this->arguments['configuration'] = $candidate;
            }
        }

        if ($arguments->hasMigrateConfiguration() && $arguments->migrateConfiguration()) {
            if (!isset($this->arguments['configuration'])) {
                print 'No configuration file found to migrate.' . PHP_EOL;

                exit(self::EXCEPTION_EXIT);
            }

            $this->migrateConfiguration(realpath($this->arguments['configuration']));
        }

        if (isset($this->arguments['configuration'])) {
            try {
                $this->arguments['configurationObject'] = (new Loader)->load($this->arguments['configuration']);
            } catch (Throwable $e) {
                print $e->getMessage() . PHP_EOL;

                exit(self::FAILURE_EXIT);
            }

            assert($this->arguments['configurationObject'] instanceof XmlConfiguration);
            Event\Facade::emitter()->testRunnerXmlConfigurationParsed($this->arguments['configurationObject']);

            $phpunitConfiguration = $this->arguments['configurationObject']->phpunit();

            (new PhpHandler)->handle($this->arguments['configurationObject']->php());

            if (isset($this->arguments['bootstrap'])) {
                $this->handleBootstrap($this->arguments['bootstrap']);
            } elseif ($phpunitConfiguration->hasBootstrap()) {
                $this->handleBootstrap($phpunitConfiguration->bootstrap());
            }

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
        } elseif (isset($this->arguments['bootstrap'])) {
            $this->handleBootstrap($this->arguments['bootstrap']);
        }

        if (isset($this->arguments['configurationObject'])) {
            try {
                Configuration::initFromCliAndXml(
                    $arguments,
                    $this->arguments['configurationObject']
                );
            } catch (Exception $e) {
                $this->printVersionString();

                print $e->getMessage() . PHP_EOL;

                exit(self::EXCEPTION_EXIT);
            }

            if (isset($this->arguments['warmCoverageCache'])) {
                $this->handleWarmCoverageCache($this->arguments['configurationObject']);
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
    }

    /**
     * Loads a bootstrap file.
     */
    private function handleBootstrap(string $filename): void
    {
        if (@fopen($filename, 'r') === false) {
            $this->exitWithErrorMessage(
                sprintf(
                    'Cannot open bootstrap script "%s".' . "\n",
                    $filename
                )
            );
        }

        try {
            include_once $filename;
        } catch (Throwable $t) {
            if ($t instanceof \PHPUnit\Exception) {
                $this->exitWithErrorMessage($t->getMessage());
            }

            $this->exitWithErrorMessage(
                sprintf(
                    'Error in bootstrap script: %s:%s%s',
                    $t::class,
                    PHP_EOL,
                    $t->getMessage()
                )
            );
        }

        Event\Facade::emitter()->bootstrapFinished($filename);
    }

    private function handleVersionCheck(): void
    {
        $this->printVersionString();

        $latestVersion = file_get_contents('https://phar.phpunit.de/latest-version-of/phpunit');
        $isOutdated    = version_compare($latestVersion, Version::id(), '>');

        if ($isOutdated) {
            printf(
                'You are not using the latest version of PHPUnit.' . PHP_EOL .
                'The latest version is PHPUnit %s.' . PHP_EOL,
                $latestVersion
            );
        } else {
            print 'You are using the latest version of PHPUnit.' . PHP_EOL;
        }

        exit(self::SUCCESS_EXIT);
    }

    /**
     * Show the help message.
     */
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

    private function handleListGroups(TestSuite $suite, bool $exit): int
    {
        $this->printVersionString();

        print 'Available test group(s):' . PHP_EOL;

        $groups = $suite->getGroups();
        sort($groups);

        foreach ($groups as $group) {
            if (str_starts_with($group, '__phpunit_')) {
                continue;
            }

            printf(
                ' - %s' . PHP_EOL,
                $group
            );
        }

        if ($exit) {
            exit(self::SUCCESS_EXIT);
        }

        return self::SUCCESS_EXIT;
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\TextUI\XmlConfiguration\Exception
     */
    private function handleListSuites(bool $exit): int
    {
        $this->printVersionString();

        print 'Available test suite(s):' . PHP_EOL;

        foreach ($this->arguments['configurationObject']->testSuite() as $testSuite) {
            printf(
                ' - %s' . PHP_EOL,
                $testSuite->name()
            );
        }

        if ($exit) {
            exit(self::SUCCESS_EXIT);
        }

        return self::SUCCESS_EXIT;
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function handleListTests(TestSuite $suite, bool $exit): int
    {
        $this->printVersionString();

        $renderer = new TextTestListRenderer;

        print $renderer->render($suite);

        if ($exit) {
            exit(self::SUCCESS_EXIT);
        }

        return self::SUCCESS_EXIT;
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function handleListTestsXml(TestSuite $suite, string $target, bool $exit): int
    {
        $this->printVersionString();

        $renderer = new XmlTestListRenderer;

        file_put_contents($target, $renderer->render($suite));

        printf(
            'Wrote list of tests that would have been run to %s' . PHP_EOL,
            $target
        );

        if ($exit) {
            exit(self::SUCCESS_EXIT);
        }

        return self::SUCCESS_EXIT;
    }

    private function generateConfiguration(): void
    {
        $this->printVersionString();

        print 'Generating phpunit.xml in ' . getcwd() . PHP_EOL . PHP_EOL;
        print 'Bootstrap script (relative to path shown above; default: vendor/autoload.php): ';

        $bootstrapScript = trim(fgets(STDIN));

        print 'Tests directory (relative to path shown above; default: tests): ';

        $testsDirectory = trim(fgets(STDIN));

        print 'Source directory (relative to path shown above; default: src): ';

        $src = trim(fgets(STDIN));

        print 'Cache directory (relative to path shown above; default: .phpunit.cache): ';

        $cacheDirectory = trim(fgets(STDIN));

        if ($bootstrapScript === '') {
            $bootstrapScript = 'vendor/autoload.php';
        }

        if ($testsDirectory === '') {
            $testsDirectory = 'tests';
        }

        if ($src === '') {
            $src = 'src';
        }

        if ($cacheDirectory === '') {
            $cacheDirectory = '.phpunit.cache';
        }

        $generator = new Generator;

        file_put_contents(
            'phpunit.xml',
            $generator->generateDefaultConfiguration(
                Version::series(),
                $bootstrapScript,
                $testsDirectory,
                $src,
                $cacheDirectory
            )
        );

        print PHP_EOL . 'Generated phpunit.xml in ' . getcwd() . '.' . PHP_EOL;
        print 'Make sure to exclude the ' . $cacheDirectory . ' directory from version control.' . PHP_EOL;

        exit(self::SUCCESS_EXIT);
    }

    private function migrateConfiguration(string $filename): void
    {
        $this->printVersionString();

        copy($filename, $filename . '.bak');

        print 'Created backup:         ' . $filename . '.bak' . PHP_EOL;

        try {
            file_put_contents(
                $filename,
                (new Migrator)->migrate($filename)
            );

            print 'Migrated configuration: ' . $filename . PHP_EOL;
        } catch (Throwable $t) {
            print 'Migration failed: ' . $t->getMessage() . PHP_EOL;

            exit(self::EXCEPTION_EXIT);
        }

        exit(self::SUCCESS_EXIT);
    }

    private function handleWarmCoverageCache(XmlConfiguration $configuration): void
    {
        $this->printVersionString();

        if (isset($this->arguments['coverageCacheDirectory'])) {
            $cacheDirectory = $this->arguments['coverageCacheDirectory'];
        } elseif ($configuration->codeCoverage()->hasCacheDirectory()) {
            $cacheDirectory = $configuration->codeCoverage()->cacheDirectory()->path();
        } else {
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
            $cacheDirectory,
            !$configuration->codeCoverage()->disableCodeCoverageIgnore(),
            $configuration->codeCoverage()->ignoreDeprecatedCodeUnits(),
            $filter
        );

        print 'done [' . $timer->stop()->asString() . ']' . PHP_EOL;

        exit(self::SUCCESS_EXIT);
    }

    private function configurationFileInDirectory(string $directory): ?string
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

        return null;
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
