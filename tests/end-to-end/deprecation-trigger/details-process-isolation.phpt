--TEST--
Configured deprecation triggers are filtered when displaying deprecation details in process isolation
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/details-process-isolation/phpunit.xml';
$_SERVER['argv'][] = '--display-deprecations';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 2 deprecations:

1) %sTest.php:25
deprecation triggered by method

Triggered by:

* PHPUnit\TestFixture\DeprecationTrigger\Test::testDeprecation
  %sTest.php:23

2) %sTest.php:26
deprecation triggered by function

Triggered by:

* PHPUnit\TestFixture\DeprecationTrigger\Test::testDeprecation
  %sTest.php:23

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 2.
