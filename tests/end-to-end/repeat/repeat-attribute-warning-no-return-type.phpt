--TEST--
#[Repeat] on method without return type declaration triggers a test runner warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatAttributeWithoutReturnTypeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Repeat\RepeatAttributeWithoutReturnTypeTest::testWithoutReturnType is annotated with #[Repeat] but does not have a void return type declaration and will not be repeated)
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithoutReturnTypeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithoutReturnTypeTest::testWithoutReturnType)
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeWithoutReturnTypeTest::testWithoutReturnType)
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeWithoutReturnTypeTest::testWithoutReturnType)
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithoutReturnTypeTest::testWithoutReturnType)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithoutReturnTypeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
