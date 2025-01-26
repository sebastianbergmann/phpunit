--TEST--
phpunit --teamcity _files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--teamcity';
$_SERVER['argv'][] = __DIR__ . '/../_files/DataProviderTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

##teamcity[testCount count='2' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TeamCity\DataProviderTest' locationHint='php_qn://%sDataProviderTest.php::\PHPUnit\TestFixture\TeamCity\DataProviderTest' flowId='%d']
##teamcity[testSuiteStarted name='testOne' locationHint='php_qn://%sDataProviderTest.php::\PHPUnit\TestFixture\TeamCity\DataProviderTest::testOne' flowId='%d']
##teamcity[testStarted name='testOne with data set #0' locationHint='php_qn://%sDataProviderTest.php::\PHPUnit\TestFixture\TeamCity\DataProviderTest::testOne with data set #0' flowId='%d']
##teamcity[testFinished name='testOne with data set #0' duration='%d' flowId='%d']
##teamcity[testStarted name='testOne with data set #1' locationHint='php_qn://%sDataProviderTest.php::\PHPUnit\TestFixture\TeamCity\DataProviderTest::testOne with data set #1' flowId='%d']
##teamcity[testFailed name='testOne with data set #1' message='Failed asserting that false is true.' details='%sDataProviderTest.php:28|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testOne with data set #1' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='testOne' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TeamCity\DataProviderTest' flowId='%d']
Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\TeamCity\DataProviderTest::testOne with data set #1 (false)
Failed asserting that false is true.

%sDataProviderTest.php:28

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
