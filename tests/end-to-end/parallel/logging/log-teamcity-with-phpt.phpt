--TEST--
phpunit --parallel=2 --log-teamcity reports the total test count and wraps all tests in the root suite, as a sequential run does
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/_files/tests/LoggingOneTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/tests/LoggingTwoTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/tests/logging.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='4' flowId='%d']
##teamcity[testSuiteStarted name='CLI Arguments' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\ParallelLogging\LoggingOneTest' locationHint='php_qn://%sLoggingOneTest.php::\PHPUnit\TestFixture\ParallelLogging\LoggingOneTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='php_qn://%sLoggingOneTest.php::\PHPUnit\TestFixture\ParallelLogging\LoggingOneTest::testOne' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testStarted name='testTwo' locationHint='php_qn://%sLoggingOneTest.php::\PHPUnit\TestFixture\ParallelLogging\LoggingOneTest::testTwo' flowId='%d']
##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\ParallelLogging\LoggingOneTest' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\ParallelLogging\LoggingTwoTest' locationHint='php_qn://%sLoggingTwoTest.php::\PHPUnit\TestFixture\ParallelLogging\LoggingTwoTest' flowId='%d']
##teamcity[testStarted name='testSomething' locationHint='php_qn://%sLoggingTwoTest.php::\PHPUnit\TestFixture\ParallelLogging\LoggingTwoTest::testSomething' flowId='%d']
##teamcity[testFinished name='testSomething' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\ParallelLogging\LoggingTwoTest' flowId='%d']
##teamcity[testStarted name='%slogging.phpt' flowId='%d']
##teamcity[testFinished name='%slogging.phpt' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='CLI Arguments' flowId='%d']
