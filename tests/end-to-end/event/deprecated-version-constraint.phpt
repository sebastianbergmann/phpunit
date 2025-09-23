--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6355
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DeprecatedVersionConstraintTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered Deprecation (Test method PHPUnit\TestFixture\Event\DeprecatedVersionConstraintTest::testOne has attribute with version constraint string argument without explicit version comparison operator ("100"))
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\DeprecatedVersionConstraintTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\DeprecatedVersionConstraintTest::testOne)
Test Skipped (PHPUnit\TestFixture\Event\DeprecatedVersionConstraintTest::testOne)
PHP 100 is required.
Test Suite Finished (PHPUnit\TestFixture\Event\DeprecatedVersionConstraintTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
