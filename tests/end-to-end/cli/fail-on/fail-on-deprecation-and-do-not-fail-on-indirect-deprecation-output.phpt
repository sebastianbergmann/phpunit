--TEST--
Details for deprecations that do not cause the test run to fail are displayed when --fail-on-deprecation and --do-not-fail-on-indirect-deprecation are used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-trigger/phpunit.xml';
$_SERVER['argv'][] = '--fail-on-deprecation';
$_SERVER['argv'][] = '--do-not-fail-on-indirect-deprecation';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-trigger/tests/IndirectDeprecationTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sthird-party.php:%d
deprecation triggered in third-party code

Triggered by:

* PHPUnit\TestFixture\FailOnDeprecationTrigger\IndirectDeprecationTest::testOne
  %sIndirectDeprecationTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.
