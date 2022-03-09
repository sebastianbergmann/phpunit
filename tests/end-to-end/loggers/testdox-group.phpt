--TEST--
phpunit --testdox-text php://stdout --testdox-group one ../../_files/TestDoxGroupTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox-text';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = '--testdox-group';
$_SERVER['argv'][] = 'one';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/TestDoxGroupTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Dox Group (PHPUnit\TestFixture\DoxGroup)
..                                                                  2 / 2 (100%) [x] One



Time: %s, Memory: %s

OK (2 tests, 2 assertions)
