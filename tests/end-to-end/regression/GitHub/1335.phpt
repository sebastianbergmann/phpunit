--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1335
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/1335/bootstrap1335.php';
$_SERVER['argv'][] = __DIR__ . '/1335/Issue1335Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

............                                                      12 / 12 (100%)

Time: %s, Memory: %s

OK (12 tests, 12 assertions)
