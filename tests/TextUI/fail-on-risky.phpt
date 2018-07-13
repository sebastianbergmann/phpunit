--TEST--
Support --fail-on-risky.
--FILE--
<?php
$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'Risky';
$_SERVER['argv'][] = '--fail-on-risky';
$_SERVER['argv'][] = __DIR__ . '/_files/FailOn.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) FailOn::testRisky
This test did not perform any assertions

%s/FailOn.php:%s

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Risky: 1.
