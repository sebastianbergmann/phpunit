--TEST--
phpunit --teamcity ../../basic/unit/StatusTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/teamcity-warning/phpunit.xml';
$_SERVER['argv'][] = '--teamcity';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s


##teamcity[testCount count='1' flowId='%d']

##teamcity[testSuiteStarted name='%s/tests/end-to-end/logging/_files/teamcity-warning/phpunit.xml' flowId='%d']

##teamcity[testSuiteStarted name='default' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Test' locationHint='php_qn://%s/teamcity-warning/tests/Test.php::\PHPUnit\TestFixture\Test' flowId='%d']

##teamcity[testStarted name='testOne' locationHint='php_qn://%s/teamcity-warning/tests/Test.php::\PHPUnit\TestFixture\Test::testOne' flowId='%d']
.                                                                   1 / 1 (100%)
##teamcity[testFinished name='testOne' duration='%s' flowId='%d']

##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Test' flowId='%d']

##teamcity[testSuiteFinished name='default' flowId='%d']

##teamcity[testSuiteFinished name='%s/tests/end-to-end/logging/_files/teamcity-warning/phpunit.xml' flowId='%d']


Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Test results may not be as expected because the XML configuration file did not pass validation:

  Line 11:
  - Element 'foo': This element is not expected.


WARNINGS!
Tests: 1, Assertions: 1, Warnings: 1.
