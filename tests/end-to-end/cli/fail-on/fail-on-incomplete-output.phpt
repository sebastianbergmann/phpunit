--TEST--
Details for incomplete tests are displayed when --fail-on-incomplete is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--fail-on-incomplete';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/IncompleteTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

I.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 incomplete test:

1) PHPUnit\TestFixture\TestRunnerStopping\IncompleteTest::testOne
message

%sIncompleteTest.php:%d

OK, but there were issues!
Tests: 2, Assertions: 1, Incomplete: 1.
