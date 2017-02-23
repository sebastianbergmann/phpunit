--TEST--
phpunit ExceptionInTest ../_files/ExceptionInTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--teamcity';
$_SERVER['argv'][3] = 'ExceptionInTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/ExceptionInTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='1' flowId='%d']

##teamcity[testSuiteStarted name='ExceptionInTest' locationHint='php_qn://%s/tests/_files/ExceptionInTest.php::\ExceptionInTest' flowId='%d']

##teamcity[testStarted name='testSomething' locationHint='php_qn://%s/tests/_files/ExceptionInTest.php::\ExceptionInTest::testSomething' flowId='%d']

##teamcity[testStdOut name='testSomething' out='|nExceptionInTest::testSomething' flowId='%d']

##teamcity[testFailed name='testSomething' message='Exception: |n' details=' /%s/tests/_files/ExceptionInTest.php:%d|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testSomething' duration='%s' flowId='%d']

##teamcity[testSuiteFinished name='ExceptionInTest' flowId='%d']


Time: %s, Memory: %s


ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
