--TEST--
#[Repeat] with --log-teamcity reports repetitions skipped after the failure threshold is reached as ignored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/_files/FailureThresholdReachedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='5' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest' locationHint='php_qn:%sFailureThresholdReachedTest.php::\PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne' locationHint='php_qn:%sFailureThresholdReachedTest.php::\PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne' flowId='%d']
##teamcity[testStarted name='testOne (repetition 1 of 5)' locationHint='php_qn:%sFailureThresholdReachedTest.php::\PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 1 of 5)' flowId='%d']
##teamcity[testFailed name='testOne (repetition 1 of 5)' message='Failure on repetition 1' details='%sFailureThresholdReachedTest.php:26|n' duration='%s' flowId='%d']
##teamcity[testFinished name='testOne (repetition 1 of 5)' duration='%s' flowId='%d']
##teamcity[testStarted name='testOne (repetition 2 of 5)' locationHint='php_qn:%sFailureThresholdReachedTest.php::\PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 2 of 5)' flowId='%d']
##teamcity[testFailed name='testOne (repetition 2 of 5)' message='Failure on repetition 2' details='%sFailureThresholdReachedTest.php:26|n' duration='%s' flowId='%d']
##teamcity[testFinished name='testOne (repetition 2 of 5)' duration='%s' flowId='%d']
##teamcity[testIgnored name='testOne (repetition 3 of 5)' message='Remaining repetition skipped after failure in repetition 2' duration='%s' flowId='%d']
##teamcity[testIgnored name='testOne (repetition 4 of 5)' message='Remaining repetition skipped after failure in repetition 2' duration='%s' flowId='%d']
##teamcity[testIgnored name='testOne (repetition 5 of 5)' message='Remaining repetition skipped after failure in repetition 2' duration='%s' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest' flowId='%d']
