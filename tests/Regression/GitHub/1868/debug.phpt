--TEST--
#1868: Support --debug option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/CoverageTest.php';
$_SERVER['argv'][3] = '--debug';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Starting test 'CoverageTest::test_it_should_always_return_true'.
.

Time: %s, Memory: %sMb

OK (1 test, 1 assertion)
