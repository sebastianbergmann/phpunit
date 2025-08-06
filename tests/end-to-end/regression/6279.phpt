--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6279
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/6279/TriggersDeprecationInDataProviderTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.D.                                                                 3 / 3 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %s/end-to-end/regression/6279/TriggersDeprecationInDataProviderTest.php:24
some deprecation

OK, but there were issues!
Tests: 3, Assertions: 3, Deprecations: 1.
