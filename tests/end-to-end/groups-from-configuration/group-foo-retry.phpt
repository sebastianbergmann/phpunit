--TEST--
phpunit --configuration _files/phpunit.xml --group foo --retry 2
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/phpunit.xml');
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'foo';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '2';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (2 tests)
Test Runner Execution Started (2 tests)
Test Suite Started (%sphpunit.xml, 2 tests)
Test Suite Started (default, 2 tests)
Test Suite Started (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne, up to 2 attempts)
Test Preparation Started (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne, up to 2 attempts)
Test Suite Finished (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest, 1 test)
Test Suite for Retried PHPT Started (%sfoo.phpt, up to 2 attempts)
Test Preparation Started (%sfoo.phpt)
Test Prepared (%sfoo.phpt)
Child Process Started (FILE section of a PHPT test)
Child Process Finished (FILE section of a PHPT test)
Test Passed (%sfoo.phpt)
Test Finished (%sfoo.phpt)
Test Suite for Retried PHPT Finished (%sfoo.phpt, up to 2 attempts)
Test Suite Finished (default, 2 tests)
Test Suite Finished (%sphpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
