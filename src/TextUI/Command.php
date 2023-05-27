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
use function class_exists;
use function copy;
use function extension_loaded;
use function fgets;
use function file_get_contents;
use function file_put_contents;
use function get_class;
use function getcwd;
use function ini_get;
use function ini_set;
use function is_array;
use function is_callable;
use function is_dir;
use function is_file;
use function is_string;
use function printf;
use function realpath;
use function sort;
use function sprintf;
use function stream_resolve_include_path;
use function strpos;
use function trim;
use function version_compare;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\StandardTestSuiteLoader;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\CliArguments\Configuration;
use PHPUnit\TextUI\CliArguments\Exception as ArgumentsException;
use PHPUnit\TextUI\CliArguments\Mapper;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\FilterMapper;
use PHPUnit\TextUI\XmlConfiguration\Generator;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use PHPUnit\TextUI\XmlConfiguration\Migrator;
use PHPUnit\TextUI\XmlConfiguration\PhpHandler;
use PHPUnit\Util\FileLoader;
use PHPUnit\Util\Filesystem;
use PHPUnit\Util\Printer;
use PHPUnit\Util\TextTestListRenderer;
use PHPUnit\Util\Xml\SchemaDetector;
use PHPUnit\Util\XmlTestListRenderer;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\StaticAnalysis\CacheWarmer;
use SebastianBergmann\Timer\Timer;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
class Command
{
    /**
     * @var array<string,mixed>
     */
    protected $arguments = [];

    /**
     * @var array<string,mixed>
     */
    protected $longOptions = [];

    /**
     * @var bool
     */
    private $versionStringPrinted = false;

    /**
     * @psalm-var list<string>
     */
    private $warnings = [];

    /**
     * @throws Exception
     */
    public static function main(bool $exit = true): int
    {
        try {
            return (new static)->run($_SERVER['argv'], $exit);
        } catch (Throwable $t) {
            throw new RuntimeException(
                $t->getMessage(),
                (int) $t->getCode(),
                $t,
            );
        }
    }

    /**
     * @throws Exception
     */
    public function run(array $argv, bool $exit = true): int
    {
        $this->handleArguments($argv);

        $runner = $this->createRunner();

        if ($this->arguments['test'] instanceof TestSuite) {
            $suite = $this->arguments['test'];
        } else {
            $suite = $runner->getTest(
                $this->arguments['test'],
                $this->arguments['testSuffixes'],
            );
        }

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

        unset($this->arguments['test'], $this->arguments['testFile']);

        try {
            $result = $runner->run($suite, $this->arguments, $this->warnings, $exit);
        } catch (Throwable $t) {
            print $t->getMessage() . PHP_EOL;
        }

        $return = TestRunner::FAILURE_EXIT;

        if (isset($result) && $result->wasSuccessful()) {
            $return = TestRunner::SUCCESS_EXIT;
        } elseif (!isset($result) || $result->errorCount() > 0) {
            $return = TestRunner::EXCEPTION_EXIT;
        }

        if ($exit) {
            exit($return);
        }

        return $return;
    }

    /**
     * Create a TestRunner, override in subclasses.
     */
    protected function createRunner(): TestRunner
    {
        return new TestRunner($this->arguments['loader']);
    }

