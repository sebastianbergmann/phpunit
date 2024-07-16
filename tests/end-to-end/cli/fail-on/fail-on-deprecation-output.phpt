--TEST--
Details for deprecations are displayed when --fail-on-deprecation is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--fail-on-deprecation';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/DeprecationTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

D.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sDeprecationTest.php:%d
message

Triggered by:

* PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testOne
  %sDeprecationTest.php:%d

OK, but there were issues!
Tests: 2, Assertions: 2, Deprecations: 1.
