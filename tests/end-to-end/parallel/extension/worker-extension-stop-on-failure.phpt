--TEST--
phpunit --parallel=2 --stop-on-failure shuts down an extension that implements ChildProcessExtension in every worker process, including the one that is running a test when the run stops
--FILE--
<?php declare(strict_types=1);
$log    = tempnam(sys_get_temp_dir(), 'phpunit_lifecycle_');
$marker = tempnam(sys_get_temp_dir(), 'phpunit_marker_');

putenv('PHPUNIT_TEST_WORKER_LIFECYCLE_LOG=' . $log);
putenv('PHPUNIT_TEST_WORKER_STOP_MARKER=' . $marker);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/worker-extension-stop-on-failure/phpunit.xml';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--parallel=2';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$bootstrapped = [];
$shutDown     = [];

foreach (file($log, FILE_IGNORE_NEW_LINES) as $line) {
    [$token, $what] = explode(' ', $line, 2);

    if ($what === 'bootstrapped') {
        $bootstrapped[] = $token;
    } else {
        $shutDown[] = $token;
    }
}

$started = file_get_contents($marker);

unlink($log);
unlink($marker);

sort($bootstrapped);
sort($shutDown);

var_dump($started);
var_dump(count($bootstrapped));
var_dump($bootstrapped === $shutDown);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit.xml
Parallel:      2 workers

F

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelWorkerExtension\StopOnFailure\AFailsOnceTheOtherTestHasStartedTest::testFails
failure

%sAFailsOnceTheOtherTestHasStartedTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
string(7) "started"
int(2)
bool(true)
