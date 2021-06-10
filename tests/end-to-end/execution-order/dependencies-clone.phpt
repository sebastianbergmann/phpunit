--TEST--
phpunit --verbose ../../_files/ClonedDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/ClonedDependencyTest.php');

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

......                                                              6 / 6 (100%)

Time: %s, Memory: %s

OK (6 tests, 6 assertions)

