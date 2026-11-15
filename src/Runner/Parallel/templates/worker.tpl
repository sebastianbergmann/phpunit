<?php declare(strict_types=1);
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestRunner\ErrorHandlerBootstrapper;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Extension\WorkerExtensionBootstrapper;
use PHPUnit\Runner\Extension\WorkerExtensionFacade;
use PHPUnit\Runner\Parallel\CommandStream;
use PHPUnit\Runner\Parallel\PassedTestsRecorder;
use PHPUnit\Runner\Parallel\WorkerDataProvider;
use PHPUnit\Runner\Parallel\WorkerException;
use PHPUnit\Runner\Preloader;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\PhpHandler;
use PHPUnit\TextUI\Configuration\SourceMapper;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\Util\DifferBuilder;

// The head that the worker shares with the child process of a test that is
// run in a separate process has PHP display errors on the standard error. In
// a worker, what PHP displays for an error that the error handler of PHPUnit
// does not handle, in a method that is called before the first test of a
// test class, for instance, is printed by the main process where the worker
// wrote it (see __phpunit_worker_output_length()). The setting that the
// worker was started with, which is the one the main process was started
// with, is therefore put back, before the configuration and the bootstrap
// script are applied as they were in the main process, so that PHP displays
// errors in a worker where it displays them in a sequential run.
$__phpunit_displayErrors = ini_get('display_errors');

{childProcessHead}

if ($__phpunit_displayErrors !== false) {
    ini_set('display_errors', $__phpunit_displayErrors);
}

// A sequential run preloads the code of PHPUnit and of its dependencies before
// the configuration and the bootstrap script are applied, so that no test
// triggers the loading of this code: a test may have changed how, or whether,
// code can be loaded, by replacing a stream wrapper or by registering an
// autoloader, for instance. The worker preloads the same code at the same
// point, so that the tests it runs see the same code loaded.
Preloader::preload();

{childProcessConfiguration}

$__phpunit_configuration = ConfigurationRegistry::get();

if ({collectCodeCoverageInformation}) {
    CodeCoverage::instance()->init($__phpunit_configuration, CodeCoverageFilterRegistry::instance(), true);
}

ErrorHandlerBootstrapper::bootstrap($__phpunit_configuration);

// The configured extensions that implement ChildProcessExtension are
// bootstrapped once, here, for the lifetime of the worker. Their subscribers
// are collected and registered with the dispatcher of every unit this worker
// runs (see __phpunit_worker_run_unit()); the warnings that a failed
// bootstrap produces are reported to the parent when the worker is stopped,
// together with the warnings that a failed shutdown produces, so that they
// do not depend on how the units this worker runs fare.
$__phpunit_extensionFacade       = new WorkerExtensionFacade(Facade::instance());
$__phpunit_extensionBootstrapper = null;

if (!$__phpunit_configuration->noExtensions()) {
    $__phpunit_extensionBootstrapper = new WorkerExtensionBootstrapper($__phpunit_configuration, $__phpunit_extensionFacade);

    foreach ($__phpunit_configuration->extensionBootstrappers() as $__phpunit_bootstrapper) {
        $__phpunit_extensionBootstrapper->bootstrap(
            $__phpunit_bootstrapper['className'],
            $__phpunit_bootstrapper['parameters'],
        );
    }
}

