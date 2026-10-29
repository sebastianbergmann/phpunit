--TEST--
phpunit --parallel=2 --recycle-workers-after=1 shuts down an extension that implements ChildProcessExtension in every worker process, including the ones that are replaced with a fresh process
--FILE--
<?php declare(strict_types=1);
$log = tempnam(sys_get_temp_dir(), 'phpunit_lifecycle_');

putenv('PHPUNIT_TEST_WORKER_LIFECYCLE_LOG=' . $log);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/worker-extension-recycling/phpunit.xml';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--recycle-workers-after=1';

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

unlink($log);

sort($bootstrapped);
sort($shutDown);

var_dump(count($bootstrapped) > 2);
var_dump($bootstrapped === $shutDown);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit.xml
Parallel:      2 workers

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
bool(true)
bool(true)
