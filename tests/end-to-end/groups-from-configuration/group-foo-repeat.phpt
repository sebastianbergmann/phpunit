--TEST--
phpunit --configuration _files/phpunit.xml --group foo --repeat 2
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/phpunit.xml');
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'foo';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (8 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (4 tests)
Test Runner Execution Started (4 tests)
Test Suite Started (%sphpunit.xml, 4 tests)
Test Suite Started (default, 4 tests)
Test Suite Started (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest, 2 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne (repetition 2 of 2))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne, 2 repetitions)
Test Suite Finished (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest, 2 tests)
Test Suite for Repeated PHPT Started (%sfoo.phpt, 2 repetitions)
Test Preparation Started (%sfoo.phpt (repetition 1 of 2))
Test Prepared (%sfoo.phpt (repetition 1 of 2))
Child Process Started (FILE section of a PHPT test)
Child Process Finished (FILE section of a PHPT test)
Test Passed (%sfoo.phpt (repetition 1 of 2))
Test Finished (%sfoo.phpt (repetition 1 of 2))
Test Preparation Started (%sfoo.phpt (repetition 2 of 2))
Test Prepared (%sfoo.phpt (repetition 2 of 2))
Child Process Started (FILE section of a PHPT test)
Child Process Finished (FILE section of a PHPT test)
Test Passed (%sfoo.phpt (repetition 2 of 2))
Test Finished (%sfoo.phpt (repetition 2 of 2))
Test Suite for Repeated PHPT Finished (%sfoo.phpt, 2 repetitions)
Test Suite Finished (default, 4 tests)
Test Suite Finished (%sphpunit.xml, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
