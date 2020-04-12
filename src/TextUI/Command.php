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

use PharIo\Manifest\ApplicationName;
use PharIo\Manifest\Exception as ManifestException;
use PharIo\Manifest\ManifestLoader;
use PharIo\Version\Version as PharIoVersion;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\StandardTestSuiteLoader;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\Arguments\Arguments;
use PHPUnit\TextUI\Arguments\ArgumentsBuilder;
use PHPUnit\TextUI\Arguments\ArgumentsMapper;
use PHPUnit\TextUI\Arguments\Exception as ArgumentsException;
use PHPUnit\TextUI\Configuration\Generator;
use PHPUnit\TextUI\Configuration\PhpHandler;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\Configuration\TestSuiteMapper;
use PHPUnit\Util\FileLoader;
use PHPUnit\Util\Filesystem;
use PHPUnit\Util\Printer;
use PHPUnit\Util\TextTestListRenderer;
use PHPUnit\Util\XmlTestListRenderer;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

/**
 * A TestRunner for the Command Line Interface (CLI)
 * PHP SAPI Module.
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
     * @throws \PHPUnit\Framework\Exception
     */
    public static function main(bool $exit = true): int
    {
        return (new static)->run($_SERVER['argv'], $exit);
    }

    /**
     * @throws Exception
     */
    public function run(array $argv, bool $exit = true): int
    {
        $this->handleArguments($argv);

        $runner = $this->createRunner();

        if ($this->arguments['test'] instanceof Test) {
            $suite = $this->arguments['test'];
        } else {
            $suite = $runner->getTest(
                $this->arguments['test'],
                $this->arguments['testSuffixes']
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
        } catch (Exception $e) {
            print $e->getMessage() . \PHP_EOL;
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
            $arguments = (new ArgumentsBuilder)->fromParameters($argv, \array_keys($this->longOptions));
        } catch (ArgumentsException $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }

        \assert(isset($arguments) && $arguments instanceof Arguments);

        if ($arguments->hasGenerateConfiguration() && $arguments->generateConfiguration()) {
            $this->printVersionString();

            print 'Generating phpunit.xml in ' . \getcwd() . \PHP_EOL . \PHP_EOL;
            print 'Bootstrap script (relative to path shown above; default: vendor/autoload.php): ';

            $bootstrapScript = \trim(\fgets(\STDIN));

            print 'Tests directory (relative to path shown above; default: tests): ';

            $testsDirectory = \trim(\fgets(\STDIN));

            print 'Source directory (relative to path shown above; default: src): ';

            $src = \trim(\fgets(\STDIN));

            if ($bootstrapScript === '') {
                $bootstrapScript = 'vendor/autoload.php';
            }

            if ($testsDirectory === '') {
                $testsDirectory = 'tests';
            }

            if ($src === '') {
                $src = 'src';
            }

            $generator = new Generator;

            \file_put_contents(
                'phpunit.xml',
                $generator->generateDefaultConfiguration(
                    Version::series(),
                    $bootstrapScript,
                    $testsDirectory,
                    $src
                )
            );

            print \PHP_EOL . 'Generated phpunit.xml in ' . \getcwd() . \PHP_EOL;

            exit(TestRunner::SUCCESS_EXIT);
        }

        if ($arguments->hasAtLeastVersion()) {
            if (\version_compare(Version::id(), $arguments->atLeastVersion(), '>=')) {
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
                \sprintf(
                    'unrecognized --order-by option: %s',
                    $arguments->unrecognizedOrderBy()
                )
            );
        }

        if ($arguments->hasIniSettings()) {
            foreach ($arguments->iniSettings() as $name => $value) {
                \ini_set($name, $value);
            }
        }

        if ($arguments->hasIncludePath()) {
            \ini_set(
                'include_path',
                $arguments->includePath() . \PATH_SEPARATOR . \ini_get('include_path')
            );
        }

        $this->arguments = (new ArgumentsMapper)->mapToLegacyArray($arguments);

        if ($arguments->hasUnrecognizedOptions()) {
            foreach ($arguments->unrecognizedOptions() as $name => $value) {
                if (isset($this->longOptions[$name])) {
                    $handler = $this->longOptions[$name];
                } elseif (isset($this->longOptions[$name . '='])) {
                    $handler = $this->longOptions[$name . '='];
                }

                if (isset($handler) && \is_callable([$this, $handler])) {
                    $this->$handler($value);

                    unset($handler);
                }
            }
        }

        $this->handleCustomTestSuite();

        if (!isset($this->arguments['testSuffixes'])) {
            $this->arguments['testSuffixes'] = ['Test.php', '.phpt'];
        }

        if (!isset($this->arguments['test']) && $arguments->hasArgument()) {
            $this->arguments['test'] = \realpath($arguments->argument());

            if ($this->arguments['test'] === false) {
                $this->exitWithErrorMessage(
                    \sprintf(
                        'Cannot open file "%s".',
                        $arguments->argument()
                    )
                );
            }
        }

        if ($this->arguments['loader'] !== null) {
            $this->arguments['loader'] = $this->handleLoader($this->arguments['loader']);
        }

        if (isset($this->arguments['configuration']) && \is_dir($this->arguments['configuration'])) {
            $configurationFile = $this->arguments['configuration'] . '/phpunit.xml';

            if (\file_exists($configurationFile)) {
                $this->arguments['configuration'] = \realpath(
                    $configurationFile
                );
            } elseif (\file_exists($configurationFile . '.dist')) {
                $this->arguments['configuration'] = \realpath(
                    $configurationFile . '.dist'
                );
            }
        } elseif (!isset($this->arguments['configuration']) && $this->arguments['useDefaultConfiguration']) {
            if (\file_exists('phpunit.xml')) {
                $this->arguments['configuration'] = \realpath('phpunit.xml');
            } elseif (\file_exists('phpunit.xml.dist')) {
                $this->arguments['configuration'] = \realpath(
                    'phpunit.xml.dist'
                );
            }
        }

        if (isset($this->arguments['configuration'])) {
            try {
                $configuration = Registry::getInstance()->get($this->arguments['configuration']);
            } catch (\Throwable $e) {
                print $e->getMessage() . \PHP_EOL;
                exit(TestRunner::FAILURE_EXIT);
            }

            $phpunitConfiguration = $configuration->phpunit();

            (new PhpHandler)->handle($configuration->php());

            if (isset($this->arguments['bootstrap'])) {
                $this->handleBootstrap($this->arguments['bootstrap']);
            } elseif ($phpunitConfiguration->hasBootstrap()) {
                $this->handleBootstrap($phpunitConfiguration->bootstrap());
            }

            if (!isset($this->arguments['stderr'])) {
                $this->arguments['stderr'] = $phpunitConfiguration->stderr();
            }

            if (!isset($this->arguments['noExtensions']) && $phpunitConfiguration->hasExtensionsDirectory() && \extension_loaded('phar')) {
                $this->handleExtensions($phpunitConfiguration->extensionsDirectory());
            }

            if (!isset($this->arguments['columns'])) {
                $this->arguments['columns'] = $phpunitConfiguration->columns();
            }

            if (!isset($this->arguments['printer']) && $phpunitConfiguration->hasPrinterClass()) {
                $file = $phpunitConfiguration->hasPrinterFile() ? $phpunitConfiguration->printerFile() : '';

                $this->arguments['printer'] = $this->handlePrinter(
                    $phpunitConfiguration->printerClass(),
                    $file
                );
            }

            if ($phpunitConfiguration->hasTestSuiteLoaderClass()) {
                $file = $phpunitConfiguration->hasTestSuiteLoaderFile() ? $phpunitConfiguration->testSuiteLoaderFile() : '';

                $this->arguments['loader'] = $this->handleLoader(
                    $phpunitConfiguration->testSuiteLoaderClass(),
                    $file
                );
            }

            if (!isset($this->arguments['testsuite']) && $phpunitConfiguration->hasDefaultTestSuite()) {
                $this->arguments['testsuite'] = $phpunitConfiguration->defaultTestSuite();
            }

            if (!isset($this->arguments['test'])) {
                $this->arguments['test'] = (new TestSuiteMapper)->map(
                    $configuration->testSuite(),
                    $this->arguments['testsuite'] ?? ''
                );
            }
        } elseif (isset($this->arguments['bootstrap'])) {
            $this->handleBootstrap($this->arguments['bootstrap']);
        }

        if (isset($this->arguments['printer']) && \is_string($this->arguments['printer'])) {
            $this->arguments['printer'] = $this->handlePrinter($this->arguments['printer']);
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

        if (!\class_exists($loaderClass, false)) {
            if ($loaderFile == '') {
                $loaderFile = Filesystem::classNameToFilename(
                    $loaderClass
                );
            }

            $loaderFile = \stream_resolve_include_path($loaderFile);

            if ($loaderFile) {
                require $loaderFile;
            }
        }

        if (\class_exists($loaderClass, false)) {
            try {
                $class = new \ReflectionClass($loaderClass);
                // @codeCoverageIgnoreStart
            } catch (\ReflectionException $e) {
                throw new Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
            // @codeCoverageIgnoreEnd

            if ($class->implementsInterface(TestSuiteLoader::class) && $class->isInstantiable()) {
                $object = $class->newInstance();

                \assert($object instanceof TestSuiteLoader);

                return $object;
            }
        }

        if ($loaderClass == StandardTestSuiteLoader::class) {
            return null;
        }

        $this->exitWithErrorMessage(
            \sprintf(
                'Could not use "%s" as loader.',
                $loaderClass
            )
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
        if (!\class_exists($printerClass, false)) {
            if ($printerFile === '') {
                $printerFile = Filesystem::classNameToFilename(
                    $printerClass
                );
            }

            $printerFile = \stream_resolve_include_path($printerFile);

            if ($printerFile) {
                require $printerFile;
            }
        }

        if (!\class_exists($printerClass)) {
            $this->exitWithErrorMessage(
                \sprintf(
                    'Could not use "%s" as printer: class does not exist',
                    $printerClass
                )
            );
        }

        try {
            $class = new \ReflectionClass($printerClass);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
            // @codeCoverageIgnoreEnd
        }

        if (!$class->implementsInterface(ResultPrinter::class)) {
            $this->exitWithErrorMessage(
                \sprintf(
                    'Could not use "%s" as printer: class does not implement %s',
                    $printerClass,
                    ResultPrinter::class
                )
            );
        }

        if (!$class->isInstantiable()) {
            $this->exitWithErrorMessage(
                \sprintf(
                    'Could not use "%s" as printer: class cannot be instantiated',
                    $printerClass
                )
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
        } catch (Exception $e) {
            $this->exitWithErrorMessage($e->getMessage());
        }
    }

    protected function handleVersionCheck(): void
    {
        $this->printVersionString();

        $latestVersion = \file_get_contents('https://phar.phpunit.de/latest-version-of/phpunit');
        $isOutdated    = \version_compare($latestVersion, Version::id(), '>');

        if ($isOutdated) {
            \printf(
                'You are not using the latest version of PHPUnit.' . \PHP_EOL .
                'The latest version is PHPUnit %s.' . \PHP_EOL,
                $latestVersion
            );
        } else {
            print 'You are using the latest version of PHPUnit.' . \PHP_EOL;
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

        print Version::getVersionString() . \PHP_EOL . \PHP_EOL;

        $this->versionStringPrinted = true;
    }

    private function exitWithErrorMessage(string $message): void
    {
        $this->printVersionString();

        print $message . \PHP_EOL;

        exit(TestRunner::FAILURE_EXIT);
    }

    private function handleExtensions(string $directory): void
    {
        foreach ((new FileIteratorFacade)->getFilesAsArray($directory, '.phar') as $file) {
            if (!\file_exists('phar://' . $file . '/manifest.xml')) {
                $this->arguments['notLoadedExtensions'][] = $file . ' is not an extension for PHPUnit';

                continue;
            }

            try {
                $applicationName = new ApplicationName('phpunit/phpunit');
                $version         = new PharIoVersion(Version::series());
                $manifest        = ManifestLoader::fromFile('phar://' . $file . '/manifest.xml');

                if (!$manifest->isExtensionFor($applicationName)) {
                    $this->arguments['notLoadedExtensions'][] = $file . ' is not an extension for PHPUnit';

                    continue;
                }

                if (!$manifest->isExtensionFor($applicationName, $version)) {
                    $this->arguments['notLoadedExtensions'][] = $file . ' is not compatible with this version of PHPUnit';

                    continue;
                }
            } catch (ManifestException $e) {
                $this->arguments['notLoadedExtensions'][] = $file . ': ' . $e->getMessage();

                continue;
            }

            require $file;

            $this->arguments['loadedExtensions'][] = $manifest->getName() . ' ' . $manifest->getVersion()->getVersionString();
        }
    }

    private function handleListGroups(TestSuite $suite, bool $exit): int
    {
        $this->printVersionString();

        print 'Available test group(s):' . \PHP_EOL;

        $groups = $suite->getGroups();
        \sort($groups);

        foreach ($groups as $group) {
            \printf(
                ' - %s' . \PHP_EOL,
                $group
            );
        }

        if ($exit) {
            exit(TestRunner::SUCCESS_EXIT);
        }

        return TestRunner::SUCCESS_EXIT;
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     */
    private function handleListSuites(bool $exit): int
    {
        $this->printVersionString();

        print 'Available test suite(s):' . \PHP_EOL;

        $configuration = Registry::getInstance()->get($this->arguments['configuration']);

        foreach ($configuration->testSuite() as $testSuite) {
            \printf(
                ' - %s' . \PHP_EOL,
                $testSuite->name()
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

        $renderer = new XmlTestListRenderer;

        \file_put_contents($target, $renderer->render($suite));

        \printf(
            'Wrote list of tests that would have been run to %s' . \PHP_EOL,
            $target
        );

        if ($exit) {
            exit(TestRunner::SUCCESS_EXIT);
        }

        return TestRunner::SUCCESS_EXIT;
    }
}
