--TEST--
phpunit --process-isolation runs the tests of a test file whose path contains a single quote, with a bootstrap script whose path contains a single quote, in separate processes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . "/_files/path-with-'-quote/bootstrap.php";
$_SERVER['argv'][] = __DIR__ . "/_files/path-with-'-quote/PathWithQuoteTest.php";

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
