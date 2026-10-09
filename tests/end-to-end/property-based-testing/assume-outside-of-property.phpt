--TEST--
Calling assume() in a test that is not a property is reported as an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/AssumeOutsideOfPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\AssumeOutsideOfPropertyTest::testCallsAssume
PHPUnit\Framework\Exception: assume() can only be used in a property

%sAssumeOutsideOfPropertyTest.php:%d

ERRORS!
Tests: 1, Assertions: %d, Errors: 1.
