--TEST--
TestDox: A test that is skipped or marked incomplete before it started is shown
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/test-that-never-started';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

Dependency (PHPUnit\TestFixture\TestDox\TestThatNeverStarted\Dependency)
 ↩ Producer
 ↩ Consumer

Requirement (PHPUnit\TestFixture\TestDox\TestThatNeverStarted\Requirement)
 ↩ One

Set Up (PHPUnit\TestFixture\TestDox\TestThatNeverStarted\SetUp)
 ∅ One
   │
   │ setUp() decided so
   │
   │ %sSetUpTest.php:18
   │

OK, but there were issues!
Tests: 4, Assertions: 0, Skipped: 3, Incomplete: 1.
