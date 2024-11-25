--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6028
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/6028';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit.xml

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sIssue6028Test.php:20
message

Triggered by:

* PHPUnit\TestFixture\Issue6028\Issue6028Test::testOne
  %sIssue6028Test.php:18

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.
