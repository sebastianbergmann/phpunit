--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5532
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/_files/IgnoreDeprecationsWithPatternTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

..D.                                                                4 / 4 (100%)

Time: %s, Memory: %s MB

1 test triggered 1 deprecation:

1) %sIgnoreDeprecationsWithPatternTest.php:%d
baz

OK, but there were issues!
Tests: 4, Assertions: 4, Deprecations: 1.
