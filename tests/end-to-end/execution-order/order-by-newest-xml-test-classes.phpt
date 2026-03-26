--TEST--
Order by newest: Test classes with different modification times
--FILE--
<?php declare(strict_types=1);
$fixture_dir    = __DIR__ . '/fixture/test-classes-with-different-modification-times';
$bootstrap_path = __DIR__ . '/../../bootstrap.php';

$xmlConfig = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit executionOrder="newest" bootstrap="{$bootstrap_path}">
  <testsuites>
      <testsuite name="newest-test">
          <directory>{$fixture_dir}</directory>
      </testsuite>
  </testsuites>
</phpunit>
XML;

// XML config must be in a file
$configFile = tempnam(sys_get_temp_dir(), 'pnt');
file_put_contents($configFile, $xmlConfig);

$_SERVER['argv'] = ['phpunit', '--configuration', $configFile, '--debug'];

// Force the modified times to be in order
touch("$fixture_dir/OldTest.php", strtotime('2026-01-01 00:00:00'));
touch("$fixture_dir/MiddleTest.php", strtotime('2026-01-02 00:00:00'));
touch("$fixture_dir/NewTest.php", strtotime('2026-01-03 00:00:00'));

require $bootstrap_path;

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%s)
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (%s, 3 tests)
Test Suite Started (newest-test, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\NewTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\NewTest::testNew)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\NewTest::testNew)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\NewTest::testNew)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\NewTest::testNew)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\NewTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\MiddleTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\MiddleTest::testMiddle)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\MiddleTest::testMiddle)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\MiddleTest::testMiddle)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\MiddleTest::testMiddle)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\MiddleTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\OldTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\OldTest::testOld)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\OldTest::testOld)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\OldTest::testOld)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\OldTest::testOld)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\ModificationTime\OldTest, 1 test)
Test Suite Finished (newest-test, 3 tests)
Test Suite Finished (%s, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
