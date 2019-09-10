--TEST--
phpunit --configuration _files/hooks.xml _files/HookTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--configuration',
    \realpath(__DIR__ . '/_files/hooks.xml'),
    \realpath(__DIR__ . '/_files/HookTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

PHPUnit\Test\Extension::tellAmountOfInjectedArguments: %d
PHPUnit\Test\Extension::executeBeforeFirstTest
PHPUnit\Test\Extension::executeBeforeTest: PHPUnit\Test\HookTest::testSuccess
PHPUnit\Test\Extension::executeAfterSuccessfulTest: PHPUnit\Test\HookTest::testSuccess
PHPUnit\Test\Extension::executeAfterTest: PHPUnit\Test\HookTest::testSuccess
PHPUnit\Test\Extension::executeBeforeTest: PHPUnit\Test\HookTest::testFailure
PHPUnit\Test\Extension::executeAfterTestFailure: PHPUnit\Test\HookTest::testFailure: Failed asserting that false is true.
PHPUnit\Test\Extension::executeAfterTest: PHPUnit\Test\HookTest::testFailure
PHPUnit\Test\Extension::executeBeforeTest: PHPUnit\Test\HookTest::testError
PHPUnit\Test\Extension::executeAfterTestError: PHPUnit\Test\HookTest::testError: message
PHPUnit\Test\Extension::executeAfterTest: PHPUnit\Test\HookTest::testError
PHPUnit\Test\Extension::executeBeforeTest: PHPUnit\Test\HookTest::testIncomplete
PHPUnit\Test\Extension::executeAfterIncompleteTest: PHPUnit\Test\HookTest::testIncomplete: message
PHPUnit\Test\Extension::executeAfterTest: PHPUnit\Test\HookTest::testIncomplete
PHPUnit\Test\Extension::executeBeforeTest: PHPUnit\Test\HookTest::testRisky
PHPUnit\Test\Extension::executeAfterRiskyTest: PHPUnit\Test\HookTest::testRisky: message
PHPUnit\Test\Extension::executeAfterTest: PHPUnit\Test\HookTest::testRisky
PHPUnit\Test\Extension::executeBeforeTest: PHPUnit\Test\HookTest::testSkipped
PHPUnit\Test\Extension::executeAfterSkippedTest: PHPUnit\Test\HookTest::testSkipped: message
PHPUnit\Test\Extension::executeAfterTest: PHPUnit\Test\HookTest::testSkipped
PHPUnit\Test\Extension::executeBeforeTest: PHPUnit\Test\HookTest::testWarning
PHPUnit\Test\Extension::executeAfterTestWarning: PHPUnit\Test\HookTest::testWarning: message
PHPUnit\Test\Extension::executeAfterTest: PHPUnit\Test\HookTest::testWarning
PHPUnit\Test\Extension::executeAfterLastTest
