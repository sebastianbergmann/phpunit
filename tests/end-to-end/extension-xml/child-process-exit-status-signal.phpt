--TEST--
An extension has access to the signal that terminated the child process of a test that runs in a separate process
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('posix')) {
    echo 'skip: Extension posix is required';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/child-process-exit-status/phpunit.xml';
$_SERVER['argv'][] = __DIR__ . '/_files/child-process-exit-status/tests/IsKilledTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECT--
child process that errored terminated by signal 9
child process that finished terminated by signal 9
