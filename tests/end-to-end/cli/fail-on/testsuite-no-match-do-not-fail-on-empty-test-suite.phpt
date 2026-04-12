--TEST--
Test Runner exits with shell exit code indicating success when --testsuite does not match and --do-not-fail-on-empty-test-suite is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/multiple-testsuites/phpunit.xml';
$_SERVER['argv'][] = '--testsuite';
$_SERVER['argv'][] = 'doesNotExist';
$_SERVER['argv'][] = '--do-not-fail-on-empty-test-suite';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (0 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (0 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
