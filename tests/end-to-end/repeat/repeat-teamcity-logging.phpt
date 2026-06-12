--TEST--
--repeat with --log-teamcity reports a test suite with a location hint per repeated test method
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='4' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Repeat\SuccessTest' locationHint='php_qn:%sSuccessTest.php::\PHPUnit\TestFixture\Repeat\SuccessTest' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Repeat\SuccessTest::testOne' locationHint='php_qn:%sSuccessTest.php::\PHPUnit\TestFixture\Repeat\SuccessTest::testOne' flowId='%d']
##teamcity[testStarted name='testOne (repetition 1 of 2)' locationHint='php_qn:%sSuccessTest.php::\PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 1 of 2)' flowId='%d']
##teamcity[testFinished name='testOne (repetition 1 of 2)' duration='%s' flowId='%d']
##teamcity[testStarted name='testOne (repetition 2 of 2)' locationHint='php_qn:%sSuccessTest.php::\PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 2 of 2)' flowId='%d']
##teamcity[testFinished name='testOne (repetition 2 of 2)' duration='%s' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Repeat\SuccessTest::testOne' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Repeat\SuccessTest::testTwo' locationHint='php_qn:%sSuccessTest.php::\PHPUnit\TestFixture\Repeat\SuccessTest::testTwo' flowId='%d']
##teamcity[testStarted name='testTwo (repetition 1 of 2)' locationHint='php_qn:%sSuccessTest.php::\PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 1 of 2)' flowId='%d']
##teamcity[testFinished name='testTwo (repetition 1 of 2)' duration='%s' flowId='%d']
##teamcity[testStarted name='testTwo (repetition 2 of 2)' locationHint='php_qn:%sSuccessTest.php::\PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 2 of 2)' flowId='%d']
##teamcity[testFinished name='testTwo (repetition 2 of 2)' duration='%s' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Repeat\SuccessTest::testTwo' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Repeat\SuccessTest' flowId='%d']
