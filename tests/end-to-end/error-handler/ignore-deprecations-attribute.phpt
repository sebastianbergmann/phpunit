--TEST--
Test method with #[IgnoreDeprecations] attribute reports deprecation as ignored by test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/IgnoreDeprecationsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\IgnoreDeprecationsTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\IgnoreDeprecationsTest::testDeprecationIgnoredByTest)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\IgnoreDeprecationsTest::testDeprecationIgnoredByTest)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\IgnoreDeprecationsTest::testDeprecationIgnoredByTest, unknown if issue was triggered in first-party code or third-party code, ignored by test) in %s:%d
deprecation message
Test Passed (PHPUnit\TestFixture\ErrorHandler\IgnoreDeprecationsTest::testDeprecationIgnoredByTest)
Test Finished (PHPUnit\TestFixture\ErrorHandler\IgnoreDeprecationsTest::testDeprecationIgnoredByTest)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\IgnoreDeprecationsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
