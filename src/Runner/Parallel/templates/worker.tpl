<?php declare(strict_types=1);
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestRunner\ErrorHandlerBootstrapper;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\Extension\WorkerExtensionBootstrapper;
use PHPUnit\Runner\Extension\WorkerExtensionFacade;
use PHPUnit\Runner\Parallel\CommandStream;
use PHPUnit\Runner\Parallel\WorkerDataProvider;
use PHPUnit\Runner\Parallel\WorkerException;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\PhpHandler;
use PHPUnit\TextUI\Configuration\SourceMapper;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\Util\DifferBuilder;

{childProcessHead}

{childProcessConfiguration}

$__phpunit_configuration = ConfigurationRegistry::get();

if ({collectCodeCoverageInformation}) {
    CodeCoverage::instance()->init($__phpunit_configuration, CodeCoverageFilterRegistry::instance(), true);
}

ErrorHandlerBootstrapper::bootstrap($__phpunit_configuration);

// A worker loads only the test class file of the unit it runs, whereas the
// main process has loaded every test class file. A class that is declared in
// another test class file and cannot be autoloaded, such as a test class that
// a #[DataProviderExternal] attribute names, is therefore loaded on demand from
// the file the main process loaded it from. This autoloader is registered
// last, so that it only ever sees classes that no other autoloader knows.
spl_autoload_register(
    static function (string $className): void
    {
        static $files = {testClassFiles};

        $className = strtolower($className);

        if (isset($files[$className])) {
            require_once $files[$className];
        }
    },
);

// The configured extensions that implement ChildProcessExtension are
// bootstrapped once, here, for the lifetime of the worker. Their subscribers
// are collected and registered with the dispatcher of every unit this worker
// runs (see __phpunit_worker_run_unit()); the warnings that a failed
// bootstrap produces are emitted with the first unit, as there is no unit to
// emit them into yet.
$__phpunit_extensionFacade       = new WorkerExtensionFacade;
$__phpunit_extensionWarnings     = [];
$__phpunit_extensionBootstrapper = null;

if (!$__phpunit_configuration->noExtensions()) {
    if ($__phpunit_configuration->hasPharExtensionDirectory()) {
        (new PharLoader(Facade::emitter()))->loadPharExtensionsInDirectory(
            $__phpunit_configuration->pharExtensionDirectory(),
        );
    }

    $__phpunit_extensionBootstrapper = new WorkerExtensionBootstrapper($__phpunit_configuration, $__phpunit_extensionFacade);

    foreach ($__phpunit_configuration->extensionBootstrappers() as $__phpunit_bootstrapper) {
        $__phpunit_extensionBootstrapper->bootstrap(
            $__phpunit_bootstrapper['className'],
            $__phpunit_bootstrapper['parameters'],
        );
    }

    $__phpunit_extensionWarnings = $__phpunit_extensionBootstrapper->warnings();
}

// A unit of work is run as a TestSuite, whose run loop consults the test
// result facade to decide whether to stop. The facade lazily registers its
// collector as an event subscriber on first use, which is not possible once
// the event facade has been sealed for isolation. The collector is therefore
// created here, while the facade is still open; it never receives events in
// the worker (the parent process owns the authoritative test result), so the
// worker never decides to stop on its own.
TestResultFacade::init();

ob_end_clean();

