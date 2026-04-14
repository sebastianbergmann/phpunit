--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6465
--FILE--
<?php declare(strict_types=1);
$_SERVER['REQUEST_TIME_FLOAT'] = 1.0;
$_SERVER['REQUEST_TIME']       = 1;
$_SERVER['SCRIPT_FILENAME']    = '/fake/parent/script.php';

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6465/Issue6465Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 3 assertions)
