--TEST--
phpunit --compact ../_files/PhpunitDeprecationAndNoticeTest (default display flags)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../_files/PhpunitDeprecationAndNoticeTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

OK (3 tests, 3 assertions, 1 PHPUnit deprecation, 1 PHPUnit notice)