    /**
     * Handles the command-line arguments.
     *
     * A child class of PHPUnit\TextUI\Command can hook into the argument
     * parsing by adding the switch(es) to the $longOptions array and point to a
     * callback method that handles the switch(es) in the child class like this
     *
     * <code>
     * <?php
     * class MyCommand extends PHPUnit\TextUI\Command
     * {
     *     public function __construct()
     *     {
     *         // my-switch won't accept a value, it's an on/off
     *         $this->longOptions['my-switch'] = 'myHandler';
     *         // my-secondswitch will accept a value - note the equals sign
     *         $this->longOptions['my-secondswitch='] = 'myOtherHandler';
     *     }
     *
     *     // --my-switch  -> myHandler()
     *     protected function myHandler()
     *     {
     *     }
     *
     *     // --my-secondswitch foo -> myOtherHandler('foo')
     *     protected function myOtherHandler ($value)
     *     {
     *     }
     *
     *     // You will also need this - the static keyword in the
     *     // PHPUnit\TextUI\Command will mean that it'll be
     *     // PHPUnit\TextUI\Command that gets instantiated,
     *     // not MyCommand
     *     public static function main($exit = true)
     *     {
     *         $command = new static;
     *
     *         return $command->run($_SERVER['argv'], $exit);
     *     }
     *
     * }
     * </code>
     *
     * @throws Exception
     */
    protected function handleArguments(array $argv): void
    {
        try {
            $arguments = (new Builder)->fromParameters($argv, array_keys($this->longOptions));
        } catch (ArgumentsException $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }

        assert(isset($arguments) && $arguments instanceof Configuration);

        if ($arguments->hasGenerateConfiguration() && $arguments->generateConfiguration()) {
            $this->generateConfiguration();
        }

        if ($arguments->hasAtLeastVersion()) {
            if (version_compare(Version::id(), $arguments->atLeastVersion(), '>=')) {
                exit(TestRunner::SUCCESS_EXIT);
            }

            exit(TestRunner::FAILURE_EXIT);
        }

        if ($arguments->hasVersion() && $arguments->version()) {
            $this->printVersionString();

            exit(TestRunner::SUCCESS_EXIT);
        }

        if ($arguments->hasCheckVersion() && $arguments->checkVersion()) {
            $this->handleVersionCheck();
        }

        if ($arguments->hasHelp()) {
            $this->showHelp();

            exit(TestRunner::SUCCESS_EXIT);
        }

        if ($arguments->hasUnrecognizedOrderBy()) {
            $this->exitWithErrorMessage(
                sprintf(
                    'unrecognized --order-by option: %s',
                    $arguments->unrecognizedOrderBy(),
                ),
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
                $arguments->includePath() . PATH_SEPARATOR . ini_get('include_path'),
            );
        }

        $this->arguments = (new Mapper)->mapToLegacyArray($arguments);

        $this->handleCustomOptions($arguments->unrecognizedOptions());
        $this->handleCustomTestSuite();

        if (!isset($this->arguments['testSuffixes'])) {
            $this->arguments['testSuffixes'] = ['Test.php', '.phpt'];
        }

        if (!isset($this->arguments['test']) && $arguments->hasArgument()) {
            $this->arguments['test'] = realpath($arguments->argument());

            if ($this->arguments['test'] === false) {
                $this->exitWithErrorMessage(
                    sprintf(
                        'Cannot open file "%s".',
                        $arguments->argument(),
                    ),
                );
            }
        }

        if ($this->arguments['loader'] !== null) {
            $this->arguments['loader'] = $this->handleLoader($this->arguments['loader']);
        }

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

                exit(TestRunner::EXCEPTION_EXIT);
            }

