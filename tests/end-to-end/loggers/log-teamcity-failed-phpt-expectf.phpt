--TEST--
phpunit --teamcity  ../../fail/fail-expectf.phpt
--DESCRIPTION--
Check that teamcity logger correctly print fails with Actual\Expected attributes
on phpt tests with `--EXPECTF--` section
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--teamcity';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../fail/fail-expectf.phpt');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='1' flowId='%d']

##teamcity[testStarted name='%s' flowId='%d']

##teamcity[testFailed name='%sfail%efail-expectf.phpt' message='Failed asserting that string matches format description.' details='%s' duration='%s' type='comparisonFailure' actual='Foo|nMultiline diff|nBuzz' expected='Foo|nMultiline|nBuzz' flowId='%s']

##teamcity[testFinished name='%s' duration='%d' flowId='%d']


Time: %s, Memory: %s


FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
