--TEST--
phpunit FailureTest ../_files/FailureTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--teamcity';
$_SERVER['argv'][3] = 'FailureTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/FailureTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='13' flowId='%d']

##teamcity[testSuiteStarted name='FailureTest' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest' flowId='%d']

##teamcity[testStarted name='testAssertArrayEqualsArray' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertArrayEqualsArray' flowId='%d']

##teamcity[testFailed name='testAssertArrayEqualsArray' message='message|nFailed asserting that two arrays are equal.' details=' /%s/tests/_files/FailureTest.php:8|n ' flowId='%d']

##teamcity[testFinished name='testAssertArrayEqualsArray' duration='50' flowId='%d']

##teamcity[testStarted name='testAssertIntegerEqualsInteger' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertIntegerEqualsInteger' flowId='%d']

##teamcity[testFailed name='testAssertIntegerEqualsInteger' message='message|nFailed asserting that 2 matches expected 1.' details=' %s/tests/_files/FailureTest.php:13|n ' flowId='%d']

##teamcity[testFinished name='testAssertIntegerEqualsInteger' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertObjectEqualsObject' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertObjectEqualsObject' flowId='%d']

##teamcity[testFailed name='testAssertObjectEqualsObject' message='message|nFailed asserting that two objects are equal.' details=' %s/tests/_files/FailureTest.php:24|n ' flowId='%d']

##teamcity[testFinished name='testAssertObjectEqualsObject' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertNullEqualsString' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertNullEqualsString' flowId='%d']

##teamcity[testFailed name='testAssertNullEqualsString' message='message|nFailed asserting that |'bar|' matches expected null.' details=' %s/tests/_files/FailureTest.php:29|n ' flowId='%d']

##teamcity[testFinished name='testAssertNullEqualsString' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertStringEqualsString' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertStringEqualsString' flowId='%d']

##teamcity[testFailed name='testAssertStringEqualsString' message='message|nFailed asserting that two strings are equal.' details=' %s/tests/_files/FailureTest.php:34|n ' flowId='%d']

##teamcity[testFinished name='testAssertStringEqualsString' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertTextEqualsText' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertTextEqualsText' flowId='%d']

##teamcity[testFailed name='testAssertTextEqualsText' message='message|nFailed asserting that two strings are equal.' details=' %s/tests/_files/FailureTest.php:39|n ' flowId='%d']

##teamcity[testFinished name='testAssertTextEqualsText' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertStringMatchesFormat' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertStringMatchesFormat' flowId='%d']

##teamcity[testFailed name='testAssertStringMatchesFormat' message='message|nFailed asserting that string matches format description.|n--- Expected|n+++ Actual|n@@ @@|n-*%s*|n+**|n' details=' %s/tests/_files/FailureTest.php:44|n ' flowId='%d']

##teamcity[testFinished name='testAssertStringMatchesFormat' duration='10' flowId='%d']

##teamcity[testStarted name='testAssertNumericEqualsNumeric' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertNumericEqualsNumeric' flowId='%d']

##teamcity[testFailed name='testAssertNumericEqualsNumeric' message='message|nFailed asserting that 2 matches expected 1.' details=' %s/tests/_files/FailureTest.php:49|n ' flowId='%d']

##teamcity[testFinished name='testAssertNumericEqualsNumeric' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertTextSameText' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertTextSameText' flowId='%d']

##teamcity[testFailed name='testAssertTextSameText' message='message|nFailed asserting that two strings are identical.' details=' %s/tests/_files/FailureTest.php:54|n ' flowId='%d']

##teamcity[testFinished name='testAssertTextSameText' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertObjectSameObject' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertObjectSameObject' flowId='%d']

##teamcity[testFailed name='testAssertObjectSameObject' message='message|nFailed asserting that two variables reference the same object.' details=' %s/tests/_files/FailureTest.php:59|n ' flowId='%d']

##teamcity[testFinished name='testAssertObjectSameObject' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertObjectSameNull' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertObjectSameNull' flowId='%d']

##teamcity[testFailed name='testAssertObjectSameNull' message='message|nFailed asserting that null is identical to an object of class "stdClass".' details=' %s/tests/_files/FailureTest.php:64|n ' flowId='%d']

##teamcity[testFinished name='testAssertObjectSameNull' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertFloatSameFloat' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertFloatSameFloat' flowId='%d']

##teamcity[testFailed name='testAssertFloatSameFloat' message='message|nFailed asserting that 1.5 is identical to 1.0.' details=' %s/tests/_files/FailureTest.php:69|n ' flowId='%d']

##teamcity[testFinished name='testAssertFloatSameFloat' duration='0' flowId='%d']

##teamcity[testStarted name='testAssertStringMatchesFormatFile' locationHint='php_qn://%s/tests/_files/FailureTest.php::\FailureTest::testAssertStringMatchesFormatFile' flowId='%d']

##teamcity[testFailed name='testAssertStringMatchesFormatFile' message='Failed asserting that string matches format description.|n--- Expected|n+++ Actual|n@@ @@|n-FOO|n-|n+...BAR...|n' details=' %s/tests/_files/FailureTest.php:75|n ' flowId='%d']

##teamcity[testFinished name='testAssertStringMatchesFormatFile' duration='0' flowId='%d']

##teamcity[testSuiteFinished name='FailureTest' flowId='%d']


Time: %s, Memory: %s


FAILURES!
Tests: 13, Assertions: 14, Failures: 13.