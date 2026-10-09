--TEST--
The types in the @param tags of a property and of the constructors that objects are derived from are read from the source file when opcache.save_comments is disabled
--INI--
opcache.enable_cli=1
opcache.save_comments=0
opcache.file_update_protection=0
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/DiscardedDocCommentsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

OK (4 tests, %d assertions)
