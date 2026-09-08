--TEST--
TestDox: The tests of a test class that is skipped as a whole are shown
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/skipped-test-class';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

Executed (PHPUnit\TestFixture\TestDox\SkippedTestClass\Executed)
 ✔ One
 ↩ Two

Skipped (PHPUnit\TestFixture\TestDox\SkippedTestClass\Skipped)
 ↩ One
 ↩ Two

OK, but some tests were skipped!
Tests: 4, Assertions: 1, Skipped: 3.
