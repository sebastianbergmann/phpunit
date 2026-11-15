--TEST--
An extension has access to the exit code of the child process of a test that runs in a separate process
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/child-process-exit-status/phpunit.xml';
$_SERVER['argv'][] = __DIR__ . '/_files/child-process-exit-status/tests/EndsNormallyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECT--
child process that finished ended with exit code 0
