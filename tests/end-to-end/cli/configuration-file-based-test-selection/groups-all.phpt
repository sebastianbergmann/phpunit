--TEST--
Tests selected using <groups> with "--all" CLI option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/configuration-file-based-test-selection/groups';
$_SERVER['argv'][] = '--all';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (%sphpunit.xml, 2 tests)
Test Suite Started (default, 2 tests)
Test Suite Started (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest::testOne)
Test Prepared (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest::testOne)
Test Passed (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest::testOne)
Test Finished (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest::testTwo)
Test Passed (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest::testTwo)
Test Finished (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups\ExampleTest, 2 tests)
Test Suite Finished (default, 2 tests)
Test Suite Finished (%sphpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