function __phpunit_worker_run_unit(array $command, array $extensionSubscribers, array $extensionWarnings): string
{
    $dispatcher = Facade::instance()->initForIsolation(
        PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
            $command['offsetSeconds'],
            $command['offsetNanoseconds']
        ),
    );

    // The subscribers of the extensions bootstrapped in this worker receive
    // the events of this unit's tests live, inside this process. They are
    // registered ahead of the streaming subscriber below, so that whatever
    // they do when a test finishes is done before the test's events are
    // streamed to the parent.
    foreach ($extensionSubscribers as $__phpunit_subscriber) {
        try {
            $dispatcher->registerSubscriber($__phpunit_subscriber);
        } catch (UnknownSubscriberTypeException) {
            // A subscriber that implements no known subscriber interface
            // cannot receive events here any more than it can in the main
            // process, where registering it made the extension's bootstrap
            // fail and reported that failure.
        }
    }

    // Stream the events of the unit to the parent process while the unit is
    // still running: whenever a test finishes, the events collected so far are
    // drained from the dispatcher and appended to the stream file as one
    // frame. The parent forwards a frame as soon as suite order allows, so
    // progress is reported per finished test instead of per finished unit.
    // Draining the dispatcher here also means that the end-of-unit result
    // envelope carries only the events emitted after the last test finished.
    //
    // The events of a retried test's attempt are diverted into a collection
    // window and not dispatched to subscribers, so a frame can never carry
    // events that a RetryTestSuite may yet decide to suppress.
    $dispatcher->registerSubscriber(
        new class($dispatcher, $command['streamFile'], $command['nonce']) implements PHPUnit\Event\Test\FinishedSubscriber
        {
            public function __construct(
                private readonly PHPUnit\Event\CollectingDispatcher $dispatcher,
                private readonly string $streamFile,
                private readonly string $nonce,
            ) {
            }

            public function notify(PHPUnit\Event\Test\Finished $event): void
            {
                $handle = @fopen($this->streamFile, 'ab');

                if ($handle === false) {
                    // The stream file cannot be appended to. The events stay
                    // in the dispatcher, and thereby ship with the end-of-unit
                    // result envelope: streaming degrades, no event is lost.
                    return;
                }

                fwrite($handle, PHPUnit\Runner\Parallel\EventStream::frame($this->nonce, $this->dispatcher->flush()));

                fclose($handle);
            }
        },
    );

    require_once $command['file'];

    $suite        = TestSuite::empty($command['className'], Facade::emitter());
    $dataProvider = new WorkerDataProvider(Facade::emitter());
    $failure      = null;

    // Each member of the unit arrived as the descriptor that
    // TestDescriptor::from() produced in the parent process; the descriptor
    // turns back into the member it describes here. The data of a
    // data-provided test case is not part of its description: it is
    // provided here, by invoking the data provider again (see
    // WorkerDataProvider). A unit that cannot be rebuilt — its data provider
    // failed in this process, or did not provide a data set the parent
    // process selected — runs no test at all; its envelope carries the
    // failure instead, and the parent reports every test of the unit as
    // errored with it.
    try {
        foreach ($command['tests'] as $__phpunit_test) {
            $suite->addTest($__phpunit_test->test($command['className'], $dataProvider));
        }
    } catch (WorkerException $e) {
        $failure = $e->getMessage();
    }

    // Invoking the data providers emitted the events that a data provider
    // invocation emits. The parent process emitted the same events when it
    // built the suite, and it is the parent's that are reported; the ones
    // emitted here are discarded so that they are not reported a second time.
    $dispatcher->flush();

    foreach ($extensionWarnings as $__phpunit_warning) {
        Facade::emitter()->testRunnerTriggeredPhpunitWarning($__phpunit_warning);
    }

    if ($failure === null) {
        $suite->run();
    }

    $codeCoverage = null;

    if (CodeCoverage::instance()->isActive()) {
        $codeCoverage = CodeCoverage::instance()->codeCoverage();
    }

    $envelope = (object) [
        'codeCoverage' => $codeCoverage,
        'events'       => $dispatcher->flush(),
        'passedTests'  => PassedTests::instance(),
    ];

    if ($failure !== null) {
        $envelope->failure = $failure;
    }

    $result = $command['nonce'] . serialize($envelope);

    // Per-unit code coverage has been collected for this command and is about
    // to be shipped to the parent process. It is cleared here so that the next
    // unit executed by this worker does not ship it a second time.
    if (CodeCoverage::instance()->isActive()) {
        CodeCoverage::instance()->codeCoverage()->clear();
    }

    // The passes recorded while running this unit ship with its envelope and
    // are forgotten here, so that the envelope of the next unit executed by
    // this worker carries only that unit's own passes. The parent imports a
    // unit's passes at the moment its turn in suite order comes; a unit that
    // ran earlier on this worker but comes later in suite order must not have
    // its passes imported ahead of that turn, because a test that depends on
    // one of them would then run where a sequential run would have skipped it.
    PassedTests::instance()->reset();

    // Reset the stream that captures test output so that the next unit does
    // not inherit the output of the unit that has just finished.
    if (@rewind(STDOUT)) {
        @ftruncate(STDOUT, 0);
    }

    return $result;
}

$__phpunit_input = fopen('php://stdin', 'rb');

while (($__phpunit_line = fgets($__phpunit_input)) !== false) {
    $__phpunit_line = trim($__phpunit_line);

    if ($__phpunit_line === '') {
        continue;
    }

    $__phpunit_command = CommandStream::decode($__phpunit_line);

    // A line that does not decode to a command was not written by the parent
    // process. Nothing that arrives on this channel can be trusted after that,
    // so the worker stops; the parent detects the dead process and reports the
    // unit it was running as crashed.
    if ($__phpunit_command === null) {
        break;
    }

    if ($__phpunit_command['command'] === 'stop') {
        // The extensions bootstrapped in this worker are shut down once the
        // worker has run its last unit. The warnings that a failed shutdown
        // produces are reported to the parent through the command's result
        // file, as there is no unit left to emit them into.
        $__phpunit_shutdownWarnings = [];

        if ($__phpunit_extensionBootstrapper !== null) {
            $__phpunit_shutdownWarnings = $__phpunit_extensionBootstrapper->shutdown();
        }

        file_put_contents(
            $__phpunit_command['resultFile'],
            $__phpunit_command['nonce'] . serialize($__phpunit_shutdownWarnings),
        );

        break;
    }

    $__phpunit_result = __phpunit_worker_run_unit(
        $__phpunit_command,
        $__phpunit_extensionFacade->subscribers(),
        $__phpunit_extensionWarnings,
    );

    // The warnings of the worker's extension bootstrap travel with the first
    // unit only.
    $__phpunit_extensionWarnings = [];

    file_put_contents($__phpunit_command['resultFile'], $__phpunit_result);

    // Signal completion through the filesystem rather than standard output: the
    // parent polls for this file, and because it is created only after the
    // result file has been fully written, its presence means the result is
    // ready to be read. This avoids the parent having to read the worker's
    // output pipe, which cannot be done without blocking on Windows.
    file_put_contents($__phpunit_command['doneFile'], $__phpunit_command['nonce']);
}
