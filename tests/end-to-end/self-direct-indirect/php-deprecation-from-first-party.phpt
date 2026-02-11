--TEST--
PHP deprecation (E_DEPRECATED) triggered in first-party code is classified as direct (first-party calling into third-party)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/php-deprecation-from-first-party';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne)
Test Prepared (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne)
Test Triggered PHP Deprecation (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne, issue triggered by first-party code calling into third-party code, suppressed using operator) in %sFirstPartyClass.php:%d
strlen(): Passing null to parameter #1 ($string) of type string is deprecated
Test Passed (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne)
Test Finished (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
