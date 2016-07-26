--TEST--
GH-1553: Tests That Fail to Close Their Output Buffers Have Any Output Swallowed.
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/1553/Issue1553Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Rhere.there

Time: %s, Memory: %sMb

OK, but incomplete, skipped, or risky tests!
Tests: 2, Assertions: 0, Risky: 1.
