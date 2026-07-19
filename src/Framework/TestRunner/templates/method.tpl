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

    $test = new {className}('{methodName}');

    $test->setData('{dataName}', unserialize('{data}'));
    $test->setDependencyInput(unserialize('{dependencyInput}'));
    $test->setRepetition({repetition}, {totalRepetitions});
    $test->setAttempt({attempt}, {maxAttempts});
    $test->setInIsolation(true);

    ob_end_clean();

    $test->run();

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

__phpunit_run_isolated_test();
