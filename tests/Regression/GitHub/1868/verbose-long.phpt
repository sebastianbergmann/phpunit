--TEST--
#1868: Support --verbose long option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--verbose';
$_SERVER['argv'][3] = __DIR__ . '/options/VerboseTest.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:	%s with Xdebug %s

I

Time: %s ms, Memory: %sMb

There was 1 incomplete test:

1) VerboseTest::testVerbose
incompleted test for verbose assertion

%s/tests/Regression/GitHub/1868/options/VerboseTest.php:6

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Incomplete: 1.

