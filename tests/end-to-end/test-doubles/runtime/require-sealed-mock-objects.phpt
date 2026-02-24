--TEST--
The right events are emitted in the right order for a successful test that uses an unsealed mock object when sealed mock objects are required
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/require-sealed-mock-objects/phpunit.xml';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/../_files/require-sealed-mock-objects/src/I.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/require-sealed-mock-objects/tests/RequireSealedMockObjectsTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sI.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\RequireSealedMockObjectsTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\RequireSealedMockObjectsTest::testOne)
Test Prepared (PHPUnit\TestFixture\RequireSealedMockObjectsTest::testOne)
Mock Object Created (PHPUnit\TestFixture\I)
Test Considered Risky (PHPUnit\TestFixture\RequireSealedMockObjectsTest::testOne)
Mock object for PHPUnit\TestFixture\I has not been sealed
Test Passed (PHPUnit\TestFixture\RequireSealedMockObjectsTest::testOne)
Test Finished (PHPUnit\TestFixture\RequireSealedMockObjectsTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\RequireSealedMockObjectsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
