--TEST--
Deprecation in test code is reported when it is configured to be reported
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-in-test-code';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit.xml

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sDeprecationInTestCodeTest.php:20
message

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.
