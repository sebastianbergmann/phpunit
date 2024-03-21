--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5760
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/5760/Issue5760Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Issue5760 (PHPUnit\TestFixture\Issue5760\Issue5760)
 ✘ One
   │
   │ Exception: message
   │
   │ %s:19
   │

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