            $this->migrateConfiguration(realpath($this->arguments['configuration']));
        }

        if (isset($this->arguments['configuration'])) {
            try {
                $this->arguments['configurationObject'] = (new Loader)->load($this->arguments['configuration']);
            } catch (Throwable $e) {
                print $e->getMessage() . PHP_EOL;

                exit(TestRunner::FAILURE_EXIT);
            }

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
                $result = (new PharLoader)->loadPharExtensionsInDirectory($phpunitConfiguration->extensionsDirectory());

                $this->arguments['loadedExtensions']    = $result['loadedExtensions'];
                $this->arguments['notLoadedExtensions'] = $result['notLoadedExtensions'];

                unset($result);
            }

            if (!isset($this->arguments['columns'])) {
                $this->arguments['columns'] = $phpunitConfiguration->columns();
            }

            if (!isset($this->arguments['printer']) && $phpunitConfiguration->hasPrinterClass()) {
                $file = $phpunitConfiguration->hasPrinterFile() ? $phpunitConfiguration->printerFile() : '';

                $this->arguments['printer'] = $this->handlePrinter(
                    $phpunitConfiguration->printerClass(),
                    $file,
                );
            }

            if ($phpunitConfiguration->hasTestSuiteLoaderClass()) {
                $file = $phpunitConfiguration->hasTestSuiteLoaderFile() ? $phpunitConfiguration->testSuiteLoaderFile() : '';

                $this->arguments['loader'] = $this->handleLoader(
                    $phpunitConfiguration->testSuiteLoaderClass(),
                    $file,
                );
            }

            if (!isset($this->arguments['testsuite']) && $phpunitConfiguration->hasDefaultTestSuite()) {
                $this->arguments['testsuite'] = $phpunitConfiguration->defaultTestSuite();
            }

            if (!isset($this->arguments['test'])) {
                try {
                    $this->arguments['test'] = (new TestSuiteMapper)->map(
                        $this->arguments['configurationObject']->testSuite(),
                        $this->arguments['testsuite'] ?? '',
                    );
                } catch (Exception $e) {
                    $this->printVersionString();

                    print $e->getMessage() . PHP_EOL;

                    exit(TestRunner::EXCEPTION_EXIT);
                }
            }
        } elseif (isset($this->arguments['bootstrap'])) {
            $this->handleBootstrap($this->arguments['bootstrap']);
        }

        if (isset($this->arguments['printer']) && is_string($this->arguments['printer'])) {
            $this->arguments['printer'] = $this->handlePrinter($this->arguments['printer']);
        }

        if (isset($this->arguments['configurationObject'], $this->arguments['warmCoverageCache'])) {
            $this->handleWarmCoverageCache($this->arguments['configurationObject']);
        }

        if (!isset($this->arguments['test'])) {
            $this->showHelp();

            exit(TestRunner::EXCEPTION_EXIT);
        }
    }

    /**
     * Handles the loading of the PHPUnit\Runner\TestSuiteLoader implementation.
     *
     * @deprecated see https://github.com/sebastianbergmann/phpunit/issues/4039
     */
    protected function handleLoader(string $loaderClass, string $loaderFile = ''): ?TestSuiteLoader
    {
        $this->warnings[] = 'Using a custom test suite loader is deprecated';

        if (!class_exists($loaderClass, false)) {
            if ($loaderFile == '') {
                $loaderFile = Filesystem::classNameToFilename(
                    $loaderClass,
                );
            }

            $loaderFile = stream_resolve_include_path($loaderFile);

            if ($loaderFile) {
                /**
                 * @noinspection PhpIncludeInspection
                 *
                 * @psalm-suppress UnresolvableInclude
                 */
                require $loaderFile;
            }
        }

        if (class_exists($loaderClass, false)) {
            try {
                $class = new ReflectionClass($loaderClass);
                // @codeCoverageIgnoreStart
            } catch (\ReflectionException $e) {
                throw new ReflectionException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e,
                );
            }
            // @codeCoverageIgnoreEnd

            if ($class->implementsInterface(TestSuiteLoader::class) && $class->isInstantiable()) {
                $object = $class->newInstance();

                assert($object instanceof TestSuiteLoader);

                return $object;
            }
        }

        if ($loaderClass == StandardTestSuiteLoader::class) {
            return null;
        }

        $this->exitWithErrorMessage(
            sprintf(
                'Could not use "%s" as loader.',
                $loaderClass,
            ),
        );

        return null;
    }

    /**
     * Handles the loading of the PHPUnit\Util\Printer implementation.
     *
     * @return null|Printer|string
     */
    protected function handlePrinter(string $printerClass, string $printerFile = '')
    {
        if (!class_exists($printerClass, false)) {
            if ($printerFile === '') {
                $printerFile = Filesystem::classNameToFilename(
                    $printerClass,
                );
            }

            $printerFile = stream_resolve_include_path($printerFile);

            if ($printerFile) {
                /**
                 * @noinspection PhpIncludeInspection
                 *
                 * @psalm-suppress UnresolvableInclude
                 */
                require $printerFile;
            }
        }

        if (!class_exists($printerClass)) {
            $this->exitWithErrorMessage(
                sprintf(
                    'Could not use "%s" as printer: class does not exist',
                    $printerClass,
                ),
            );
        }

        try {
            $class = new ReflectionClass($printerClass);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
            // @codeCoverageIgnoreEnd
        }

        if (!$class->implementsInterface(ResultPrinter::class)) {
            $this->exitWithErrorMessage(
                sprintf(
                    'Could not use "%s" as printer: class does not implement %s',
                    $printerClass,
                    ResultPrinter::class,
                ),
            );
        }

        if (!$class->isInstantiable()) {
            $this->exitWithErrorMessage(
                sprintf(
                    'Could not use "%s" as printer: class cannot be instantiated',
                    $printerClass,
                ),
            );
        }

        if ($class->isSubclassOf(ResultPrinter::class)) {
            return $printerClass;
        }

        $outputStream = isset($this->arguments['stderr']) ? 'php://stderr' : null;

        return $class->newInstance($outputStream);
    }

    /**
     * Loads a bootstrap file.
     */
    protected function handleBootstrap(string $filename): void
    {
        try {
            FileLoader::checkAndLoad($filename);
        } catch (Throwable $t) {
            if ($t instanceof \PHPUnit\Exception) {
                $this->exitWithErrorMessage($t->getMessage());
            }

            $message = sprintf(
                'Error in bootstrap script: %s:%s%s%s%s',
                get_class($t),
                PHP_EOL,
                $t->getMessage(),
                PHP_EOL,
                $t->getTraceAsString(),
            );

            while ($t = $t->getPrevious()) {
                $message .= sprintf(
                    '%s%sPrevious error: %s:%s%s%s%s',
                    PHP_EOL,
                    PHP_EOL,
                    get_class($t),
                    PHP_EOL,
                    $t->getMessage(),
                    PHP_EOL,
                    $t->getTraceAsString(),
                );
            }

            $this->exitWithErrorMessage($message);
        }
    }

    protected function handleVersionCheck(): void
    {
        $this->printVersionString();

        $latestVersion = file_get_contents('https://phar.phpunit.de/latest-version-of/phpunit');
        $isOutdated    = version_compare($latestVersion, Version::id(), '>');

        if ($isOutdated) {
            printf(
                'You are not using the latest version of PHPUnit.' . PHP_EOL .
                'The latest version is PHPUnit %s.' . PHP_EOL,
                $latestVersion,
            );
        } else {
            print 'You are using the latest version of PHPUnit.' . PHP_EOL;
        }

        exit(TestRunner::SUCCESS_EXIT);
    }

    /**
     * Show the help message.
     */
    protected function showHelp(): void
    {
        $this->printVersionString();
        (new Help)->writeToConsole();
    }

    /**
     * Custom callback for test suite discovery.
     */
    protected function handleCustomTestSuite(): void
    {
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

        exit(TestRunner::FAILURE_EXIT);
    }

    private function handleListGroups(TestSuite $suite, bool $exit): int
    {
        $this->printVersionString();

        $this->warnAboutConflictingOptions(
            'listGroups',
            [
                'filter',
                'groups',
                'excludeGroups',
                'testsuite',
            ],
        );

        print 'Available test group(s):' . PHP_EOL;

        $groups = $suite->getGroups();
        sort($groups);

        foreach ($groups as $group) {
            if (strpos($group, '__phpunit_') === 0) {
                continue;
            }

            printf(
                ' - %s' . PHP_EOL,
                $group,
            );
        }

        if ($exit) {
            exit(TestRunner::SUCCESS_EXIT);
        }

        return TestRunner::SUCCESS_EXIT;
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\TextUI\XmlConfiguration\Exception
     */
    private function handleListSuites(bool $exit): int
    {
        $this->printVersionString();

        $this->warnAboutConflictingOptions(
            'listSuites',
            [
                'filter',
                'groups',
                'excludeGroups',
                'testsuite',
            ],
        );

        print 'Available test suite(s):' . PHP_EOL;

        foreach ($this->arguments['configurationObject']->testSuite() as $testSuite) {
            printf(
                ' - %s' . PHP_EOL,
                $testSuite->name(),
            );
        }

        if ($exit) {
            exit(TestRunner::SUCCESS_EXIT);
        }

        return TestRunner::SUCCESS_EXIT;
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function handleListTests(TestSuite $suite, bool $exit): int
    {
        $this->printVersionString();

        $this->warnAboutConflictingOptions(
            'listTests',
            [
                'filter',
                'groups',
                'excludeGroups',
            ],
        );

        $renderer = new TextTestListRenderer;

        print $renderer->render($suite);

        if ($exit) {
            exit(TestRunner::SUCCESS_EXIT);
        }

        return TestRunner::SUCCESS_EXIT;
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function handleListTestsXml(TestSuite $suite, string $target, bool $exit): int
    {
        $this->printVersionString();

        $this->warnAboutConflictingOptions(
            'listTestsXml',
            [
                'filter',
                'groups',
                'excludeGroups',
            ],
        );

        $renderer = new XmlTestListRenderer;

        file_put_contents($target, $renderer->render($suite));

        printf(
            'Wrote list of tests that would have been run to %s' . PHP_EOL,
            $target,
        );

        if ($exit) {
            exit(TestRunner::SUCCESS_EXIT);
        }

        return TestRunner::SUCCESS_EXIT;
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
                $cacheDirectory,
            ),
        );

        print PHP_EOL . 'Generated phpunit.xml in ' . getcwd() . '.' . PHP_EOL;
        print 'Make sure to exclude the ' . $cacheDirectory . ' directory from version control.' . PHP_EOL;

        exit(TestRunner::SUCCESS_EXIT);
    }

    private function migrateConfiguration(string $filename): void
    {
        $this->printVersionString();

        if (!(new SchemaDetector)->detect($filename)->detected()) {
            print $filename . ' does not need to be migrated.' . PHP_EOL;

            exit(TestRunner::EXCEPTION_EXIT);
        }

        copy($filename, $filename . '.bak');

        print 'Created backup:         ' . $filename . '.bak' . PHP_EOL;

        try {
            file_put_contents(
                $filename,
                (new Migrator)->migrate($filename),
            );

            print 'Migrated configuration: ' . $filename . PHP_EOL;
        } catch (Throwable $t) {
            print 'Migration failed: ' . $t->getMessage() . PHP_EOL;

            exit(TestRunner::EXCEPTION_EXIT);
        }

        exit(TestRunner::SUCCESS_EXIT);
    }

    private function handleCustomOptions(array $unrecognizedOptions): void
    {
        foreach ($unrecognizedOptions as $name => $value) {
            if (isset($this->longOptions[$name])) {
                $handler = $this->longOptions[$name];
            }

            $name .= '=';

            if (isset($this->longOptions[$name])) {
                $handler = $this->longOptions[$name];
            }

            if (isset($handler) && is_callable([$this, $handler])) {
                $this->{$handler}($value);

                unset($handler);
            }
        }
    }

    private function handleWarmCoverageCache(XmlConfiguration\Configuration $configuration): void
    {
        $this->printVersionString();

        if (isset($this->arguments['coverageCacheDirectory'])) {
            $cacheDirectory = $this->arguments['coverageCacheDirectory'];
        } elseif ($configuration->codeCoverage()->hasCacheDirectory()) {
            $cacheDirectory = $configuration->codeCoverage()->cacheDirectory()->path();
        } else {
            print 'Cache for static analysis has not been configured' . PHP_EOL;

            exit(TestRunner::EXCEPTION_EXIT);
        }

        $filter = new Filter;

        if ($configuration->codeCoverage()->hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()) {
            (new FilterMapper)->map(
                $filter,
                $configuration->codeCoverage(),
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

            exit(TestRunner::EXCEPTION_EXIT);
        }

        $timer = new Timer;
        $timer->start();

        print 'Warming cache for static analysis ... ';

        (new CacheWarmer)->warmCache(
            $cacheDirectory,
            !$configuration->codeCoverage()->disableCodeCoverageIgnore(),
            $configuration->codeCoverage()->ignoreDeprecatedCodeUnits(),
            $filter,
        );

        print 'done [' . $timer->stop()->asString() . ']' . PHP_EOL;

        exit(TestRunner::SUCCESS_EXIT);
    }

    private function configurationFileInDirectory(string $directory): ?string
    {
        $candidates = [
            $directory . '/phpunit.xml',
            $directory . '/phpunit.xml.dist',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return realpath($candidate);
            }
        }

        return null;
    }

    /**
     * @psalm-param "listGroups"|"listSuites"|"listTests"|"listTestsXml"|"filter"|"groups"|"excludeGroups"|"testsuite" $key
     * @psalm-param list<"listGroups"|"listSuites"|"listTests"|"listTestsXml"|"filter"|"groups"|"excludeGroups"|"testsuite"> $keys
     */
    private function warnAboutConflictingOptions(string $key, array $keys): void
    {
        $warningPrinted = false;

        foreach ($keys as $_key) {
            if (!empty($this->arguments[$_key])) {
                printf(
                    'The %s and %s options cannot be combined, %s is ignored' . PHP_EOL,
                    $this->mapKeyToOptionForWarning($_key),
                    $this->mapKeyToOptionForWarning($key),
                    $this->mapKeyToOptionForWarning($_key),
                );

                $warningPrinted = true;
            }
        }

        if ($warningPrinted) {
            print PHP_EOL;
        }
    }

    /**
     * @psalm-param "listGroups"|"listSuites"|"listTests"|"listTestsXml"|"filter"|"groups"|"excludeGroups"|"testsuite" $key
     */
    private function mapKeyToOptionForWarning(string $key): string
    {
        switch ($key) {
            case 'listGroups':
                return '--list-groups';

            case 'listSuites':
                return '--list-suites';

            case 'listTests':
                return '--list-tests';

            case 'listTestsXml':
                return '--list-tests-xml';

            case 'filter':
                return '--filter';

            case 'groups':
                return '--group';

            case 'excludeGroups':
                return '--exclude-group';

            case 'testsuite':
                return '--testsuite';
        }
    }
}