// A sequential run loads every test class file before it runs a test, so a
// test may rely on what another test class file declares: a class that it
// names in #[DataProviderExternal], an interface, a trait, a function, or a
// constant, for instance. The worker loads the test class files that the
// main process loaded, in the same order, so that the tests it runs see the
// same declarations, whichever unit it is asked to run. A file that no longer
// exists, because it was generated into a temporary directory that has been
// removed since, for instance, is skipped.
foreach ({testClassFiles} as $__phpunit_testClassFile) {
    if (is_file($__phpunit_testClassFile)) {
        require_once $__phpunit_testClassFile;
    }
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

function __phpunit_worker_halt_was_requested(string $haltFile): bool
{
    clearstatcache(true, $haltFile);

    return is_file($haltFile);
}

// The standard output and the standard error of the worker are captured in
// two files, which the parent process reads (see PersistentWorker): what is
// written to them while a unit runs, by a test whose output is not buffered
// or by a method that is called before the first or after the last test of a
// test class, for instance, is printed by the parent process, to its standard
// output and to its standard error, as it is printed in a sequential run. The
// numbers of bytes written so far travel with the events of every test that
// finishes, so that the parent process can tell what was written before the
// test finished from what was written after. A test may have closed the
// stream: what the worker wrote to it is then handed over with the result of
// the unit.
function __phpunit_worker_output_length(mixed $stream): int
{
    if (!is_resource($stream)) {
        return 0;
    }

    $statistics = fstat($stream);

    if ($statistics === false) {
        return 0;
    }

    return max(0, $statistics['size']);
}

// The parent process holds a lock on the lock file for as long as it lives
// (see PersistentWorker). Once the lock can be taken, or the lock file is not
// there anymore, the parent process is gone: it was killed, or it ended with
// a fatal error. The files are checked for before they are opened, so that no
// warning reaches an error handler that the bootstrap script may have
// registered.
function __phpunit_worker_parent_process_is_gone(string $lockFile): bool
{
    clearstatcache(true, $lockFile);

    if (!is_file($lockFile)) {
        return true;
    }

    $lock = fopen($lockFile, 'rb');

    if ($lock === false) {
        return true;
    }

    $parentProcessIsGone = flock($lock, LOCK_SH | LOCK_NB);

    fclose($lock);

    return $parentProcessIsGone;
}

// A unit is halted between two of its tests when the main process asks for
// it, and when the main process is gone: nobody is left to report the results
// of the tests that the unit would still run to, and a worker that goes on
// running them works on what the tests work on, a database for instance,
// after the test run has ended for whoever started it.
function __phpunit_worker_unit_is_to_be_halted(string $haltFile, string $lockFile): bool
{
    if (__phpunit_worker_halt_was_requested($haltFile)) {
        return true;
    }

    return __phpunit_worker_parent_process_is_gone($lockFile);
}

function __phpunit_worker_run_unit(array $command, array $extensionSubscribers, string $lockFile): string
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
        $dispatcher->registerSubscriber($__phpunit_subscriber);
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

                fwrite($handle, PHPUnit\Runner\Parallel\EventStream::frame($this->nonce, $this->dispatcher->flush(), __phpunit_worker_output_length(STDOUT), __phpunit_worker_output_length(STDERR)));

                fclose($handle);
            }
        },
    );

    // The main process asks the worker to halt the unit when the run is to
    // stop (--stop-on-*), by creating the unit's halt file (see
    // PersistentWorker::requestHalt()). The request is honoured between two
    // tests, as the sequential test runner stops between two tests: the test
    // run is interrupted, so that the test suite starts no further test and
    // runs the methods that run after the last test of the class,
    // tearDownAfterClass() for instance, and the unit does not leave its
    // fixtures behind. The request is checked for whenever a test has
    // finished, and when the methods that run before the first test of the
    // class have finished. An interrupted worker runs no further test, which
    // is of no concern: the main process does not dispatch another unit once
    // the run is stopping.
    //
    // The unit is halted in the same way when the main process is gone (see
    // __phpunit_worker_unit_is_to_be_halted()).
    //
    // The examples of a property are tests that finish as well, but their
    // events are diverted into a collection window and not dispatched to
    // subscribers. The request is therefore also checked for when such a
    // test has finished, so that a property is not checked for all of its
    // examples after the run was stopped: it stops between two examples, as
    // it does in a sequential run.
    $__phpunit_halt_subscriber = new class($command['haltFile'], $lockFile) implements PHPUnit\Event\Test\FinishedSubscriber
    {
        public function __construct(
            private readonly string $haltFile,
            private readonly string $lockFile,
        ) {
        }

        public function notify(PHPUnit\Event\Test\Finished $event): void
        {
            if (__phpunit_worker_unit_is_to_be_halted($this->haltFile, $this->lockFile)) {
                TestResultFacade::interrupt();
            }
        }
    };

    $dispatcher->registerSubscriber($__phpunit_halt_subscriber);
    $dispatcher->registerSubscriberForCollectedEvents($__phpunit_halt_subscriber);

    $dispatcher->registerSubscriber(
        new class($command['haltFile'], $lockFile) implements PHPUnit\Event\Test\BeforeFirstTestMethodFinishedSubscriber
        {
            public function __construct(
                private readonly string $haltFile,
                private readonly string $lockFile,
            ) {
            }

            public function notify(PHPUnit\Event\Test\BeforeFirstTestMethodFinished $event): void
            {
                if (__phpunit_worker_unit_is_to_be_halted($this->haltFile, $this->lockFile)) {
                    TestResultFacade::interrupt();
                }
            }
        },
    );

    // A test that depends on a test method whose test is run with the data
    // sets of a data provider, or repeated, can only run once that test
    // method has been recorded as passed. The test result collector, which
    // records this in the main process, does not receive the events of this
    // unit, so a recorder of the unit's own records it (see
    // PassedTestsRecorder).
    (new PassedTestsRecorder)->registerWith($dispatcher);

    require_once $command['file'];

    $suite        = TestSuite::forTestClass($command['className'], Facade::emitter());
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
            $suite->addTest($__phpunit_test->member($command['className'], $dataProvider));
        }
    } catch (WorkerException $e) {
        $failure = $e->getMessage();
    }

    // The value objects with which the tests of the unit are reported are
    // built when they are first asked for, which the parent process did
    // before it ran a test: when it reported that the test suite was loaded.
    // Building them can emit events, an error for a method that cannot be
    // used as a TestDox formatter, for instance. They are built here, before
    // the unit is run, so that these events are emitted here as well.
    PHPUnit\Event\TestSuite\TestSuiteBuilder::from($suite);

    // Invoking the data providers emitted the events that a data provider
    // invocation emits, and building the value objects of the tests emitted
    // the events that building them emits. The parent process emitted the
    // same events when it built the suite, and it is the parent's that are
    // reported; the ones emitted here are discarded so that they are not
    // reported a second time.
    $dispatcher->flush();

    // A unit whose halt was requested before it started is not run at all:
    // its tests come after the test that made the run stop.
    if ($failure === null && !__phpunit_worker_halt_was_requested($command['haltFile'])) {
        $suite->run();
    }

    $codeCoverage = null;

    if (CodeCoverage::instance()->isActive()) {
        $codeCoverage = CodeCoverage::instance()->collectedCodeCoverage();
    }

    $envelope = (object) [
        'codeCoverage' => $codeCoverage,
        'events'       => $dispatcher->flush(),
        'passedTests'  => PassedTests::instance()->withoutReturnValues(),
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

    return $result;
}

