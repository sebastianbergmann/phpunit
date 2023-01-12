--TEST--
TestDox: Diff
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/DiffTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

Diff (PHPUnit\TestFixture\TestDox\Diff)
 ✘ Something that does not work
   │
   │ Failed asserting that two strings are equal.
   │ --- Expected
   │ +++ Actual
   │ @@ @@
   │  'foo\n
   │ +baz\n
   │  bar\n
   │ -baz\n
   │  '
   │
   │ %sDiffTest.php:%d
   │

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
