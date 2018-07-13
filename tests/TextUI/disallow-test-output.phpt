--TEST--
Support --disallow-test-output with specified file
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';

$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--disallow-test-output';
$_SERVER['argv'][] = __DIR__ . '/_files/Output.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R                                                                   1 / 1 (100%)Outputed string

Time: %s ms, Memory: %sMB

There was 1 risky test:

1) Output::testProduceOutput
This test printed output: Outputed string

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 1, Risky: 1.
