<?php
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use PHPUnit\TextUI\XmlConfiguration\PhpHandler;

if (!defined('STDOUT')) {
    // php://stdout does not obey output buffering. Any output would break
    // unserialization of child process results in the parent process.
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

function __phpunit_run_isolated_test()
{
    $dispatcher = Facade::initForIsolation(
        PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
            {offsetSeconds},
            {offsetNanoseconds}
        )
    );

    if (!class_exists('{className}')) {
        require_once '{filename}';
    }

    $result = new PHPUnit\Framework\TestResult;

    if ({collectCodeCoverageInformation}) {
        CodeCoverage::activate(
            unserialize('{codeCoverageFilter}'),
            {pathCoverage}
        );

        if ({cachesStaticAnalysis}) {
            CodeCoverage::instance()->cacheStaticAnalysis(unserialize('{codeCoverageCacheDirectory}'));
        }
    }

    $test = new {className}('{methodName}');
    \assert($test instanceof TestCase);

    $test->setData('{dataName}', unserialize('{data}'));
    $test->setDependencyInput(unserialize('{dependencyInput}'));
    $test->setInIsolation(true);

    ob_end_clean();
    $test->run($result);
    $output = '';
    if (!$test->hasExpectationOnOutput()) {
        $output = $test->output();
    }

    ini_set('xdebug.scream', '0');
    @rewind(STDOUT); /* @ as not every STDOUT target stream is rewindable */
    if ($stdout = @stream_get_contents(STDOUT)) {
        $output = $stdout . $output;
        $streamMetaData = stream_get_meta_data(STDOUT);
        if (!empty($streamMetaData['stream_type']) && 'STDIO' === $streamMetaData['stream_type']) {
            @ftruncate(STDOUT, 0);
            @rewind(STDOUT);
        }
    }

    print serialize(
      [
        'testResult'    => $test->result(),
        'codeCoverage'  => {collectCodeCoverageInformation} ? CodeCoverage::instance() : null,
        'numAssertions' => $test->numberOfAssertionsPerformed(),
        'result'        => $result,
        'output'        => $output,
        'events'        => $dispatcher->flush()
      ]
    );
}

$configurationFilePath = '{configurationFilePath}';

if ('' !== $configurationFilePath) {
    $configuration = (new Loader)->load($configurationFilePath);

    (new PhpHandler)->handle($configuration->php());

    unset($configuration);
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

if (isset($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
    require_once $GLOBALS['__PHPUNIT_BOOTSTRAP'];
    unset($GLOBALS['__PHPUNIT_BOOTSTRAP']);
}

Registry::loadFrom('{serializedConfiguration}');

__phpunit_run_isolated_test();