// The worker receives its commands through a command file, not through its
// standard input, which the parent process closes right after it has started
// the worker: a test that reads its standard input, or a process that a test
// starts and that inherits it, would otherwise wait forever for input on a
// channel that stays open between commands (see PersistentWorker). The worker
// polls for the next command and, every so often, checks whether the parent
// process is gone (see __phpunit_worker_parent_process_is_gone()); once it
// is, the worker exits. The command file is checked for before it is opened,
// so that no warning reaches an error handler that the bootstrap script may
// have registered.
function __phpunit_worker_next_command(string $commandFile, string $lockFile): ?string
{
    $nextLockCheck = 0;

    while (true) {
        clearstatcache(true, $commandFile);

        if (is_file($commandFile)) {
            $command = file_get_contents($commandFile);

            unlink($commandFile);

            if ($command === false) {
                return null;
            }

            return $command;
        }

        if (hrtime(true) >= $nextLockCheck) {
            if (__phpunit_worker_parent_process_is_gone($lockFile)) {
                return null;
            }

            $nextLockCheck = hrtime(true) + 100000000;
        }

        usleep(1000);
    }
}

$__phpunit_stopped = false;

while (($__phpunit_line = __phpunit_worker_next_command({commandFile}, {lockFile})) !== null) {
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
        // worker has run its last unit. The warnings that a failed bootstrap
        // or a failed shutdown produced are reported to the parent through
        // the command's result file.
        $__phpunit_extensionWarnings = [];

        if ($__phpunit_extensionBootstrapper !== null) {
            $__phpunit_extensionWarnings = array_merge(
                $__phpunit_extensionBootstrapper->warnings(),
                $__phpunit_extensionBootstrapper->shutdown(),
            );
        }

        file_put_contents(
            $__phpunit_command['resultFile'],
            $__phpunit_command['nonce'] . serialize($__phpunit_extensionWarnings),
        );

        $__phpunit_stopped = true;

        break;
    }

    $__phpunit_result = __phpunit_worker_run_unit(
        $__phpunit_command,
        $__phpunit_extensionFacade->subscribers(),
        {lockFile},
    );

    // Nobody is left to read the result of the unit when the parent process
    // is gone, and nobody is left to delete the files through which the
    // result of the unit travels: the worker deletes them before it exits.
    if (__phpunit_worker_parent_process_is_gone({lockFile})) {
        foreach ([$__phpunit_command['resultFile'], $__phpunit_command['streamFile']] as $__phpunit_file) {
            clearstatcache(true, $__phpunit_file);

            if (is_file($__phpunit_file)) {
                unlink($__phpunit_file);
            }
        }

        break;
    }

    file_put_contents($__phpunit_command['resultFile'], $__phpunit_result);

    // Signal completion through the filesystem rather than standard output: the
    // parent polls for this file, and because it is created only after the
    // result file has been fully written, its presence means the result is
    // ready to be read. This avoids the parent having to read the worker's
    // output pipe, which cannot be done without blocking on Windows.
    file_put_contents($__phpunit_command['doneFile'], $__phpunit_command['nonce']);
}

// A worker that was not stopped by the parent process, but exits because the
// parent process is gone, deletes the lock file that the parent process left
// behind.
if (!$__phpunit_stopped && __phpunit_worker_parent_process_is_gone({lockFile})) {
    clearstatcache(true, {lockFile});

    if (is_file({lockFile})) {
        unlink({lockFile});
    }
}
