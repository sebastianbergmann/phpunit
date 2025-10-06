--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6356
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidVersionConstraintTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered Warning (Test method PHPUnit\TestFixture\Event\InvalidVersionConstraintTest::testOne has attribute with version constraint string argument without explicit version comparison operator ("100"), version constraint is ignored)
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\InvalidVersionConstraintTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\InvalidVersionConstraintTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\InvalidVersionConstraintTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\InvalidVersionConstraintTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\InvalidVersionConstraintTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\InvalidVersionConstraintTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
