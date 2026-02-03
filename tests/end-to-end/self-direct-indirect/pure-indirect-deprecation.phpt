--TEST--
Deprecation in third-party code triggered by third-party code is classified as indirect
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/pure-indirect-deprecation';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sThirdPartyClassB.php:11
deprecation in third-party code triggered by third-party code

Triggered by:

* PHPUnit\TestFixture\SelfDirectIndirect\FirstPartyClassTest::testPureIndirect
  %sFirstPartyClassTest.php:22

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.
