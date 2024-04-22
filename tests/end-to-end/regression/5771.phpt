--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5771
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/5771/Issue5771Test.php';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = tempnam(sys_get_temp_dir(), __FILE__);

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Issue5771\Issue5771Test::test
Test was run in child process and ended unexpectedly

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
