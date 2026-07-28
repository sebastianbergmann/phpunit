--TEST--
https://github.com/sebastianbergmann/phpunit/issues/2155
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/2155/Issue2155Test_NoExpectsLog.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

logged a side effect
.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Issue2155Test_No Expects Log (PHPUnit\TestFixture\Issue2155\Issue2155Test_NoExpectsLog)
 ✔ One

OK (1 test, 1 assertion)
