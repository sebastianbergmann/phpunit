--TEST--
The correct deprecations are reported when deprecations triggered from third-party code should be ignored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/user-deprecation-report-self-direct';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

DD                                                                  2 / 2 (100%)

Time: %s, Memory: %s

2 tests triggered 2 deprecations:

1) %sThirdPartyClass.php:8
deprecation in third-party code

Triggered by:

* PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne
  %s:16

* PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testTwo
  %s:21

2) %sFirstPartyClass.php:21
deprecation in first-party code

Triggered by:

* PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne
  %sFirstPartyClassTest.php:16

OK, but there were issues!
Tests: 2, Assertions: 2, Deprecations: 2.
