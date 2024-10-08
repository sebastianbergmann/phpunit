--TEST--
The right events are emitted in the right order when a PHPUnit extension from a PHAR is loaded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/phar-extension/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Extension Loaded from PHAR (phpunit/phpunit-test-extension 1.0.0)
Extension Bootstrapped (PHPUnit\TestFixture\MyExtension\MyExtensionBootstrap)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%s%etests%eend-to-end%e_files%ephar-extension%ephpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\MyExtension\Test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\MyExtension\Test::testOne)
Test Prepared (PHPUnit\TestFixture\Event\MyExtension\Test::testOne)
Test Passed (PHPUnit\TestFixture\Event\MyExtension\Test::testOne)
Test Finished (PHPUnit\TestFixture\Event\MyExtension\Test::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\MyExtension\Test, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%s%etests%eend-to-end%e_files%ephar-extension%ephpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
