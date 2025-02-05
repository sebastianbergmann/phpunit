--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6100
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--fail-on-deprecation';
$_SERVER['argv'][] = '--stop-on-deprecation';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/6100/Issue6100Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Issue6100\Issue6100Test, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Issue6100\Issue6100Test::testOne)
Test Prepared (PHPUnit\TestFixture\Issue6100\Issue6100Test::testOne)
Test Triggered Deprecation (PHPUnit\TestFixture\Issue6100\Issue6100Test::testOne, unknown if issue was triggered in first-party code or third-party code, suppressed using operator) in %s:%d
test
Test Passed (PHPUnit\TestFixture\Issue6100\Issue6100Test::testOne)
Test Finished (PHPUnit\TestFixture\Issue6100\Issue6100Test::testOne)
Test Preparation Started (PHPUnit\TestFixture\Issue6100\Issue6100Test::testTwo)
Test Prepared (PHPUnit\TestFixture\Issue6100\Issue6100Test::testTwo)
Test Passed (PHPUnit\TestFixture\Issue6100\Issue6100Test::testTwo)
Test Finished (PHPUnit\TestFixture\Issue6100\Issue6100Test::testTwo)
Test Suite Finished (PHPUnit\TestFixture\Issue6100\Issue6100Test, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
