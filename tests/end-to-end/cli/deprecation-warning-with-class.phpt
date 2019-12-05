--TEST--
phpunit DummyFooTest ../../_files/DummyFooTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'DummyFooTest';
$_SERVER['argv'][3] = __DIR__ . '/../../_files/DummyFooTest.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Warning:       Invocation with class name is deprecated

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
