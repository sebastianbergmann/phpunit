--TEST--
The correct deprecations are reported when only deprecations in first-party code triggered from first-party code should be reported
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/user-deprecation-report-self';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sFirstPartyClass.php:21
deprecation in first-party code

Triggered by:

* PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testOne
  %sFirstPartyClassTest.php:16

OK, but there were issues!
Tests: 2, Assertions: 2, Deprecations: 1.
