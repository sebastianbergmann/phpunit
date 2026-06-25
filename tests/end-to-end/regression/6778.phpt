--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6778
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/6778/phpunit.xml';
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

1) %sDeprecatedClass.php:%d
triggered while autoloading a class during requirement checking

Triggered by:

* PHPUnit\TestFixture\Issue6778\RequiresMethodTest::testSomething
  %sRequiresMethodTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.
