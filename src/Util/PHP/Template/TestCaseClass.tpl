<?php declare(strict_types=1);
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\CodeCoverage;
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
set_include_path(unserialize('{include_path}'));

$composerAutoload = {composerAutoload};
$phar             = {phar};

ob_start();

if ($composerAutoload) {
    require_once $composerAutoload;

    define('PHPUNIT_COMPOSER_INSTALL', $composerAutoload);
} else if ($phar) {
    require $phar;
}

function __phpunit_run_isolated_test()
{
    $dispatcher = Facade::instance()->initForIsolation(
        PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
            {offsetSeconds},
            {offsetNanoseconds}
        ),
        {exportObjects},
    );

    require_once '{filename}';

    if ({collectCodeCoverageInformation}) {
        CodeCoverage::instance()->init(ConfigurationRegistry::get(), CodeCoverageFilterRegistry::instance(), true);
        CodeCoverage::instance()->ignoreLines({linesToBeIgnored});
    }

    $output = '';

    $tests = unserialize('{testData}');
    foreach ($tests as &$test) {
        $className = $test['className'];
        $methodName = $test['methodName'];
        $dataName = unserialize($test['dataName']);
        $data = unserialize($test['data']);
        $dependencyInput = unserialize($test['dependencyInput']);

        $test = new $className($methodName);

        assert($test instanceof TestCase);

        $test->setData($dataName, $data);
        $test->setDependencyInput($dependencyInput);
        $test->setInIsolation(true);

        @ob_end_clean();

        $test->run();

        if (!$test->expectsOutput()) {
            $output .= $test->output();
        }
    }

    ini_set('xdebug.scream', '0');

    // Not every STDOUT target stream is rewindable
    @rewind(STDOUT);

    if ($stdout = @stream_get_contents(STDOUT)) {
        $output         = $stdout . $output;
        $streamMetaData = stream_get_meta_data(STDOUT);

        if (!empty($streamMetaData['stream_type']) && 'STDIO' === $streamMetaData['stream_type']) {
            @ftruncate(STDOUT, 0);
            @rewind(STDOUT);
        }
    }

    $fileContents = '';

    foreach ($tests as $test) {
        $fileContents .= serialize(
            [
                'testResult'    => $test->result(),
                'codeCoverage'  => {collectCodeCoverageInformation} ? CodeCoverage::instance()->codeCoverage() : null,
                'numAssertions' => $test->numberOfAssertionsPerformed(),
                'output'        => $output,
                'events'        => $dispatcher->flush(),
                'passedTests'   => PassedTests::instance()
            ]
        );
    }

    file_put_contents(
        '{processResultFile}',
        $fileContents,
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
(new PhpHandler)->handle(ConfigurationRegistry::get()->php());

if ('{bootstrap}' !== '') {
    require_once '{bootstrap}';
}

__phpunit_run_isolated_test();
