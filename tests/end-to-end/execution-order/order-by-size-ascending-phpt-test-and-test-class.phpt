--TEST--
Order by test size ascending: PHPT test, which has no size, and test class that has a size
--FILE--
<?php declare(strict_types=1);
$sandbox = sys_get_temp_dir() . '/' . basename(__FILE__, '.phpt');

@mkdir($sandbox);

/*
 * The PHPT test is written to a directory of its own so that it is not
 * picked up as a test of PHPUnit's own test suite.
 */
file_put_contents(
    $sandbox . '/test.phpt',
    <<<'PHPT'
    --TEST--
    test
    --FILE--
    <?php declare(strict_types=1);
    print 'test';
    --EXPECT--
    test
    PHPT
);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'size-ascending';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = $sandbox . '/test.phpt';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-classes-with-different-sizes/UnitTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
$sandbox = sys_get_temp_dir() . '/' . basename(__FILE__, '.phpt');

unlink($sandbox . '/test.phpt');
rmdir($sandbox);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (CLI Arguments, 2 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest, 1 test)
Test Preparation Started (%s%etest.phpt)
Test Prepared (%s%etest.phpt)
Child Process Started (FILE section of a PHPT test)
Child Process Finished (FILE section of a PHPT test)
Test Passed (%s%etest.phpt)
Test Finished (%s%etest.phpt)
Test Suite Finished (CLI Arguments, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
