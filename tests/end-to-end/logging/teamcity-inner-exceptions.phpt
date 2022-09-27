--TEST--
phpunit --teamcity ../../_files/ExceptionStackTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--teamcity';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/ExceptionStackTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='2' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\TestFixture\ExceptionStackTest' locationHint='php_qn://%s%etests%e_files%eExceptionStackTest.php::\PHPUnit\TestFixture\ExceptionStackTest' flowId='%d']

##teamcity[testStarted name='testPrintingChildException' locationHint='php_qn://%s%etests%e_files%eExceptionStackTest.php::\PHPUnit\TestFixture\ExceptionStackTest::testPrintingChildException' flowId='%d']

##teamcity[testFailed name='testPrintingChildException' message='Child exception|nmessage|nFailed asserting that two arrays are equal.|n--- Expected|n+++ Actual|n@@ @@|n Array (|n-    0 => 1|n+    0 => 2|n )|n' details=' %s_files%eExceptionStackTest.php:%d|n |n Caused by|n message|n Failed asserting that two arrays are equal.|n --- Expected|n +++ Actual|n @@ @@|n  Array (|n -    0 => 1|n +    0 => 2|n  )|n |n %s_files%eExceptionStackTest.php:%d|n ' duration='%d' flowId='%d']

##teamcity[testFinished name='testPrintingChildException' duration='%d' flowId='%d']

##teamcity[testStarted name='testNestedExceptions' locationHint='php_qn://%s%etests%e_files%eExceptionStackTest.php::\PHPUnit\TestFixture\ExceptionStackTest::testNestedExceptions' flowId='%d']

##teamcity[testFailed name='testNestedExceptions' message='Exception : One' details=' %s%etests%e_files%eExceptionStackTest.php:%d|n |n Caused by|n InvalidArgumentException: Two|n |n %s%etests%e_files%eExceptionStackTest.php:%d|n |n Caused by|n Exception: Three|n |n %s%etests%e_files%eExceptionStackTest.php:%d|n ' duration='%d' flowId='%d']

##teamcity[testFinished name='testNestedExceptions' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='PHPUnit\TestFixture\ExceptionStackTest' flowId='%d']


Time: %s, Memory: %s


ERRORS!
Tests: 2, Assertions: 1, Errors: 2.
