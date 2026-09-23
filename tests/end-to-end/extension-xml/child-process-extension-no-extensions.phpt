--TEST--
An extension that implements ChildProcessExtension is not bootstrapped in the child process of a test that runs in a separate process when --no-extensions is used
--FILE--
<?php declare(strict_types=1);
$log = tempnam(sys_get_temp_dir(), __FILE__);
putenv('PHPUNIT_CHILD_PROCESS_EXTENSION_LOG=' . $log);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-extensions';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/child-process-extension/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($log);

unlink($log);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
running testRunsInSeparateProcess
running testRunsInSeparateProcess
running testRunsInMainProcessUnlessProcessIsolationIsConfigured
