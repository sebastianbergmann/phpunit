--TEST--
A custom error handler registered in the test suite's bootstrap script using set_error_handler() is not overwritten by PHPUnit by default
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/custom-error-handler-registered-in-bootstrap-is-not-overwritten-by-phpunit/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sbootstrap.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\ErrorHandlerIsNotOverwritten\ExampleTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerIsNotOverwritten\ExampleTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerIsNotOverwritten\ExampleTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerIsNotOverwritten\ExampleTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerIsNotOverwritten\ExampleTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\ErrorHandlerIsNotOverwritten\ExampleTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
