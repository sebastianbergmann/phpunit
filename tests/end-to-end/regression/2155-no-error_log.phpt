--TEST--
https://github.com/sebastianbergmann/phpunit/issues/2155
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/2155/Issue2155Test_NoErrorLog.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Issue2155Test_No Error Log (PHPUnit\TestFixture\Issue2155\Issue2155Test_NoErrorLog)
 ✘ One
   │
   │ Test did not call error_log().
   │ Failed asserting that a string is not empty.

   │

FAILURES!
Tests: 1, Assertions: 2, Failures: 1.
