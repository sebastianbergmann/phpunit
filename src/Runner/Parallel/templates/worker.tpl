<?php declare(strict_types=1);
use PHPUnit\Event\Facade;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\RetryTestSuite;
use PHPUnit\Framework\TestRunner\ErrorHandlerBootstrapper;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\PhpHandler;
use PHPUnit\TextUI\Configuration\SourceMapper;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
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

ConfigurationRegistry::loadFrom('{serializedConfiguration}');

DifferBuilder::configureComparatorFactory();

if ('{sourceMapFile}' !== '') {
    SourceMapper::loadFrom('{sourceMapFile}', ConfigurationRegistry::get()->source());
}

(new PhpHandler)->handle(ConfigurationRegistry::get()->php());

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

$__phpunit_configuration = ConfigurationRegistry::get();

if ({collectCodeCoverageInformation}) {
    CodeCoverage::instance()->init($__phpunit_configuration, CodeCoverageFilterRegistry::instance(), true);
}

ErrorHandlerBootstrapper::bootstrap($__phpunit_configuration);

// A unit of work is run as a TestSuite, whose run loop consults the test
// result facade to decide whether to stop. The facade lazily registers its
// collector as an event subscriber on first use, which is not possible once
// the event facade has been sealed for isolation. The collector is therefore
// created here, while the facade is still open; it never receives events in
// the worker (the parent process owns the authoritative test result), so the
// worker never decides to stop on its own.
TestResultFacade::init();

ob_end_clean();

function __phpunit_worker_build_test(string $className, object $descriptor): PHPUnit\Framework\TestCase
{
    $test = new $className($descriptor->methodName);

    // A string data-set name travels base64-encoded because it is not
    // required to be valid UTF-8; an integer name travels as-is.
    $dataName = $descriptor->dataName;

    if (is_string($dataName)) {
        $dataName = base64_decode($dataName);
    }

    $test->setData($dataName, unserialize(base64_decode($descriptor->data)));
    $test->setDependencyInput(unserialize(base64_decode($descriptor->dependencyInput)));
    $test->setRepetition($descriptor->repetition, $descriptor->totalRepetitions);
    $test->setAttempt($descriptor->attempt, $descriptor->maxAttempts);

    return $test;
}

function __phpunit_worker_build_member(string $className, object $descriptor): PHPUnit\Framework\Test
{
    // The tests of a data provider method travel as their
    // DataProviderTestSuite so that the suite's event envelope, which nests
    // the tests in the logger output of a sequential run, is emitted here,
    // inside the worker, too. The suite is rebuilt exactly as TestBuilder
    // builds it, so its "test suite started" event carries the same value.
    if ($descriptor->type === 'dataProvider') {
        $suite = DataProviderTestSuite::empty($descriptor->name);

        foreach ($descriptor->tests as $member) {
            $suite->addTest(__phpunit_worker_build_member($className, $member));
        }

        return $suite;
    }

    // A retried test method travels as its RetryTestSuite so that the
    // retry orchestration runs inside the worker; additional attempts are
    // built here, from the same descriptor as the first one.
    if ($descriptor->type === 'retry') {
        $testDescriptor = $descriptor->test;

        $factory = static function () use ($className, $testDescriptor): PHPUnit\Framework\TestCase
        {
            return __phpunit_worker_build_test($className, $testDescriptor);
        };

        return RetryTestSuite::fromTestCase(
            $descriptor->name,
            $factory(),
            $descriptor->maxAttempts,
            $factory,
        );
    }

    // A repeated test method travels as its RepeatTestSuite so that the
    // repetition orchestration (failure threshold, skipping of remaining
    // repetitions) runs inside the worker.
    if ($descriptor->type === 'repeat') {
        $repetitions = [];

        foreach ($descriptor->tests as $repetition) {
            $repetitions[] = __phpunit_worker_build_test($className, $repetition);
        }

        return RepeatTestSuite::fromTests(
            $descriptor->name,
            $repetitions,
            $descriptor->failureThreshold,
        );
    }

    return __phpunit_worker_build_test($className, $descriptor);
}

function __phpunit_worker_run_unit(object $command): string
{
    $dispatcher = Facade::instance()->initForIsolation(
        PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
            $command->offsetSeconds,
            $command->offsetNanoseconds
        ),
    );

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
        new class($dispatcher, $command->streamFile, $command->nonce) implements PHPUnit\Event\Test\FinishedSubscriber
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

    require_once $command->file;

    $suite = TestSuite::empty($command->className);

    foreach ($command->tests as $__phpunit_test) {
        $suite->addTest(__phpunit_worker_build_member($command->className, $__phpunit_test));
    }

    $suite->run();

    $codeCoverage = null;

    if (CodeCoverage::instance()->isActive()) {
        $codeCoverage = CodeCoverage::instance()->codeCoverage();
    }

    $result = $command->nonce . serialize(
        (object) [
            'codeCoverage' => $codeCoverage,
            'events'       => $dispatcher->flush(),
            'passedTests'  => PassedTests::instance(),
        ]
    );

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

    $__phpunit_command = json_decode($__phpunit_line);

    if ($__phpunit_command->command === 'stop') {
        break;
    }

    $__phpunit_result = __phpunit_worker_run_unit($__phpunit_command);

    file_put_contents($__phpunit_command->resultFile, $__phpunit_result);

    // Signal completion through the filesystem rather than standard output: the
    // parent polls for this file, and because it is created only after the
    // result file has been fully written, its presence means the result is
    // ready to be read. This avoids the parent having to read the worker's
    // output pipe, which cannot be done without blocking on Windows.
    file_put_contents($__phpunit_command->doneFile, $__phpunit_command->nonce);
}
