--TEST--
TestDox: A test that has more than one outcome is shown with its most important outcome
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/MoreThanOneOutcomeTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

More Than One Outcome (PHPUnit\TestFixture\TestDox\MoreThanOneOutcome)
 ✘ Error and failure in tear down
   │
   │ Exception: exception in test
   │
   │ %sMoreThanOneOutcomeTest.php:%d
   │

ERRORS!
Tests: 1, Assertions: 1, Errors: 1, Failures: 1.
