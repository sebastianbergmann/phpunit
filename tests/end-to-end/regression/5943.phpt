--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5943
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/5943';
$_SERVER['argv'][] = '--list-groups';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test groups:
 - bar (1 test)
 - barbara (1 test)
 - baz (1 test)
 - foo (1 test)
