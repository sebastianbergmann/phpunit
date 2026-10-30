--TEST--
The tests that triggered an issue are grouped, sorted, and limited when --triggering-tests-limit is used with the compact output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit.xml';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--triggering-tests-limit';
$_SERVER['argv'][] = '2';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

OK (8 tests, 8 assertions, 2 deprecations)

--- DEPRECATION: %sthird-party.php:%d
deprecation triggered in third-party code
Triggered 6 times (direct) by 5 tests in 2 test classes
Triggered by: PHPUnit\TestFixture\TriggeringTestsLimit\OneTest (3 tests, 4 times)
Triggered by: PHPUnit\TestFixture\TriggeringTestsLimit\TwoTest (2 tests)

--- DEPRECATION: %sFirstParty.php:%d
deprecation triggered in first-party code
Triggered 3 times (self) by 3 tests
Triggered by: PHPUnit\TestFixture\TriggeringTestsLimit\OneTest::testFour (3 data sets)
