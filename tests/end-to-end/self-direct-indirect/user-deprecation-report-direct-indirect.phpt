--TEST--
The correct deprecations are reported when deprecations in first-party code should be ignored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/user-deprecation-report-direct-indirect';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

DD                                                                  2 / 2 (100%)

Time: %s, Memory: %s

2 tests triggered 1 deprecation:

1) %sThirdPartyClass.php:8
deprecation in third-party code

Triggered by:

* PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne
  %sFirstPartyClassTest.php:16

* PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testTwo
  %sFirstPartyClassTest.php:21

OK, but there were issues!
Tests: 2, Assertions: 2, Deprecations: 1.
