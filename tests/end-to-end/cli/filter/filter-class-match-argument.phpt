--TEST--
phpunit --filter FooTest tests/FooTest.php
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'FooTest';
$_SERVER['argv'][] = __DIR__ . '/../../_files/groups/tests/FooTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (3 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Suite Filtered (3 tests)
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\Groups\FooTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Groups\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Groups\FooTest::testTwo)
Test Passed (PHPUnit\TestFixture\Groups\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\Groups\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Passed (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Finished (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\Groups\FooTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
