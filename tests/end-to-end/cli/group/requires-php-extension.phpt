--TEST--
phpunit --requires-php-extension standard
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--requires-php-extension';
$_SERVER['argv'][] = 'standard';
$_SERVER['argv'][] = __DIR__ . '/../../_files/attribute-based-filtering';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (5 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (CLI Arguments, 1 test)
Test Suite Started (PHPUnit\TestFixture\AttributeBasedFiltering\RequiresPhpExtensionTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\AttributeBasedFiltering\RequiresPhpExtensionTest::testOne)
Test Prepared (PHPUnit\TestFixture\AttributeBasedFiltering\RequiresPhpExtensionTest::testOne)
Test Passed (PHPUnit\TestFixture\AttributeBasedFiltering\RequiresPhpExtensionTest::testOne)
Test Finished (PHPUnit\TestFixture\AttributeBasedFiltering\RequiresPhpExtensionTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\AttributeBasedFiltering\RequiresPhpExtensionTest, 1 test)
Test Suite Finished (CLI Arguments, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
