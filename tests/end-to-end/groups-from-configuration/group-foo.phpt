--TEST--
phpunit --configuration _files/phpunit.xml --list-groups
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/phpunit.xml');
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'foo';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\GroupsFromConfiguration\FooTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
