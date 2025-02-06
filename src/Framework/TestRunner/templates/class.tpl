<?php declare(strict_types=1);
use PHPUnit\Event;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\PhpHandler;
use PHPUnit\TestRunner\TestResult\PassedTests;

// php://stdout does not obey output buffering. Any output would break
// unserialization of child process results in the parent process.
if (!defined('STDOUT')) {
    define('STDOUT', fopen('php://temp', 'w+b'));
    define('STDERR', fopen('php://stderr', 'wb'));
}

{iniSettings}
ini_set('display_errors', 'stderr');
set_include_path('{include_path}');

$composerAutoload = {composerAutoload};
$phar             = {phar};

ob_start();

if ($composerAutoload) {
    require_once $composerAutoload;

    define('PHPUNIT_COMPOSER_INSTALL', $composerAutoload);
} else if ($phar) {
    require $phar;
}

function __phpunit_run_isolated_class()
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

    $deprecationTriggers = [
        'functions' => [],
        'methods'   => [],
    ];

    foreach ($configuration->source()->deprecationTriggers()['functions'] as $function) {
        $deprecationTriggers['functions'][] = $function;
    }

    foreach ($configuration->source()->deprecationTriggers()['methods'] as $method) {
        [$className, $methodName] = explode('::', $method);

        $deprecationTriggers['methods'][] = [
            'className'  => $className,
            'methodName' => $methodName,
        ];
    }

    ErrorHandler::instance()->useDeprecationTriggers($deprecationTriggers);

    ini_set('xdebug.scream', '0');

    try {
        $testClass = (new TestSuiteLoader)->load('{filename}');
    } catch (Exception $e) {
        print $e->getMessage() . PHP_EOL;
        exit(1);
    }

    $output = '';
    $results = [];

    $suite = TestSuite::fromClassReflector($testClass);
    $suite->setIsInSeparatedProcess(false);

    $testSuiteValueObjectForEvents = Event\TestSuite\TestSuiteBuilder::from($suite);

    if (!$suite->invokeMethodsBeforeFirstTest(Facade::emitter(), $testSuiteValueObjectForEvents)) {
        return;
    }

    foreach($suite->tests() as $test) {
        $test->setRunClassInSeparateProcess(false);
        $test->run();

        $testOutput = '';

        if (!$test->expectsOutput()) {
            $testOutput = $test->output();
        }

        // Not every STDOUT target stream is rewindable
        @rewind(STDOUT);

        if ($stdout = @stream_get_contents(STDOUT)) {
            $testOutput     = $stdout . $testOutput;
            $streamMetaData = stream_get_meta_data(STDOUT);

            if (!empty($streamMetaData['stream_type']) && 'STDIO' === $streamMetaData['stream_type']) {
                @ftruncate(STDOUT, 0);
                @rewind(STDOUT);
            }
        }

        $results[] = (object)[
            'testResult'    => $test->result(),
            'codeCoverage'  => {collectCodeCoverageInformation} ? CodeCoverage::instance()->codeCoverage() : null,
            'numAssertions' => $test->numberOfAssertionsPerformed(),
            'output'        => $testOutput,
            'events'        => $dispatcher->flush(),
            'passedTests'   => PassedTests::instance()
        ];

        $output .= $testOutput;
    }

    $suite->invokeMethodsAfterLastTest(Facade::emitter());

    Facade::emitter()->testRunnerFinishedChildProcess($output, '');

    file_put_contents('{processResultFile}', serialize($results));
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
(new PhpHandler)->handle(ConfigurationRegistry::get()->php());

if ('{bootstrap}' !== '') {
    require_once '{bootstrap}';
}

__phpunit_run_isolated_class();
