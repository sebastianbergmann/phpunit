--TEST--
phpunit --list-suites --configuration=__DIR__.'/../_files/configuration.suites.xml'
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--list-suites';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__.'/../_files/configuration.suites.xml';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test suite(s):
 - Suite One
 - Suite Two
