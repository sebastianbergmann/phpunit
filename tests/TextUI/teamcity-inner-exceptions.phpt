--TEST--
phpunit --teamcity ExceptionStackTest ../_files/ExceptionStackTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--teamcity';
$_SERVER['argv'][3] = 'ExceptionStackTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/ExceptionStackTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='2' flowId='%d']

##teamcity[testSuiteStarted name='ExceptionStackTest' locationHint='php_qn://%s/tests/_files/ExceptionStackTest.php::\ExceptionStackTest' flowId='%d']

##teamcity[testStarted name='testPrintingChildException' locationHint='php_qn://%s/tests/_files/ExceptionStackTest.php::\ExceptionStackTest::testPrintingChildException' flowId='%d']

##teamcity[testFailed name='testPrintingChildException' message='Child exception|nmessage|nFailed asserting that two arrays are equal.|n--- Expected|n+++ Actual|n@@ @@|n Array (|n-    0 => 1|n+    0 => 2|n )|n' details=' %s/tests/_files/ExceptionStackTest.php:14|n |n Caused by|n message|n Failed asserting that two arrays are equal.|n --- Expected|n +++ Actual|n @@ @@|n  Array (|n -    0 => 1|n +    0 => 2|n  )|n |n %s/tests/_files/ExceptionStackTest.php:10|n ' flowId='%d']

##teamcity[testFinished name='testPrintingChildException' duration='%d' flowId='%d']

##teamcity[testStarted name='testNestedExceptions' locationHint='php_qn://%s/tests/_files/ExceptionStackTest.php::\ExceptionStackTest::testNestedExceptions' flowId='%d']

##teamcity[testFailed name='testNestedExceptions' message='One' details=' %s/tests/_files/ExceptionStackTest.php:22|n |n Caused by|n InvalidArgumentException: Two|n |n %s/tests/_files/ExceptionStackTest.php:21|n |n Caused by|n Exception: Three|n |n %s/tests/_files/ExceptionStackTest.php:20|n ' flowId='%d']

##teamcity[testFinished name='testNestedExceptions' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='ExceptionStackTest' flowId='%d']


Time: %s, Memory: %s


ERRORS!
Tests: 2, Assertions: 1, Errors: 2.