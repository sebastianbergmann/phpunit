--TEST--
phpunit --configuration _files/hooks.xml _files/HookTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/hooks.xml');
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/HookTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit\TestFixture\Extension::tellAmountOfInjectedArguments: %d
PHPUnit\TestFixture\Extension::executeBeforeFirstTest
PHPUnit\TestFixture\Extension::executeBeforeTest: PHPUnit\TestFixture\HookTest::testSuccess
PHPUnit\TestFixture\Extension::executeAfterSuccessfulTest: PHPUnit\TestFixture\HookTest::testSuccess
PHPUnit\TestFixture\Extension::executeAfterTest: PHPUnit\TestFixture\HookTest::testSuccess
PHPUnit\TestFixture\Extension::executeBeforeTest: PHPUnit\TestFixture\HookTest::testFailure
PHPUnit\TestFixture\Extension::executeAfterTestFailure: PHPUnit\TestFixture\HookTest::testFailure: Failed asserting that false is true.
PHPUnit\TestFixture\Extension::executeAfterTest: PHPUnit\TestFixture\HookTest::testFailure
PHPUnit\TestFixture\Extension::executeBeforeTest: PHPUnit\TestFixture\HookTest::testError
PHPUnit\TestFixture\Extension::executeAfterTestError: PHPUnit\TestFixture\HookTest::testError: message
PHPUnit\TestFixture\Extension::executeAfterTest: PHPUnit\TestFixture\HookTest::testError
PHPUnit\TestFixture\Extension::executeBeforeTest: PHPUnit\TestFixture\HookTest::testIncomplete
PHPUnit\TestFixture\Extension::executeAfterIncompleteTest: PHPUnit\TestFixture\HookTest::testIncomplete: message
PHPUnit\TestFixture\Extension::executeAfterTest: PHPUnit\TestFixture\HookTest::testIncomplete
PHPUnit\TestFixture\Extension::executeBeforeTest: PHPUnit\TestFixture\HookTest::testRisky
PHPUnit\TestFixture\Extension::executeAfterRiskyTest: PHPUnit\TestFixture\HookTest::testRisky: message
PHPUnit\TestFixture\Extension::executeAfterTest: PHPUnit\TestFixture\HookTest::testRisky
PHPUnit\TestFixture\Extension::executeBeforeTest: PHPUnit\TestFixture\HookTest::testSkipped
PHPUnit\TestFixture\Extension::executeAfterSkippedTest: PHPUnit\TestFixture\HookTest::testSkipped: message
PHPUnit\TestFixture\Extension::executeAfterTest: PHPUnit\TestFixture\HookTest::testSkipped
PHPUnit\TestFixture\Extension::executeBeforeTest: PHPUnit\TestFixture\HookTest::testWarning
PHPUnit\TestFixture\Extension::executeAfterTestWarning: PHPUnit\TestFixture\HookTest::testWarning: message
PHPUnit\TestFixture\Extension::executeAfterTest: PHPUnit\TestFixture\HookTest::testWarning
PHPUnit\TestFixture\Extension::executeAfterLastTest
