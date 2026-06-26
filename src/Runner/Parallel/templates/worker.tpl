<?php declare(strict_types=1);
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestRunner\ChildProcessOutputCollector;
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

// The real standard output is captured as the worker's control channel before
// STDOUT is potentially redirected to capture the output produced by tests.
$__phpunit_worker_control = fopen('php://fd/1', 'wb');

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

function __phpunit_worker_run_test(object $command): string
{
    $dispatcher = Facade::instance()->initForIsolation(
        PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
            $command->offsetSeconds,
            $command->offsetNanoseconds
        ),
    );

    require_once $command->file;

    $className  = $command->className;
    $methodName = $command->methodName;

    $test = new $className($methodName);

    $test->setData($command->dataName, unserialize(base64_decode($command->data)));
    $test->setDependencyInput(unserialize(base64_decode($command->dependencyInput)));
    $test->setRepetition($command->repetition, $command->totalRepetitions);
    $test->setAttempt($command->attempt, $command->maxAttempts);
    $test->setInIsolation(true);

    $test->run();

    $output = ChildProcessOutputCollector::collect($test);

    $codeCoverage = null;

    if (CodeCoverage::instance()->isActive()) {
        $codeCoverage = CodeCoverage::instance()->codeCoverage();
    }

    $result = $command->nonce . serialize(
        (object) [
            'testResult'    => $test->result(),
            'status'        => $test->status(),
            'codeCoverage'  => $codeCoverage,
            'numAssertions' => $test->numberOfAssertionsPerformed(),
            'output'        => $output,
            'events'        => $dispatcher->flush(),
            'passedTests'   => PassedTests::instance(),
        ]
    );

    // Per-test code coverage has been collected for this command and is about
    // to be shipped to the parent process. It is cleared here so that the next
    // test executed by this worker does not ship it a second time.
    if (CodeCoverage::instance()->isActive()) {
        CodeCoverage::instance()->codeCoverage()->clear();
    }

    // Reset the stream that captures test output so that the next test does
    // not inherit the output of the test that has just finished.
    if (@rewind(STDOUT)) {
        @ftruncate(STDOUT, 0);
    }

    return $result;
}

function __phpunit_worker_run_unit(object $command): string
{
    $dispatcher = Facade::instance()->initForIsolation(
        PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
            $command->offsetSeconds,
            $command->offsetNanoseconds
        ),
    );

    require_once $command->file;

    $suite = TestSuite::empty($command->className);

    foreach ($command->tests as $__phpunit_test) {
        $className  = $command->className;
        $methodName = $__phpunit_test->methodName;

        $test = new $className($methodName);

        $test->setData($__phpunit_test->dataName, unserialize(base64_decode($__phpunit_test->data)));
        $test->setDependencyInput(unserialize(base64_decode($__phpunit_test->dependencyInput)));
        $test->setRepetition($__phpunit_test->repetition, $__phpunit_test->totalRepetitions);
        $test->setAttempt($__phpunit_test->attempt, $__phpunit_test->maxAttempts);

        $suite->addTest($test);
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

    // Reset the stream that captures test output so that the next unit does
    // not inherit the output of the unit that has just finished.
    if (@rewind(STDOUT)) {
        @ftruncate(STDOUT, 0);
    }

    return $result;
}

function __phpunit_worker_run_phpt(object $command): string
{
    $dispatcher = Facade::instance()->initForIsolation(
        PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
            $command->offsetSeconds,
            $command->offsetNanoseconds
        ),
    );

    $test = new PHPUnit\Runner\Phpt\TestCase($command->file);

    $test->run();

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

    // Per-test code coverage has been collected for this command and is about
    // to be shipped to the parent process. It is cleared here so that the next
    // test executed by this worker does not ship it a second time.
    if (CodeCoverage::instance()->isActive()) {
        CodeCoverage::instance()->codeCoverage()->clear();
    }

    // Reset the stream that captures test output so that the next test does
    // not inherit the output of the test that has just finished.
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

    if ($__phpunit_command->command === 'runUnit') {
        $__phpunit_result = __phpunit_worker_run_unit($__phpunit_command);
    } else if ($__phpunit_command->command === 'runPhpt') {
        $__phpunit_result = __phpunit_worker_run_phpt($__phpunit_command);
    } else {
        $__phpunit_result = __phpunit_worker_run_test($__phpunit_command);
    }

    file_put_contents($__phpunit_command->resultFile, $__phpunit_result);

    fwrite($__phpunit_worker_control, 'PHPUNIT_WORKER_DONE:' . $__phpunit_command->nonce . "\n");
    fflush($__phpunit_worker_control);
}
