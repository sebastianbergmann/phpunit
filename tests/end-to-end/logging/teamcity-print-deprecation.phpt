--TEST--
TeamCity: print deprecation message
--FILE--
<?php declare(strict_types=1);

$parentDirectory = dirname(__DIR__);

$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  realpath($parentDirectory. '/_files/stop-on-fail-on/DeprecationTest.php');

require realpath($parentDirectory . '/../bootstrap.php');

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='%d' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest' locationHint='%sDeprecationTest.php::\PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest' flowId='%d']

##teamcity[testStarted name='testOne' locationHint='%sDeprecationTest.php::\PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testOne' flowId='%d']

##teamcity[testFinished name='testOne' duration='%d' flowId='%d']

##teamcity[testStarted name='testTwo' locationHint='%sDeprecationTest.php::\PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testTwo' flowId='%d']

##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest' flowId='%d']
