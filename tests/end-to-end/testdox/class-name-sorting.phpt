--TEST--
TestDox: Test classes are sorted by their prettified names using case-insensitive natural ordering
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/class-name-sorting';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

apple
 ✔ One

Banana
 ✔ One

Item 9
 ✔ One

item 10
 ✔ One

OK (4 tests, 4 assertions)
