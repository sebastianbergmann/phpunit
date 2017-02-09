--TEST--
#2273: --testsuite <pattern> is not an actual pattern filter
--FILE--
<?php
$_SERVER['argv'][1] = '--configuration';
$_SERVER['argv'][2] = __DIR__ . '/2273/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

%s
%s

....                                                                  2 / 2 (100%)

%s

OK (2 tests, 2 assertions)
