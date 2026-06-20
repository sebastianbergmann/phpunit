--TEST--
#[Repeat] with JUnit XML logging records repetitions skipped after the failure threshold is reached
--FILE--
<?php declare(strict_types=1);
$junitFile = tempnam(sys_get_temp_dir(), 'phpunit_repeat_junit_');

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $junitFile;
$_SERVER['argv'][] = __DIR__ . '/_files/FailureThresholdReachedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($junitFile);

unlink($junitFile);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest" file="%sFailureThresholdReachedTest.php" tests="5" assertions="2" errors="0" failures="2" skipped="3" time="%f">
    <testsuite name="PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne" tests="5" assertions="2" errors="0" failures="2" skipped="3" time="%f">
      <testcase name="testOne (repetition 1 of 5)" file="%sFailureThresholdReachedTest.php" line="21" class="PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest" classname="PHPUnit.TestFixture.Repeat.FailureThresholdReachedTest" assertions="1" time="%f">
        <failure type="PHPUnit\Framework\AssertionFailedError">PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 1 of 5)%A
Failure on repetition 1
%A
%sFailureThresholdReachedTest.php:26</failure>
      </testcase>
      <testcase name="testOne (repetition 2 of 5)" file="%sFailureThresholdReachedTest.php" line="21" class="PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest" classname="PHPUnit.TestFixture.Repeat.FailureThresholdReachedTest" assertions="1" time="%f">
        <failure type="PHPUnit\Framework\AssertionFailedError">PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 2 of 5)%A
Failure on repetition 2
%A
%sFailureThresholdReachedTest.php:26</failure>
      </testcase>
      <testcase name="testOne (repetition 3 of 5)" file="%sFailureThresholdReachedTest.php" line="21" class="PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest" classname="PHPUnit.TestFixture.Repeat.FailureThresholdReachedTest" assertions="0" time="%f">
        <skipped/>
      </testcase>
      <testcase name="testOne (repetition 4 of 5)" file="%sFailureThresholdReachedTest.php" line="21" class="PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest" classname="PHPUnit.TestFixture.Repeat.FailureThresholdReachedTest" assertions="0" time="%f">
        <skipped/>
      </testcase>
      <testcase name="testOne (repetition 5 of 5)" file="%sFailureThresholdReachedTest.php" line="21" class="PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest" classname="PHPUnit.TestFixture.Repeat.FailureThresholdReachedTest" assertions="0" time="%f">
        <skipped/>
      </testcase>
    </testsuite>
  </testsuite>
</testsuites>
