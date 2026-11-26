--TEST--
A test that fails because of an exception of a registered failure type is not considered risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/registered-failure-type/phpunit.xml';
$_SERVER['argv'][] = __DIR__ . '/../_files/registered-failure-type/RegisteredFailureTypeTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

FF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\RegisteredFailureType\RegisteredFailureTypeTest::testFailsWithoutPerformingAssertions
RuntimeException: message

%sRegisteredFailureTypeTest.php:%d

2) PHPUnit\TestFixture\RegisteredFailureType\RegisteredFailureTypeTest::testFailsAfterPerformingAssertion
RuntimeException: message

%sRegisteredFailureTypeTest.php:%d

FAILURES!
Tests: 2, Assertions: 1, Failures: 2.
