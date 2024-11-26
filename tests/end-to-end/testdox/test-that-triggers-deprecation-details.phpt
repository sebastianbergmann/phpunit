--TEST--
TestDox: Test triggers deprecation and --display-deprecations is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/_files/DeprecationTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Deprecation (PHPUnit\TestFixture\TestDox\Deprecation)
 ⚠ Deprecation

1 test triggered 1 deprecation:

1) %sDeprecationTest.php:20
deprecation

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.
