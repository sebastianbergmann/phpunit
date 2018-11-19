<?php
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
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Runner\StandardTestSuiteLoader;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Configuration;
use PHPUnit\Util\ConfigurationGenerator;
use PHPUnit\Util\FileLoader;
use PHPUnit\Util\Filesystem;
use PHPUnit\Util\Getopt;
use PHPUnit\Util\Log\TeamCity;
use PHPUnit\Util\Printer;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use PHPUnit\Util\TextTestListRenderer;
use PHPUnit\Util\XmlTestListRenderer;
use ReflectionClass;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

use Throwable;

/**
 * A TestRunner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 */
class Command
{
    /**
     * @var array
     */
    protected $arguments = [
        'listGroups'              => false,
        'listSuites'              => false,
        'listTests'               => false,
        'listTestsXml'            => false,
        'loader'                  => null,
        'useDefaultConfiguration' => true,
        'loadedExtensions'        => [],
        'notLoadedExtensions'     => [],
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $longOptions = [
        'atleast-version='          => null,
        'prepend='                  => null,
        'bootstrap='                => null,
        'cache-result'              => null,
        'cache-result-file='        => null,
        'check-version'             => null,
        'colors=='                  => null,
        'columns='                  => null,
        'configuration='            => null,
        'coverage-clover='          => null,
        'coverage-crap4j='          => null,
        'coverage-html='            => null,
        'coverage-php='             => null,
        'coverage-text=='           => null,
        'coverage-xml='             => null,
        'debug'                     => null,
        'disallow-test-output'      => null,
        'disallow-resource-usage'   => null,
        'disallow-todo-tests'       => null,
        'default-time-limit='       => null,
        'enforce-time-limit'        => null,
        'exclude-group='            => null,
        'filter='                   => null,
        'generate-configuration'    => null,
        'globals-backup'            => null,
        'group='                    => null,
        'help'                      => null,
        'resolve-dependencies'      => null,
        'ignore-dependencies'       => null,
        'include-path='             => null,
        'list-groups'               => null,
        'list-suites'               => null,
        'list-tests'                => null,
        'list-tests-xml='           => null,
        'loader='                   => null,
        'log-junit='                => null,
        'log-teamcity='             => null,
        'no-configuration'          => null,
        'no-coverage'               => null,
        'no-logging'                => null,
        'no-extensions'             => null,
        'order-by='                 => null,
        'printer='                  => null,
        'process-isolation'         => null,
        'repeat='                   => null,
        'dont-report-useless-tests' => null,
        'random-order'              => null,
        'random-order-seed='        => null,
        'reverse-order'             => null,
        'reverse-list'              => null,
        'static-backup'             => null,
        'stderr'                    => null,
        'stop-on-defect'            => null,
        'stop-on-error'             => null,
        'stop-on-failure'           => null,
        'stop-on-warning'           => null,
        'stop-on-incomplete'        => null,
        'stop-on-risky'             => null,
        'stop-on-skipped'           => null,
        'fail-on-warning'           => null,
        'fail-on-risky'             => null,
        'strict-coverage'           => null,
        'disable-coverage-ignore'   => null,
        'strict-global-state'       => null,
        'teamcity'                  => null,
        'testdox'                   => null,
        'testdox-group='            => null,
        'testdox-exclude-group='    => null,
        'testdox-html='             => null,
        'testdox-text='             => null,
        'testdox-xml='              => null,
        'test-suffix='              => null,
        'testsuite='                => null,
        'verbose'                   => null,
        'version'                   => null,
        'whitelist='                => null,
        'dump-xdebug-filter='       => null,
    ];

    /**
     * @var bool
     */
    private $versionStringPrinted = false;

    /**
     * @throws \RuntimeException
     * @throws \PHPUnit\Framework\Exception
     * @throws \InvalidArgumentException
     */
    public static function main(bool $exit = true): int
    {
        $command = new static;

        return $command->run($_SERVER['argv'], $exit);
    }

    /**
     * @throws \RuntimeException
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
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
                $this->arguments['testFile'],
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
            $result = $runner->doRun($suite, $this->arguments, $exit);
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
            $this->options = Getopt::getopt(
                $argv,
                'd:c:hv',
                \array_keys($this->longOptions)
            );
        } catch (Exception $t) {
            $this->exitWithErrorMessage($t->getMessage());
        }

        foreach ($this->options[0] as $option) {
            switch ($option[0]) {
                case '--colors':
                    $this->arguments['colors'] = $option[1] ?: ResultPrinter::COLOR_AUTO;

                    break;

                case '--bootstrap':
                    $this->arguments['bootstrap'] = $option[1];

                    break;

                case '--cache-result':
                    $this->arguments['cacheResult'] = true;

                    break;

                case '--cache-result-file':
                    $this->arguments['cacheResultFile'] = $option[1];

                    break;

                case '--columns':
                    if (\is_numeric($option[1])) {
                        $this->arguments['columns'] = (int) $option[1];
                    } elseif ($option[1] === 'max') {
                        $this->arguments['columns'] = 'max';
                    }

                    break;

                case 'c':
                case '--configuration':
                    $this->arguments['configuration'] = $option[1];

                    break;

                case '--coverage-clover':
                    $this->arguments['coverageClover'] = $option[1];

                    break;

                case '--coverage-crap4j':
                    $this->arguments['coverageCrap4J'] = $option[1];

                    break;

                case '--coverage-html':
                    $this->arguments['coverageHtml'] = $option[1];

                    break;

                case '--coverage-php':
                    $this->arguments['coveragePHP'] = $option[1];

                    break;

                case '--coverage-text':
                    if ($option[1] === null) {
                        $option[1] = 'php://stdout';
                    }

                    $this->arguments['coverageText']                   = $option[1];
                    $this->arguments['coverageTextShowUncoveredFiles'] = false;
                    $this->arguments['coverageTextShowOnlySummary']    = false;

                    break;

                case '--coverage-xml':
                    $this->arguments['coverageXml'] = $option[1];

                    break;

                case 'd':
                    $ini = \explode('=', $option[1]);

                    if (isset($ini[0])) {
                        if (isset($ini[1])) {
                            \ini_set($ini[0], $ini[1]);
                        } else {
                            \ini_set($ini[0], true);
                        }
                    }

                    break;

                case '--debug':
                    $this->arguments['debug'] = true;

                    break;

                case 'h':
                case '--help':
                    $this->showHelp();
                    exit(TestRunner::SUCCESS_EXIT);

                    break;

                case '--filter':
                    $this->arguments['filter'] = $option[1];

                    break;

                case '--testsuite':
                    $this->arguments['testsuite'] = $option[1];

                    break;

                case '--generate-configuration':
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

                    $generator = new ConfigurationGenerator;

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

                    break;

                case '--group':
                    $this->arguments['groups'] = \explode(',', $option[1]);

                    break;

                case '--exclude-group':
                    $this->arguments['excludeGroups'] = \explode(
                        ',',
                        $option[1]
                    );

                    break;

                case '--test-suffix':
                    $this->arguments['testSuffixes'] = \explode(
                        ',',
                        $option[1]
                    );

                    break;

                case '--include-path':
                    $includePath = $option[1];

                    break;

                case '--list-groups':
                    $this->arguments['listGroups'] = true;

                    break;

                case '--list-suites':
                    $this->arguments['listSuites'] = true;

                    break;

                case '--list-tests':
                    $this->arguments['listTests'] = true;

                    break;

                case '--list-tests-xml':
                    $this->arguments['listTestsXml'] = $option[1];

                    break;

                case '--printer':
                    $this->arguments['printer'] = $option[1];

                    break;

                case '--loader':
                    $this->arguments['loader'] = $option[1];

                    break;

                case '--log-junit':
                    $this->arguments['junitLogfile'] = $option[1];

                    break;

                case '--log-teamcity':
                    $this->arguments['teamcityLogfile'] = $option[1];

                    break;

                case '--order-by':
                    $this->handleOrderByOption($option[1]);

                    break;

                case '--process-isolation':
                    $this->arguments['processIsolation'] = true;

                    break;

                case '--repeat':
                    $this->arguments['repeat'] = (int) $option[1];

                    break;

                case '--stderr':
                    $this->arguments['stderr'] = true;

                    break;

                case '--stop-on-defect':
                    $this->arguments['stopOnDefect'] = true;

                    break;

                case '--stop-on-error':
                    $this->arguments['stopOnError'] = true;

                    break;

                case '--stop-on-failure':
                    $this->arguments['stopOnFailure'] = true;

                    break;

                case '--stop-on-warning':
                    $this->arguments['stopOnWarning'] = true;

                    break;

                case '--stop-on-incomplete':
                    $this->arguments['stopOnIncomplete'] = true;

                    break;

                case '--stop-on-risky':
                    $this->arguments['stopOnRisky'] = true;

                    break;

                case '--stop-on-skipped':
                    $this->arguments['stopOnSkipped'] = true;

                    break;

                case '--fail-on-warning':
                    $this->arguments['failOnWarning'] = true;

                    break;

                case '--fail-on-risky':
                    $this->arguments['failOnRisky'] = true;

                    break;

                case '--teamcity':
                    $this->arguments['printer'] = TeamCity::class;

                    break;

                case '--testdox':
                    $this->arguments['printer'] = CliTestDoxPrinter::class;

                    break;

                case '--testdox-group':
                    $this->arguments['testdoxGroups'] = \explode(
                        ',',
                        $option[1]
                    );

                    break;

                case '--testdox-exclude-group':
                    $this->arguments['testdoxExcludeGroups'] = \explode(
                        ',',
                        $option[1]
                    );

                    break;

                case '--testdox-html':
                    $this->arguments['testdoxHTMLFile'] = $option[1];

                    break;

                case '--testdox-text':
                    $this->arguments['testdoxTextFile'] = $option[1];

                    break;

                case '--testdox-xml':
                    $this->arguments['testdoxXMLFile'] = $option[1];

                    break;

                case '--no-configuration':
                    $this->arguments['useDefaultConfiguration'] = false;

                    break;

                case '--no-extensions':
                    $this->arguments['noExtensions'] = true;

                    break;

                case '--no-coverage':
                    $this->arguments['noCoverage'] = true;

                    break;

                case '--no-logging':
                    $this->arguments['noLogging'] = true;

                    break;

                case '--globals-backup':
                    $this->arguments['backupGlobals'] = true;

                    break;

                case '--static-backup':
                    $this->arguments['backupStaticAttributes'] = true;

                    break;

                case 'v':
                case '--verbose':
                    $this->arguments['verbose'] = true;

                    break;

                case '--atleast-version':
                    if (\version_compare(Version::id(), $option[1], '>=')) {
                        exit(TestRunner::SUCCESS_EXIT);
                    }

                    exit(TestRunner::FAILURE_EXIT);

                    break;

                case '--version':
                    $this->printVersionString();
                    exit(TestRunner::SUCCESS_EXIT);

                    break;

                case '--dont-report-useless-tests':
                    $this->arguments['reportUselessTests'] = false;

                    break;

                case '--strict-coverage':
                    $this->arguments['strictCoverage'] = true;

                    break;

                case '--disable-coverage-ignore':
                    $this->arguments['disableCodeCoverageIgnore'] = true;

                    break;

                case '--strict-global-state':
                    $this->arguments['beStrictAboutChangesToGlobalState'] = true;

                    break;

                case '--disallow-test-output':
                    $this->arguments['disallowTestOutput'] = true;

                    break;

                case '--disallow-resource-usage':
                    $this->arguments['beStrictAboutResourceUsageDuringSmallTests'] = true;

                    break;

                case '--default-time-limit':
                    $this->arguments['defaultTimeLimit'] = (int) $option[1];

                    break;

                case '--enforce-time-limit':
                    $this->arguments['enforceTimeLimit'] = true;

                    break;

                case '--disallow-todo-tests':
                    $this->arguments['disallowTodoAnnotatedTests'] = true;

                    break;

                case '--reverse-list':
                    $this->arguments['reverseList'] = true;

                    break;

                case '--check-version':
                    $this->handleVersionCheck();

                    break;

                case '--whitelist':
                    $this->arguments['whitelist'] = $option[1];

                    break;

                case '--random-order':
                    $this->handleOrderByOption('random');

                    break;

                case '--random-order-seed':
                    $this->arguments['randomOrderSeed'] = (int) $option[1];

                    break;

                case '--resolve-dependencies':
                    $this->handleOrderByOption('depends');

                    break;

                case '--ignore-dependencies':
                    $this->arguments['resolveDependencies'] = false;

                    break;

                case '--reverse-order':
                    $this->handleOrderByOption('reverse');

                    break;

                case '--dump-xdebug-filter':
                    $this->arguments['xdebugFilterFile'] = $option[1];

                    break;

                default:
                    $optionName = \str_replace('--', '', $option[0]);

                    $handler = null;

                    if (isset($this->longOptions[$optionName])) {
                        $handler = $this->longOptions[$optionName];
                    } elseif (isset($this->longOptions[$optionName . '='])) {
                        $handler = $this->longOptions[$optionName . '='];
                    }

                    if (isset($handler) && \is_callable([$this, $handler])) {
                        $this->$handler($option[1]);
                    }
            }
        }

        $this->handleCustomTestSuite();

        if (!isset($this->arguments['test'])) {
            if (isset($this->options[1][0])) {
                $this->arguments['test'] = $this->options[1][0];
            }

            if (isset($this->options[1][1])) {
                $this->arguments['testFile'] = \realpath($this->options[1][1]);
            } else {
                $this->arguments['testFile'] = '';
            }

            if (isset($this->arguments['test']) &&
                \is_file($this->arguments['test']) &&
                \substr($this->arguments['test'], -5, 5) != '.phpt') {
                $this->arguments['testFile'] = \realpath($this->arguments['test']);
                $this->arguments['test']     = \substr($this->arguments['test'], 0, \strrpos($this->arguments['test'], '.'));
            }
        }

        if (!isset($this->arguments['testSuffixes'])) {
            $this->arguments['testSuffixes'] = ['Test.php', '.phpt'];
        }

        if (isset($includePath)) {
            \ini_set(
                'include_path',
                $includePath . \PATH_SEPARATOR . \ini_get('include_path')
            );
        }

        if ($this->arguments['loader'] !== null) {
            $this->arguments['loader'] = $this->handleLoader($this->arguments['loader']);
        }

        if (isset($this->arguments['configuration']) &&
            \is_dir($this->arguments['configuration'])) {
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
        } elseif (!isset($this->arguments['configuration']) &&
            $this->arguments['useDefaultConfiguration']) {
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
                $configuration = Configuration::getInstance(
                    $this->arguments['configuration']
                );
            } catch (Throwable $t) {
                print $t->getMessage() . \PHP_EOL;
                exit(TestRunner::FAILURE_EXIT);
            }

            $phpunitConfiguration = $configuration->getPHPUnitConfiguration();

            $configuration->handlePHPConfiguration();

            /*
             * Issue #1216
             */
            if (isset($this->arguments['bootstrap'])) {
                $this->handleBootstrap($this->arguments['bootstrap']);
            } elseif (isset($phpunitConfiguration['bootstrap'])) {
                $this->handleBootstrap($phpunitConfiguration['bootstrap']);
            }

            /*
             * Issue #657
             */
            if (isset($phpunitConfiguration['stderr']) && !isset($this->arguments['stderr'])) {
                $this->arguments['stderr'] = $phpunitConfiguration['stderr'];
            }

            if (isset($phpunitConfiguration['extensionsDirectory']) && !isset($this->arguments['noExtensions']) && \extension_loaded('phar')) {
                $this->handleExtensions($phpunitConfiguration['extensionsDirectory']);
            }

            if (isset($phpunitConfiguration['columns']) && !isset($this->arguments['columns'])) {
                $this->arguments['columns'] = $phpunitConfiguration['columns'];
            }

            if (!isset($this->arguments['printer']) && isset($phpunitConfiguration['printerClass'])) {
                if (isset($phpunitConfiguration['printerFile'])) {
                    $file = $phpunitConfiguration['printerFile'];
                } else {
                    $file = '';
                }

                $this->arguments['printer'] = $this->handlePrinter(
                    $phpunitConfiguration['printerClass'],
                    $file
                );
            }

            if (isset($phpunitConfiguration['testSuiteLoaderClass'])) {
                if (isset($phpunitConfiguration['testSuiteLoaderFile'])) {
                    $file = $phpunitConfiguration['testSuiteLoaderFile'];
                } else {
                    $file = '';
                }

                $this->arguments['loader'] = $this->handleLoader(
                    $phpunitConfiguration['testSuiteLoaderClass'],
                    $file
                );
            }

            if (!isset($this->arguments['testsuite']) && isset($phpunitConfiguration['defaultTestSuite'])) {
                $this->arguments['testsuite'] = $phpunitConfiguration['defaultTestSuite'];
            }

            if (!isset($this->arguments['test'])) {
                $testSuite = $configuration->getTestSuiteConfiguration($this->arguments['testsuite'] ?? '');

                if ($testSuite !== null) {
                    $this->arguments['test'] = $testSuite;
                }
            }
        } elseif (isset($this->arguments['bootstrap'])) {
            $this->handleBootstrap($this->arguments['bootstrap']);
        }

