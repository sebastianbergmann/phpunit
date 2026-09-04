--TEST--
Order by modification time descending: Suite with test classes whose parent class was modified
--FILE--
<?php declare(strict_types=1);
$fixtures = __DIR__ . '/fixture/test-classes-with-modified-parent-class';
$sandbox  = sys_get_temp_dir() . '/' . basename(__FILE__, '.phpt');

@mkdir($sandbox);

/*
 * The modification time of a file in a working copy is the time it was checked
 * out, so the fixtures are copied to a directory of their own, where their
 * modification times can be set explicitly.
 *
 * AlsoInheritingTest and InheritingTest inherit their test methods from
 * BaseTestCase, which uses a trait. Their own files are the oldest of the five,
 * the files of the class they extend and of the trait it uses are the newest.
 */
$modificationTimes = [
    'AlsoInheritingTest' => 1704067200,
    'InheritingTest'     => 1704067200,
    'OwnTest'            => 1735689600,
    'BaseTestCase'       => 1767225600,
    'TestMethods'        => 1767225600,
];

foreach ($modificationTimes as $class => $modificationTime) {
    copy($fixtures . '/' . $class . '.php', $sandbox . '/' . $class . '.php');
    touch($sandbox . '/' . $class . '.php', $modificationTime);
}

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'modified-descending';
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
Test Suite Loaded (5 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (5 tests)
Test Suite Started (CLI Arguments, 5 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest::testTwo)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\AlsoInheritingTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest::testTwo)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\InheritingTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\OwnTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\OwnTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\OwnTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\OwnTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\OwnTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\ModifiedParentClass\OwnTest, 1 test)
Test Suite Finished (CLI Arguments, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
