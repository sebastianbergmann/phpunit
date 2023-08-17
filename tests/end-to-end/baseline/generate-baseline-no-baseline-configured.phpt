--TEST--
phpunit --configuration ../_files/baseline/generate-baseline/phpunit.xml --generate-baseline
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--generate-baseline';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/generate-baseline-no-baseline-configured/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Generation of baseline requested using --generate-baseline, but no baseline is configured

--

1 test triggered 1 deprecation:

1) %s/Test.php:%d
deprecation

Triggered by:

* PHPUnit\TestFixture\Baseline\Test::testOne
  %s/Test.php:%d

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 1, Deprecations: 1.