        if (isset($this->arguments['printer']) &&
            \is_string($this->arguments['printer'])) {
            $this->arguments['printer'] = $this->handlePrinter($this->arguments['printer']);
        }

        if (isset($this->arguments['test']) && \is_string($this->arguments['test']) && \substr($this->arguments['test'], -5, 5) == '.phpt') {
            $test = new PhptTestCase($this->arguments['test']);

            $this->arguments['test'] = new TestSuite;
            $this->arguments['test']->addTest($test);
        }

        if (!isset($this->arguments['test'])) {
            $this->showHelp();
            exit(TestRunner::EXCEPTION_EXIT);
        }
    }

    /**
     * Handles the loading of the PHPUnit\Runner\TestSuiteLoader implementation.
     */
    protected function handleLoader(string $loaderClass, string $loaderFile = ''): ?TestSuiteLoader
    {
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
            $class = new ReflectionClass($loaderClass);

            if ($class->implementsInterface(TestSuiteLoader::class) &&
                $class->isInstantiable()) {
                return $class->newInstance();
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
            if ($printerFile == '') {
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

        $class = new ReflectionClass($printerClass);

        if (!$class->implementsInterface(TestListener::class)) {
            $this->exitWithErrorMessage(
                \sprintf(
                    'Could not use "%s" as printer: class does not implement %s',
                    $printerClass,
                    TestListener::class
                )
            );
        }

        if (!$class->isSubclassOf(Printer::class)) {
            $this->exitWithErrorMessage(
                \sprintf(
                    'Could not use "%s" as printer: class does not extend %s',
                    $printerClass,
                    Printer::class
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

        print <<<EOT
Usage: phpunit [options] UnitTest [UnitTest.php]
       phpunit [options] <directory>

Code Coverage Options:

  --coverage-clover <file>    Generate code coverage report in Clover XML format
  --coverage-crap4j <file>    Generate code coverage report in Crap4J XML format
  --coverage-html <dir>       Generate code coverage report in HTML format
  --coverage-php <file>       Export PHP_CodeCoverage object to file
  --coverage-text=<file>      Generate code coverage report in text format
                              Default: Standard output
  --coverage-xml <dir>        Generate code coverage report in PHPUnit XML format
  --whitelist <dir>           Whitelist <dir> for code coverage analysis
  --disable-coverage-ignore   Disable annotations for ignoring code coverage
  --no-coverage               Ignore code coverage configuration
  --dump-xdebug-filter <file> Generate script to set Xdebug code coverage filter

Logging Options:

  --log-junit <file>          Log test execution in JUnit XML format to file
  --log-teamcity <file>       Log test execution in TeamCity format to file
  --testdox-html <file>       Write agile documentation in HTML format to file
  --testdox-text <file>       Write agile documentation in Text format to file
  --testdox-xml <file>        Write agile documentation in XML format to file
  --reverse-list              Print defects in reverse order

Test Selection Options:

  --filter <pattern>          Filter which tests to run
  --testsuite <name,...>      Filter which testsuite to run
  --group ...                 Only runs tests from the specified group(s)
  --exclude-group ...         Exclude tests from the specified group(s)
  --list-groups               List available test groups
  --list-suites               List available test suites
  --list-tests                List available tests
  --list-tests-xml <file>     List available tests in XML format
  --test-suffix ...           Only search for test in files with specified
                              suffix(es). Default: Test.php,.phpt

Test Execution Options:

  --dont-report-useless-tests Do not report tests that do not test anything
  --strict-coverage           Be strict about @covers annotation usage
  --strict-global-state       Be strict about changes to global state
  --disallow-test-output      Be strict about output during tests
  --disallow-resource-usage   Be strict about resource usage during small tests
  --enforce-time-limit        Enforce time limit based on test size
  --default-time-limit=<sec>  Timeout in seconds for tests without @small, @medium or @large
  --disallow-todo-tests       Disallow @todo-annotated tests

  --process-isolation         Run each test in a separate PHP process
  --globals-backup            Backup and restore \$GLOBALS for each test
  --static-backup             Backup and restore static attributes for each test

  --colors=<flag>             Use colors in output ("never", "auto" or "always")
  --columns <n>               Number of columns to use for progress output
  --columns max               Use maximum number of columns for progress output
  --stderr                    Write to STDERR instead of STDOUT
  --stop-on-defect            Stop execution upon first not-passed test
  --stop-on-error             Stop execution upon first error
  --stop-on-failure           Stop execution upon first error or failure
  --stop-on-warning           Stop execution upon first warning
  --stop-on-risky             Stop execution upon first risky test
  --stop-on-skipped           Stop execution upon first skipped test
  --stop-on-incomplete        Stop execution upon first incomplete test
  --fail-on-warning           Treat tests with warnings as failures
  --fail-on-risky             Treat risky tests as failures
  -v|--verbose                Output more verbose information
  --debug                     Display debugging information

  --loader <loader>           TestSuiteLoader implementation to use
  --repeat <times>            Runs the test(s) repeatedly
  --teamcity                  Report test execution progress in TeamCity format
  --testdox                   Report test execution progress in TestDox format
  --testdox-group             Only include tests from the specified group(s)
  --testdox-exclude-group     Exclude tests from the specified group(s)
  --printer <printer>         TestListener implementation to use

  --resolve-dependencies      Resolve dependencies between tests
  --order-by=<order>          Run tests in order: default|reverse|random|defects|depends
  --random-order-seed=<N>     Use a specific random seed <N> for random order
  --cache-result              Write run result to cache to enable ordering tests defects-first

Configuration Options:

  --prepend <file>            A PHP script that is included as early as possible
  --bootstrap <file>          A PHP script that is included before the tests run
  -c|--configuration <file>   Read configuration from XML file
  --no-configuration          Ignore default configuration file (phpunit.xml)
  --no-logging                Ignore logging configuration
  --no-extensions             Do not load PHPUnit extensions
  --include-path <path(s)>    Prepend PHP's include_path with given path(s)
  -d key[=value]              Sets a php.ini value
  --generate-configuration    Generate configuration file with suggested settings
  --cache-result-file=<file>  Specify result cache path and filename

Miscellaneous Options:

  -h|--help                   Prints this usage information
  --version                   Prints the version and exits
  --atleast-version <min>     Checks that version is greater than min and exits
  --check-version             Check whether PHPUnit is the latest version

EOT;
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
        $facade = new FileIteratorFacade;

        foreach ($facade->getFilesAsArray($directory, '.phar') as $file) {
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

    private function handleListSuites(bool $exit): int
    {
        $this->printVersionString();

        print 'Available test suite(s):' . \PHP_EOL;

        $configuration = Configuration::getInstance(
            $this->arguments['configuration']
        );

        $suiteNames = $configuration->getTestSuiteNames();

        foreach ($suiteNames as $suiteName) {
            \printf(
                ' - %s' . \PHP_EOL,
                $suiteName
            );
        }

        if ($exit) {
            exit(TestRunner::SUCCESS_EXIT);
        }

        return TestRunner::SUCCESS_EXIT;
    }

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

    private function handleOrderByOption(string $value): void
    {
        foreach (\explode(',', $value) as $order) {
            switch ($order) {
                case 'default':
                    $this->arguments['executionOrder']        = TestSuiteSorter::ORDER_DEFAULT;
                    $this->arguments['executionOrderDefects'] = TestSuiteSorter::ORDER_DEFAULT;
                    $this->arguments['resolveDependencies']   = false;

                    break;

                case 'reverse':
                    $this->arguments['executionOrder'] = TestSuiteSorter::ORDER_REVERSED;

                    break;

                case 'random':
                    $this->arguments['executionOrder'] = TestSuiteSorter::ORDER_RANDOMIZED;

                    break;

                case 'defects':
                    $this->arguments['executionOrderDefects'] = TestSuiteSorter::ORDER_DEFECTS_FIRST;

                    break;

                case 'depends':
                    $this->arguments['resolveDependencies'] = true;

                    break;

                default:
                    $this->exitWithErrorMessage("unrecognized --order-by option: $order");
            }
        }
    }
}
