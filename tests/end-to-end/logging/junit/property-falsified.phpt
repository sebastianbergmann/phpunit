--TEST--
JUnit XML logging includes how a property was falsified, with its minimal counterexample, in the content of the failure element
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../property-based-testing/_files/FalsifiedPropertyTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest" file="%sFalsifiedPropertyTest.php" tests="1" assertions="2" errors="0" failures="1" skipped="0" time="%f">
    <testsuite name="PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen" tests="1" assertions="2" errors="0" failures="1" skipped="0" time="%f">
      <testcase name="testSumIsAtMostTen" file="%sFalsifiedPropertyTest.php" line="22" class="PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest" classname="PHPUnit.TestFixture.PropertyBasedTesting.FalsifiedPropertyTest" assertions="2" time="%f">
        <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen%r(&#13;)?%r
Failed asserting that 11 is equal to 10 or is less than 10.
%r(&#13;)?%r
Property falsified after 1 example, counterexample shrunk in 2 steps:

    $a = 0;
    $b = 11;
%r(&#13;)?%r
%sFalsifiedPropertyTest.php:%d</failure>
      </testcase>
    </testsuite>
  </testsuite>
</testsuites>
