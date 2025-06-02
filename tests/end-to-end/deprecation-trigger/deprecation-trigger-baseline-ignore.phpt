--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_USER_DEPRECATED
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-trigger-baseline-ignore';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (%s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\BaselineIgnoreDeprecation\FirstPartyClassTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\BaselineIgnoreDeprecation\FirstPartyClassTest::testOne)
Test Prepared (PHPUnit\TestFixture\BaselineIgnoreDeprecation\FirstPartyClassTest::testOne)
Test Triggered Deprecation (PHPUnit\TestFixture\BaselineIgnoreDeprecation\FirstPartyClassTest::testOne, issue triggered by third-party code, suppressed using operator, ignored by baseline) in %s:%d
deprecation in third-party code
Test Passed (PHPUnit\TestFixture\BaselineIgnoreDeprecation\FirstPartyClassTest::testOne)
Test Finished (PHPUnit\TestFixture\BaselineIgnoreDeprecation\FirstPartyClassTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\BaselineIgnoreDeprecation\FirstPartyClassTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
