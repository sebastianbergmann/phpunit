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

{childProcessHead}

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

{childProcessConfiguration}

// The extensions in PHARs are loaded before the event facade is initialized
// for isolation, so that the events that are emitted while they are loaded,
// which the main process has emitted already, are not reported again.
if (!ConfigurationRegistry::get()->noExtensions() && ConfigurationRegistry::get()->hasPharExtensionDirectory()) {
    (new PharLoader(Facade::emitter()))->loadPharExtensionsInDirectory(
        ConfigurationRegistry::get()->pharExtensionDirectory(),
    );
}

__phpunit_run_isolated_test();
