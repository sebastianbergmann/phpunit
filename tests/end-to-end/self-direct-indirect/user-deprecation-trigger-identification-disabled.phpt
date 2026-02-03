--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_USER_DEPRECATED and trigger identification is disabled
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/user-deprecation-trigger-identification-disabled';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (%sphpunit.xml, 2 tests)
Test Suite Started (default, 2 tests)
Test Suite Started (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne)
Test Prepared (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne)
Test Triggered Deprecation (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne, unknown if issue was triggered in first-party code or third-party code, suppressed using operator) in %s:%d
deprecation in third-party code
Test Triggered Deprecation (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne, unknown if issue was triggered in first-party code or third-party code, suppressed using operator) in %s:%d
deprecation in first-party code
Test Passed (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne)
Test Finished (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testTwo)
Test Prepared (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testTwo)
Test Triggered Deprecation (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testTwo, unknown if issue was triggered in first-party code or third-party code, suppressed using operator) in %s:%d
deprecation in third-party code
Test Triggered Deprecation (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testTwo, unknown if issue was triggered in first-party code or third-party code, suppressed using operator) in %s:%d
deprecation in first-party code
Test Passed (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testTwo)
Test Finished (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest, 2 tests)
Test Suite Finished (default, 2 tests)
Test Suite Finished (%sphpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
