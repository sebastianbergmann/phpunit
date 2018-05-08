--TEST--
GH-2724: Missing initialization of setRunClassInSeparateProcess() for tests without data providers
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'SeparateClassRunMethodInNewProcessTest';
$_SERVER['argv'][3] = __DIR__ . '/2724/SeparateClassRunMethodInNewProcessTest.php';

require __DIR__ . '/../../bootstrap.php';

\file_put_contents(__DIR__ . '/2724/parent_process_id.txt', \getmypid());

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 3 assertions)
