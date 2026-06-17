<?php declare(strict_types=1);
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestRunner\ChildProcessOutputCollector;
use PHPUnit\Framework\TestRunner\ErrorHandlerBootstrapper;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\PhpHandler;
use PHPUnit\TextUI\Configuration\SourceMapper;
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

    $__phpunit_result = __phpunit_worker_run_test($__phpunit_command);

    file_put_contents($__phpunit_command->resultFile, $__phpunit_result);

    fwrite($__phpunit_worker_control, 'PHPUNIT_WORKER_DONE:' . $__phpunit_command->nonce . "\n");
    fflush($__phpunit_worker_control);
}
