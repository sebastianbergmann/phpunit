--TEST--
phpunit --testdox --configuration=__DIR__.'/../_files/configuration.defaulttestsuite.xml' --testsuite 'First'
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--testdox';
$_SERVER['argv'][2] = '--configuration';
$_SERVER['argv'][3] = __DIR__.'/../_files/configuration.defaulttestsuite.xml';
$_SERVER['argv'][4] = '--testsuite';
$_SERVER['argv'][5] = 'First';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Dummy Foo
 ✔ Foo equals foo

Time: %s, Memory: %s

OK (1 test, 1 assertion)
