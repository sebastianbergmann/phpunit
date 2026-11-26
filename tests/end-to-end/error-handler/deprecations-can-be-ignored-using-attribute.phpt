--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5532
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/../event/_files/IgnoreDeprecationsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.D.D                                                                4 / 4 (100%)

Time: %s, Memory: %s

2 tests triggered 2 deprecations:

1) %sIgnoreDeprecationsTest.php:%d
message

2) %sIgnoreDeprecationsTest.php:%d
message

OK, but there were issues!
Tests: 4, Assertions: 6, Deprecations: 2.
