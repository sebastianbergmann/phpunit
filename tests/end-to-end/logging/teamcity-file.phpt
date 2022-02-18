--TEST--
phpunit --teamcity ../../basic/unit/StatusTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--teamcity';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/unit/StatusTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
##teamcity[testCount count='15' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\SelfTest\Basic\StatusTest' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest' flowId='%d']

##teamcity[testStarted name='testSuccess' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testSuccess' flowId='%d']

##teamcity[testFinished name='testSuccess' duration='%d' flowId='%d']

##teamcity[testStarted name='testFailure' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testFailure' flowId='%d']

##teamcity[testFailed name='testFailure' message='Failed asserting that false is true.' details='%sStatusTest.php:36|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testFailure' duration='%d' flowId='%d']

##teamcity[testStarted name='testError' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testError' flowId='%d']

##teamcity[testFailed name='testError' message='RuntimeException' details='%sStatusTest.php:41|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testError' duration='%d' flowId='%d']

##teamcity[testStarted name='testIncomplete' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testIncomplete' flowId='%d']

##teamcity[testIgnored name='testIncomplete' message='' details='%sStatusTest.php:46|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testIncomplete' duration='%d' flowId='%d']

##teamcity[testStarted name='testSkipped' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testSkipped' flowId='%d']

##teamcity[testIgnored name='testSkipped' message='' details='%sStatusTest.php:51|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testSkipped' duration='%d' flowId='%d']

##teamcity[testStarted name='testRisky' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testRisky' flowId='%d']

##teamcity[testFailed name='testRisky' message='This test did not perform any assertions' details='' duration='%d' flowId='%d']

##teamcity[testFinished name='testRisky' duration='%d' flowId='%d']

##teamcity[testStarted name='testWarning' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testWarning' flowId='%d']

##teamcity[testFinished name='testWarning' duration='%d' flowId='%d']

##teamcity[testStarted name='testSuccessWithMessage' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage' flowId='%d']

##teamcity[testFinished name='testSuccessWithMessage' duration='%d' flowId='%d']

##teamcity[testStarted name='testFailureWithMessage' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage' flowId='%d']

##teamcity[testFailed name='testFailureWithMessage' message='failure with custom message|nFailed asserting that false is true.' details='%sStatusTest.php:70|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testFailureWithMessage' duration='%d' flowId='%d']

##teamcity[testStarted name='testErrorWithMessage' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage' flowId='%d']

##teamcity[testFailed name='testErrorWithMessage' message='RuntimeException: error with custom message' details='%sStatusTest.php:75|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testErrorWithMessage' duration='%d' flowId='%d']

##teamcity[testStarted name='testIncompleteWithMessage' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage' flowId='%d']

##teamcity[testIgnored name='testIncompleteWithMessage' message='incomplete with custom message' details='%sStatusTest.php:80|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testIncompleteWithMessage' duration='%d' flowId='%d']

##teamcity[testIgnored name='testSkippedByMetadata' message='PHP > 9000 is required.' details='' duration='%d' flowId='%d']

##teamcity[testStarted name='testSkippedWithMessage' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage' flowId='%d']

##teamcity[testIgnored name='testSkippedWithMessage' message='skipped with custom message' details='%sStatusTest.php:90|n' duration='%d' flowId='%d']

##teamcity[testFinished name='testSkippedWithMessage' duration='%d' flowId='%d']

##teamcity[testStarted name='testRiskyWithMessage' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage' flowId='%d']

##teamcity[testFailed name='testRiskyWithMessage' message='This test did not perform any assertions' details='' duration='%d' flowId='%d']

##teamcity[testFinished name='testRiskyWithMessage' duration='%d' flowId='%d']

##teamcity[testStarted name='testWarningWithMessage' locationHint='php_qn://%sStatusTest.php::\PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage' flowId='%d']
warning with custom message

##teamcity[testFinished name='testWarningWithMessage' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='PHPUnit\SelfTest\Basic\StatusTest' flowId='%d']
