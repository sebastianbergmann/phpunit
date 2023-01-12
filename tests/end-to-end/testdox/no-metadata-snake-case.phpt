--TEST--
TestDox: Default output; Test name in snake-case notation; No TestDox metadata
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/SnakeCaseTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

Snake Case (PHPUnit\TestFixture\TestDox\SnakeCase)
 ✔ Something that works
 ✘ Something that does not work
   │
   │ Failed asserting that false is true.
   │
   │ %sSnakeCaseTest.php:%d
   │

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
