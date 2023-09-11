--TEST--
phpunit --configuration ../_files/baseline/use-baseline/phpunit.xml --ignore-baseline
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/use-baseline/phpunit.xml';
$_SERVER['argv'][] = '--ignore-baseline';
$_SERVER['argv'][] = '--display-deprecations';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sTest.php:%d
deprecation

Triggered by:

* PHPUnit\TestFixture\Baseline\Test::testOne
  %sTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.
