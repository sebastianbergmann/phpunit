--TEST--
phpunit ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/multiple-bootstraps-loads-correctly/phpunit.xml';
$_SERVER['argv'][] = '--testsuite';
$_SERVER['argv'][] = 'unit';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (%d %s, %d %s)
