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

.D.DD                                                               5 / 5 (100%)

Time: %s, Memory: %s

3 tests triggered 3 deprecations:

1) %sTriggersDeprecationInDataProviderTest.php:25
some deprecation

Triggered by:

* PHPUnit\TestFixture\Issue6279\TriggersDeprecationInDataProviderTest::method2#0
  %sTriggersDeprecationInDataProviderTest.php:38

* PHPUnit\TestFixture\Issue6279\TriggersDeprecationInDataProviderTest::method4#0
  %sTriggersDeprecationInDataProviderTest.php:51

2) %sTriggersDeprecationInDataProviderTest.php:65
first

3) %sTriggersDeprecationInDataProviderTest.php:66
second

OK, but there were issues!
Tests: 5, Assertions: 5, Deprecations: 3.
