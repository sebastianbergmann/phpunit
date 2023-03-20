--TEST--
phpunit --teamcity ../../basic/unit/
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--teamcity';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/unit/';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


##teamcity[testCount count='19' flowId='%d']

##teamcity[testSuiteStarted name='%stests/end-to-end/_files/basic/unit' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\SelfTest\Basic\SetUpBeforeClassTest' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/SetUpBeforeClassTest.php::\PHPUnit\SelfTest\Basic\SetUpBeforeClassTest' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\SelfTest\Basic\SetUpTest' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/SetUpTest.php::\PHPUnit\SelfTest\Basic\SetUpTest' flowId='%d']

##teamcity[testFailed name='testOneWithSetUpException' message='RuntimeException: throw exception in setUp' details='%stests/end-to-end/_files/basic/unit/SetUpTest.php:%d|n' duration='%d' flowId='%d']

##teamcity[testFailed name='testTwoWithSetUpException' message='RuntimeException: throw exception in setUp' details='%stests/end-to-end/_files/basic/unit/SetUpTest.php:%d|n' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='PHPUnit\SelfTest\Basic\SetUpTest' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\SelfTest\Basic\StatusTest' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest' flowId='%d']

##teamcity[testStarted name='testSuccess' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testSuccess' flowId='%d']

##teamcity[testFinished name='testSuccess' duration='%d' flowId='%d']

##teamcity[testStarted name='testFailure' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testFailure' flowId='%d']

##teamcity[testFailed name='testFailure' message='Failed asserting that false is true.' details='%stests/end-to-end/_files/basic/unit/StatusTest.php:%d|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testFailure' duration='%d' flowId='%d']

##teamcity[testStarted name='testError' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testError' flowId='%d']

##teamcity[testFailed name='testError' message='RuntimeException' details='%stests/end-to-end/_files/basic/unit/StatusTest.php:%d|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testError' duration='%d' flowId='%d']

##teamcity[testStarted name='testIncomplete' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testIncomplete' flowId='%d']

##teamcity[testIgnored name='testIncomplete' message='' details='%stests/end-to-end/_files/basic/unit/StatusTest.php:%d|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testIncomplete' duration='%d' flowId='%d']

##teamcity[testStarted name='testSkipped' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testSkipped' flowId='%d']

##teamcity[testIgnored name='testSkipped' message='' duration='%d' flowId='%d']

##teamcity[testFinished name='testSkipped' duration='%d' flowId='%d']

##teamcity[testStarted name='testRisky' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testRisky' flowId='%d']

##teamcity[testFailed name='testRisky' message='This test did not perform any assertions' details='' duration='%d' flowId='%d']

##teamcity[testFinished name='testRisky' duration='%d' flowId='%d']

##teamcity[testStarted name='testSuccessWithMessage' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage' flowId='%d']

##teamcity[testFinished name='testSuccessWithMessage' duration='%d' flowId='%d']

##teamcity[testStarted name='testFailureWithMessage' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage' flowId='%d']

##teamcity[testFailed name='testFailureWithMessage' message='failure with custom message|nFailed asserting that false is true.' details='%stests/end-to-end/_files/basic/unit/StatusTest.php:%d|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testFailureWithMessage' duration='%d' flowId='%d']

##teamcity[testStarted name='testErrorWithMessage' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage' flowId='%d']

##teamcity[testFailed name='testErrorWithMessage' message='RuntimeException: error with custom message' details='%stests/end-to-end/_files/basic/unit/StatusTest.php:%d|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testErrorWithMessage' duration='%d' flowId='%d']

##teamcity[testStarted name='testIncompleteWithMessage' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage' flowId='%d']

##teamcity[testIgnored name='testIncompleteWithMessage' message='incomplete with custom message' details='%stests/end-to-end/_files/basic/unit/StatusTest.php:%d|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testIncompleteWithMessage' duration='%d' flowId='%d']

##teamcity[testIgnored name='testSkippedByMetadata' message='PHP > 9000 is required.' duration='%d' flowId='%d']

##teamcity[testStarted name='testSkippedWithMessage' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage' flowId='%d']

##teamcity[testIgnored name='testSkippedWithMessage' message='skipped with custom message' duration='%d' flowId='%d']

##teamcity[testFinished name='testSkippedWithMessage' duration='%d' flowId='%d']

##teamcity[testStarted name='testRiskyWithMessage' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/StatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage' flowId='%d']

##teamcity[testFailed name='testRiskyWithMessage' message='This test did not perform any assertions' details='' duration='%d' flowId='%d']

##teamcity[testFinished name='testRiskyWithMessage' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='PHPUnit\SelfTest\Basic\StatusTest' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\SelfTest\Basic\TearDownAfterClassTest' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/TearDownAfterClassTest.php::\PHPUnit\SelfTest\Basic\TearDownAfterClassTest' flowId='%d']

##teamcity[testStarted name='testOne' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/TearDownAfterClassTest.php::\PHPUnit\SelfTest\Basic\TearDownAfterClassTest::testOne' flowId='%d']

##teamcity[testFinished name='testOne' duration='%d' flowId='%d']

##teamcity[testStarted name='testTwo' locationHint='php_qn://%stests/end-to-end/_files/basic/unit/TearDownAfterClassTest.php::\PHPUnit\SelfTest\Basic\TearDownAfterClassTest::testTwo' flowId='%d']

##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='PHPUnit\SelfTest\Basic\TearDownAfterClassTest' flowId='%d']

##teamcity[testSuiteFinished name='%stests/end-to-end/_files/basic/unit' flowId='%d']
Time: %s, Memory: %s

There were 5 errors:

1) PHPUnit\SelfTest\Basic\SetUpBeforeClassTest
Exception: forcing an Exception in setUpBeforeClass()

%s/SetUpBeforeClassTest.php:%d

2) PHPUnit\SelfTest\Basic\SetUpTest::testOneWithSetUpException
RuntimeException: throw exception in setUp

%s/SetUpTest.php:%d

3) PHPUnit\SelfTest\Basic\SetUpTest::testTwoWithSetUpException
RuntimeException: throw exception in setUp

%s/SetUpTest.php:%d

4) PHPUnit\SelfTest\Basic\StatusTest::testError
RuntimeException: 

%s/StatusTest.php:%d

5) PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage
RuntimeException: error with custom message

%s/StatusTest.php:%d

--

There were 2 failures:

1) PHPUnit\SelfTest\Basic\StatusTest::testFailure
Failed asserting that false is true.

%s/StatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage
failure with custom message
Failed asserting that false is true.

%s/StatusTest.php:%d

--

There were 2 risky tests:

1) PHPUnit\SelfTest\Basic\StatusTest::testRisky
This test did not perform any assertions

%s/StatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage
This test did not perform any assertions

%s/StatusTest.php:%d

ERRORS!
Tests: 18, Assertions: 6, Errors: 5, Failures: 2, Skipped: 3, Incomplete: 2, Risky: 2.
