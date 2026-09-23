<?php declare(strict_types=1);
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestRunner\ChildProcessOutputCollector;
use PHPUnit\Framework\TestRunner\ErrorHandlerBootstrapper;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Extension\ChildProcessExtensionBootstrapper;
use PHPUnit\Runner\Extension\ChildProcessExtensionFacade;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\PhpHandler;
use PHPUnit\TextUI\Configuration\SourceMapper;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\Util\DifferBuilder;

// php://stdout does not obey output buffering. Any output would break
// unserialization of child process results in the parent process.
if (!defined('STDOUT')) {
    define('STDOUT', fopen('php://temp', 'w+b'));
    define('STDERR', fopen('php://stderr', 'wb'));
}

{iniSettings}
ini_set('display_errors', 'stderr');
if (get_include_path() !== '{include_path}') {
    set_include_path('{include_path}');
}

$__phpunit_composerAutoload = {composerAutoload};
$__phpunit_phar             = {phar};

ob_start();

if ($__phpunit_composerAutoload) {
    require_once $__phpunit_composerAutoload;

    define('PHPUNIT_COMPOSER_INSTALL', $__phpunit_composerAutoload);
} else if ($__phpunit_phar) {
    require $__phpunit_phar;
}

function __phpunit_run_isolated_test()
{
    $dispatcher = Facade::instance()->initForIsolation(
        PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
            {offsetSeconds},
            {offsetNanoseconds}
        ),
    );

    require_once '{filename}';

    $configuration = ConfigurationRegistry::get();

    if ({collectCodeCoverageInformation}) {
        CodeCoverage::instance()->init($configuration, CodeCoverageFilterRegistry::instance(), true);
    }

    ErrorHandlerBootstrapper::bootstrap($configuration);

    // The extensions are bootstrapped after the event facade has been
    // initialized for isolation, so that the subscribers they register
    // receive the events of the test, and so that a failure to bootstrap one
    // is reported to the main process along with these events.
    $extensionBootstrapper = new ChildProcessExtensionBootstrapper(
        $configuration,
        new ChildProcessExtensionFacade($dispatcher),
        Facade::emitter(),
    );

    if (!$configuration->noExtensions()) {
        foreach ($configuration->extensionBootstrappers() as $bootstrapper) {
            $extensionBootstrapper->bootstrap(
                $bootstrapper['className'],
                $bootstrapper['parameters'],
            );
        }
    }

    $test = new {className}('{methodName}');

    $test->setData({dataName}, unserialize('{data}'));
    $test->setDependencyInput(unserialize('{dependencyInput}'));
    $test->setRepetition({repetition}, {totalRepetitions});
    $test->setAttempt({attempt}, {maxAttempts});
    $test->setInIsolation(true);

    ob_end_clean();

    $test->run();

    // What an extension prints while it is shut down is not shown.
    ob_start();
    $extensionBootstrapper->shutdown();
    ob_end_clean();

    $output = ChildProcessOutputCollector::collect($test);

    file_put_contents(
        '{processResultFile}',
        '{processResultNonce}' . serialize(
            (object)[
                'testResult'    => $test->result(),
                'status'        => $test->status(),
                'codeCoverage'  => {collectCodeCoverageInformation} ? CodeCoverage::instance()->codeCoverage() : null,
                'numAssertions' => $test->numberOfAssertionsPerformed(),
                'output'        => $output,
                'events'        => $dispatcher->flush(),
                'passedTests'   => PassedTests::instance()
            ]
        )
    );
}

function __phpunit_error_handler($errno, $errstr, $errfile, $errline)
{
   return true;
}

set_error_handler('__phpunit_error_handler');

{constants}
{included_files}
{globals}

restore_error_handler();

ConfigurationRegistry::loadFrom('{serializedConfiguration}');

DifferBuilder::configureComparatorFactory();

if ('{sourceMapFile}' !== '') {
    SourceMapper::loadFrom('{sourceMapFile}', ConfigurationRegistry::get()->source());
}

(new PhpHandler(Facade::emitter()))->handle(ConfigurationRegistry::get()->php());

if ('{bootstrap}' !== '') {
    require_once '{bootstrap}';
}

$__phpunit_includeTestSuites = ConfigurationRegistry::get()->includeTestSuites();
$__phpunit_excludeTestSuites = ConfigurationRegistry::get()->excludeTestSuites();

foreach (ConfigurationRegistry::get()->bootstrapForTestSuite() as $__phpunit_testSuiteName => $__phpunit_bootstrapForTestSuite) {
    if ($__phpunit_includeTestSuites !== [] && !in_array($__phpunit_testSuiteName, $__phpunit_includeTestSuites, true)) {
        continue;
    }

    if ($__phpunit_excludeTestSuites !== [] && in_array($__phpunit_testSuiteName, $__phpunit_excludeTestSuites, true)) {
        continue;
    }

    require_once $__phpunit_bootstrapForTestSuite;
}

// The extensions in PHARs are loaded before the event facade is initialized
// for isolation, so that the events that are emitted while they are loaded,
// which the main process has emitted already, are not reported again.
if (!ConfigurationRegistry::get()->noExtensions() && ConfigurationRegistry::get()->hasPharExtensionDirectory()) {
    (new PharLoader(Facade::emitter()))->loadPharExtensionsInDirectory(
        ConfigurationRegistry::get()->pharExtensionDirectory(),
    );
}

__phpunit_run_isolated_test();
