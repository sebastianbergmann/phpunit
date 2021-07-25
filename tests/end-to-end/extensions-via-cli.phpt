--TEST--
phpunit --extensions=...
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--extensions=\\PHPUnit\\Test\\Extension';
$_SERVER['argv'][] = __DIR__ . '/../_files/ConcreteTest.php';

require_once __DIR__ . '/../bootstrap.php';

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
