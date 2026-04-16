--TEST--
--repeat does not repeat test with union return type
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/UnionReturnTypeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithUnionReturnType)
Test Prepared (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithUnionReturnType)
Test Passed (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithUnionReturnType)
Test Finished (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithUnionReturnType)
Test Preparation Started (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithVoidReturn (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithVoidReturn (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithVoidReturn (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithVoidReturn (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithVoidReturn (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithVoidReturn (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithVoidReturn (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest::testWithVoidReturn (repetition 2 of 2))
Test Suite Finished (PHPUnit\TestFixture\Repeat\UnionReturnTypeTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
