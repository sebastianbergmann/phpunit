--TEST--
Order by modification time ascending: Suite with test classes whose files were modified at different times
--FILE--
<?php declare(strict_types=1);
$fixtures = __DIR__ . '/fixture/test-classes-with-different-modification-times';
$sandbox  = sys_get_temp_dir() . '/' . basename(__FILE__, '.phpt');

@mkdir($sandbox);

/*
 * The modification time of a file in a working copy is the time it was checked
 * out, so the fixtures are copied to a directory of their own, where their
 * modification times can be set explicitly.
 */
$modificationTimes = [
    'OldTest'    => 1704067200,
    'MiddleTest' => 1735689600,
    'NewTest'    => 1767225600,
];

foreach ($modificationTimes as $test => $modificationTime) {
    copy($fixtures . '/' . $test . '.php', $sandbox . '/' . $test . '.php');
    touch($sandbox . '/' . $test . '.php', $modificationTime);
}

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'modified-ascending';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = $sandbox;

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
$sandbox = sys_get_temp_dir() . '/' . basename(__FILE__, '.phpt');

foreach (glob($sandbox . '/*.php') as $file) {
    unlink($file);
}

rmdir($sandbox);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (CLI Arguments, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\OldTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\OldTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\OldTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\OldTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\OldTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\OldTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\MiddleTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\MiddleTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\MiddleTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\MiddleTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\MiddleTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\MiddleTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\NewTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\NewTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\NewTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\NewTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\NewTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentModificationTimes\NewTest, 1 test)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
