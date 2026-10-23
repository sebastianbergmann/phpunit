--TEST--
TeamCity: tests skipped/errored due to unmet/invalid dependencies
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../../_files/dependencies/DependencyFailureTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='6' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\DependencyFailureTest' locationHint='php_qn://%sDependencyFailureTest.php::\PHPUnit\TestFixture\DependencyFailureTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='php_qn://%sDependencyFailureTest.php::\PHPUnit\TestFixture\DependencyFailureTest::testOne' flowId='%d']
##teamcity[testFailed name='testOne' message='Failed asserting that false is true.' details='%s|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testStarted name='testTwo' locationHint='php_qn://%sDependencyFailureTest.php::\PHPUnit\TestFixture\DependencyFailureTest::testTwo' flowId='%d']
##teamcity[testIgnored name='testTwo' message='This test depends on "PHPUnit\TestFixture\DependencyFailureTest::testOne" to pass' duration='%d' flowId='%d']
##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']
##teamcity[testStarted name='testThree' locationHint='php_qn://%sDependencyFailureTest.php::\PHPUnit\TestFixture\DependencyFailureTest::testThree' flowId='%d']
##teamcity[testIgnored name='testThree' message='This test depends on "PHPUnit\TestFixture\DependencyFailureTest::testTwo" to pass' duration='%d' flowId='%d']
##teamcity[testFinished name='testThree' duration='%d' flowId='%d']
##teamcity[testStarted name='testFour' locationHint='php_qn://%sDependencyFailureTest.php::\PHPUnit\TestFixture\DependencyFailureTest::testFour' flowId='%d']
##teamcity[testIgnored name='testFour' message='This test depends on "PHPUnit\TestFixture\DependencyFailureTest::testOne" to pass' duration='%d' flowId='%d']
##teamcity[testFinished name='testFour' duration='%d' flowId='%d']
##teamcity[testStarted name='testHandlesDependencyOnTestMethodThatDoesNotExist' locationHint='php_qn://%sDependencyFailureTest.php::\PHPUnit\TestFixture\DependencyFailureTest::testHandlesDependencyOnTestMethodThatDoesNotExist' flowId='%d']
##teamcity[testFailed name='testHandlesDependencyOnTestMethodThatDoesNotExist' message='This test depends on "PHPUnit\TestFixture\DependencyFailureTest::doesNotExist" which does not exist' details='' duration='%d' flowId='%d']
##teamcity[testFinished name='testHandlesDependencyOnTestMethodThatDoesNotExist' duration='%d' flowId='%d']
##teamcity[testStarted name='testHandlesDependencyOnTestMethodWithEmptyName' locationHint='php_qn://%sDependencyFailureTest.php::\PHPUnit\TestFixture\DependencyFailureTest::testHandlesDependencyOnTestMethodWithEmptyName' flowId='%d']
##teamcity[testFailed name='testHandlesDependencyOnTestMethodWithEmptyName' message='This test depends on "" which does not exist' details='' duration='%d' flowId='%d']
##teamcity[testFinished name='testHandlesDependencyOnTestMethodWithEmptyName' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\DependencyFailureTest' flowId='%d']
