--TEST--
GH-2591: Test class process isolation with preserving global state and with loaded bootstrap.
Expected result is to have a global variable modified in first test to be the same in the second.
--FILE--
<?php declare(strict_types=1);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--bootstrap';
$_SERVER['argv'][3] = __DIR__ . '/2591/bootstrapWithBootstrap.php';
$_SERVER['argv'][4] = __DIR__ . '/2591/SeparateClassPreserveTest.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
