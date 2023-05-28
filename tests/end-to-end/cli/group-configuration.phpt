--TEST--
phpunit --group one
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/groups';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'one';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'PHPUnit\TestFixture\Groups\FooTest::testOne' started
Test 'PHPUnit\TestFixture\Groups\FooTest::testOne' ended


Time: %s, Memory: %s

OK (1 test, 1 assertion)
