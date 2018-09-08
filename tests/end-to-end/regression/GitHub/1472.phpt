--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1472
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue1472Test';
$_SERVER['argv'][3] = __DIR__ . '/1472/Issue1472Test.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 4 assertions)
