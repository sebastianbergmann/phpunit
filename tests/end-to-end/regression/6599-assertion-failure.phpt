--TEST--
GH-6599: --teamcity wraps assertion failures in setUp() and setUpBeforeClass()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'Issue6599Failed';
$_SERVER['argv'][] = __DIR__ . '/6599';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='2' flowId='%d']
##teamcity[testSuiteStarted name='CLI Arguments' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpBeforeClassTest' locationHint='php_qn://%sIssue6599FailedSetUpBeforeClassTest.php::\PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpBeforeClassTest' flowId='%d']
##teamcity[testStarted name='PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpBeforeClassTest' flowId='%d']
##teamcity[testFailed name='PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpBeforeClassTest' message='assertion failure in setUpBeforeClass|nFailed asserting that false is true.' details='%sIssue6599FailedSetUpBeforeClassTest.php:%d|n' duration='%d' flowId='%d']
##teamcity[testFinished name='PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpBeforeClassTest' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpBeforeClassTest' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpBeforeClassTest' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpTest' locationHint='php_qn://%sIssue6599FailedSetUpTest.php::\PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='php_qn://%sIssue6599FailedSetUpTest.php::\PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpTest::testOne' flowId='%d']
##teamcity[testFailed name='testOne' message='assertion failure in setUp|nFailed asserting that false is true.' details='%sIssue6599FailedSetUpTest.php:%d|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Issue6599\Issue6599FailedSetUpTest' flowId='%d']
##teamcity[testSuiteFinished name='CLI Arguments' flowId='%d']
