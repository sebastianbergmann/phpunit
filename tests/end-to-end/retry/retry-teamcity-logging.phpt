--TEST--
#[Retry] with --log-teamcity reports a test suite with a location hint per retried test method and only the deciding attempt
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/_files/PassesOnSecondAttemptTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='1' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest' locationHint='php_qn:%sPassesOnSecondAttemptTest.php::\PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest::testOne' locationHint='php_qn:%sPassesOnSecondAttemptTest.php::\PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest::testOne' flowId='%d']
##teamcity[testStarted name='testOne (attempt 2 of 3)' locationHint='php_qn:%sPassesOnSecondAttemptTest.php::\PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest::testOne (attempt 2 of 3)' flowId='%d']
##teamcity[testFinished name='testOne (attempt 2 of 3)' duration='%s' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest::testOne' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest' flowId='%d']
