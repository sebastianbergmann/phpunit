--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3093
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--order-by=reverse';
$_SERVER['argv'][] = __DIR__ . '/Issue3093Test.php';

require __DIR__ . '/../../../../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
