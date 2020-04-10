--TEST--
phpunit --extensions=...
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][0] = '--no-configuration';
$_SERVER['argv'][1] = '--extensions=\\PHPUnit\\Test\\Extension';
$_SERVER['argv'][2] = __DIR__ . '/../_files/ConcreteTest.php';

require __DIR__ . '/../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

PHPUnit\Test\Extension::tellAmountOfInjectedArguments: 0
PHPUnit\Test\Extension::executeBeforeFirstTest
PHPUnit\Test\Extension::executeBeforeTest: ConcreteTest::testTwo
PHPUnit\Test\Extension::executeAfterSuccessfulTest: ConcreteTest::testTwo
PHPUnit\Test\Extension::executeAfterTest: ConcreteTest::testTwo
.PHPUnit\Test\Extension::executeBeforeTest: ConcreteTest::testOne
PHPUnit\Test\Extension::executeAfterSuccessfulTest: ConcreteTest::testOne
PHPUnit\Test\Extension::executeAfterTest: ConcreteTest::testOne
.                                                                  2 / 2 (100%)PHPUnit\Test\Extension::executeAfterLastTest


Time: %s, Memory: %s

OK (2 tests, 2 assertions)
