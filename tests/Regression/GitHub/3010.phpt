--TEST--
GH-3010: undefined index cause the test to fail
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue3010Test';
$_SERVER['argv'][3] = __DIR__ . '/3010/Issue3010Test.php';

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) Issue3010Test::testOne
PHPUnit\Framework\Exception: Undefined index: index in %s%e3010%eUndefinedIndex.php; line %d
%A
ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
