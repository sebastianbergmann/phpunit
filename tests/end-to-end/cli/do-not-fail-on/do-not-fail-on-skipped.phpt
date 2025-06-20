--TEST--
todo
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--do-not-fail-on-skipped';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/do-not-fail-on/phpunit.xml';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testThatIsSkipped';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (%s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (8 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\DoNotFailOn\IssueTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\DoNotFailOn\IssueTest::testThatIsSkipped)
Test Prepared (PHPUnit\TestFixture\DoNotFailOn\IssueTest::testThatIsSkipped)
Test Skipped (PHPUnit\TestFixture\DoNotFailOn\IssueTest::testThatIsSkipped)
message
Test Finished (PHPUnit\TestFixture\DoNotFailOn\IssueTest::testThatIsSkipped)
Test Suite Finished (PHPUnit\TestFixture\DoNotFailOn\IssueTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
