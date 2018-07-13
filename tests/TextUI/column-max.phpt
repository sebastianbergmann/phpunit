--TEST--
Support --columns=max option.
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';

$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/Column.php';
$_SERVER['argv'][] = '--columns=max';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

....................                                              20 / 20 (100%)

Time: %s, Memory: %s

OK (20 tests, 20 assertions)
