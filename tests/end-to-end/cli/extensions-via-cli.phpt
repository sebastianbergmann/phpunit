--TEST--
phpunit --extensions=...
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--extensions=\\PHPUnit\\TestFixture\\Extension';
$_SERVER['argv'][] = __DIR__ . '/../../_files/ConcreteTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

PHPUnit\TestFixture\Extension::tellAmountOfInjectedArguments: 0
PHPUnit\TestFixture\Extension::executeBeforeFirstTest
PHPUnit\TestFixture\Extension::executeBeforeTest: ConcreteTest::testTwo
PHPUnit\TestFixture\Extension::executeAfterSuccessfulTest: ConcreteTest::testTwo
PHPUnit\TestFixture\Extension::executeAfterTest: ConcreteTest::testTwo
.PHPUnit\TestFixture\Extension::executeBeforeTest: ConcreteTest::testOne
PHPUnit\TestFixture\Extension::executeAfterSuccessfulTest: ConcreteTest::testOne
PHPUnit\TestFixture\Extension::executeAfterTest: ConcreteTest::testOne
.                                                                  2 / 2 (100%)PHPUnit\TestFixture\Extension::executeAfterLastTest


Time: %s, Memory: %s

OK (2 tests, 2 assertions)
