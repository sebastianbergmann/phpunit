--TEST--
E_USER_DEPRECATED classified correctly for all callee/caller combinations
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/user-deprecation';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Suite Loaded (5 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (5 tests)
Test Suite Started (%sphpunit.xml, 5 tests)
Test Suite Started (default, 5 tests)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest, 5 tests)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testFromTestCode)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testFromTestCode)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testFromTestCode, issue triggered by test code, suppressed using operator) in %s:%d
deprecation in test code
Test Passed (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testFromTestCode)
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testFromTestCode)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testSelf)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testSelf)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testSelf, issue triggered by first-party code calling into first-party code, suppressed using operator) in %s:%d
deprecation in first-party code
Test Passed (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testSelf)
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testSelf)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testDirect)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testDirect)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testDirect, issue triggered by first-party code calling into third-party code, suppressed using operator) in %s:%d
deprecation in third-party code
Test Passed (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testDirect)
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testDirect)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testThirdPartyCallsFirstParty)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testThirdPartyCallsFirstParty)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testThirdPartyCallsFirstParty, issue triggered by third-party code calling into first-party code, suppressed using operator) in %s:%d
deprecation in first-party code
Test Passed (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testThirdPartyCallsFirstParty)
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testThirdPartyCallsFirstParty)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testIndirect)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testIndirect)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testIndirect, issue triggered by third-party code calling into third-party code, suppressed using operator) in %s:%d
deprecation in third-party code B
Test Passed (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testIndirect)
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest::testIndirect)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\UserDeprecation\UserDeprecationTest, 5 tests)
Test Suite Finished (default, 5 tests)
Test Suite Finished (%sphpunit.xml, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
