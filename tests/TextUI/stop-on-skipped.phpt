--TEST--
Support --stop-on-skipped long option.
--FILE--
<?php
$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'Skipped';
$_SERVER['argv'][] = '--stop-on-skipped';
$_SERVER['argv'][] = __DIR__ . '/_files/StopOn.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

S

Time: %s, Memory: %s

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Skipped: 1.

