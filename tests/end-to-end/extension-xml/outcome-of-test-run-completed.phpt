--TEST--
An extension has access to the outcome of a test run that was completed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/outcome-of-test-run/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECT--
status: completed
shell exit code: 1
tests run: 3
failed tests: 1
warnings triggered by tests: 1
warnings triggered by the test runner: 0
