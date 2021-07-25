--TEST--
phpunit --testdox --configuration=__DIR__.'/../_files/configuration.defaulttestsuite.xml' --testsuite 'First'
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__.'/../_files/configuration.defaulttestsuite.xml';
$_SERVER['argv'][] = '--testsuite';
$_SERVER['argv'][] = 'First';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Dummy Foo (PHPUnit\TestFixture\DummyFoo)
 ✔ Foo equals foo

Time: %s, Memory: %s

OK (1 test, 1 assertion)
