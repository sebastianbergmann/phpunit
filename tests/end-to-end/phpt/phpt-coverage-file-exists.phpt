--TEST--
Error when code coverage file exists
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-coverage-file-exists/test.phpt');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit test runner warning:

1) File %stest.coverage exists, PHPT test %stest.phpt will not be executed

No tests executed!
