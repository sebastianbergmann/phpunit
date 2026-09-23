--TEST--
A subscriber that a PHPUnit extension registers can tell the events of a test that ran in a separate process from those of its own process by the process ID in their telemetry information
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/process-of-origin/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECT--
testInThisProcess finished in this process
testInSeparateProcess finished in another process
