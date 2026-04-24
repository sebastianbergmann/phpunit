--TEST--
#[Repeat] on method that depends on another test triggers a test runner warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatAttributeOnDependentTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest::testTwo is annotated with #[Repeat] but depends on another test and will not be repeated)
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest::testOne)
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest::testOne)
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest::testOne)
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest::testTwo)
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest::testTwo)
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeOnDependentTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
