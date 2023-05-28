--TEST--
phpunit --filter FooTest
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/groups';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'FooTest';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'PHPUnit\TestFixture\Groups\FooTest::testOne' started
Test 'PHPUnit\TestFixture\Groups\FooTest::testOne' ended
Test 'PHPUnit\TestFixture\Groups\FooTest::testTwo' started
Test 'PHPUnit\TestFixture\Groups\FooTest::testTwo' ended
Test 'PHPUnit\TestFixture\Groups\FooTest::testThree' started
Test 'PHPUnit\TestFixture\Groups\FooTest::testThree' ended


Time: %s, Memory: %s

OK (3 tests, 3 assertions)
