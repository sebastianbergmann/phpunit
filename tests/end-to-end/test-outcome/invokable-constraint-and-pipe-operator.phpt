--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6354
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare('8.5.0-dev', PHP_VERSION, '>')) {
    print 'skip: PHP 8.5 is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/InvokableConstraintAndPipeOperatorTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\InvokableConstraintAndPipeOperatorTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\InvokableConstraintAndPipeOperatorTest::testOne)
Test Prepared (PHPUnit\TestFixture\InvokableConstraintAndPipeOperatorTest::testOne)
Test Passed (PHPUnit\TestFixture\InvokableConstraintAndPipeOperatorTest::testOne)
Test Finished (PHPUnit\TestFixture\InvokableConstraintAndPipeOperatorTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\InvokableConstraintAndPipeOperatorTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
