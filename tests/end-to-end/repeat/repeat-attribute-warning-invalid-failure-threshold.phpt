--TEST--
#[Repeat] with a failure threshold that is not a positive integer triggers a test runner warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatAttributeInvalidFailureThresholdTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidFailureThresholdTest::testOne is annotated with #[Repeat] but 0 is not a positive integer for the failure threshold and will not be repeated)
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidFailureThresholdTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidFailureThresholdTest::testOne)
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidFailureThresholdTest::testOne)
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidFailureThresholdTest::testOne)
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidFailureThresholdTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidFailureThresholdTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
