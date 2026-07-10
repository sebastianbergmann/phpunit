--TEST--
Test Runner exits with shell exit code indicating success when a test triggered an indirect deprecation and --fail-on-self-deprecation is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-trigger/phpunit.xml';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--fail-on-self-deprecation';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-trigger/tests/IndirectDeprecationTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\FailOnDeprecationTrigger\IndirectDeprecationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\FailOnDeprecationTrigger\IndirectDeprecationTest::testOne)
Test Prepared (PHPUnit\TestFixture\FailOnDeprecationTrigger\IndirectDeprecationTest::testOne)
Test Triggered Deprecation (PHPUnit\TestFixture\FailOnDeprecationTrigger\IndirectDeprecationTest::testOne, issue triggered by third-party code calling into third-party code) in %s:%d
deprecation triggered in third-party code
Test Passed (PHPUnit\TestFixture\FailOnDeprecationTrigger\IndirectDeprecationTest::testOne)
Test Finished (PHPUnit\TestFixture\FailOnDeprecationTrigger\IndirectDeprecationTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\FailOnDeprecationTrigger\IndirectDeprecationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
