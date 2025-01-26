--TEST--
JUnit XML: test skipped in test method
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SkippedTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\TestRunnerStopping\SkippedTest" file="%sSkippedTest.php" tests="2" assertions="1" errors="0" failures="0" skipped="1" time="%s">
    <testcase name="testOne" file="%sSkippedTest.php" line="16" class="PHPUnit\TestFixture\TestRunnerStopping\SkippedTest" classname="PHPUnit.TestFixture.TestRunnerStopping.SkippedTest" assertions="0" time="%s">
      <skipped/>
    </testcase>
    <testcase name="testTwo" file="%sSkippedTest.php" line="21" class="PHPUnit\TestFixture\TestRunnerStopping\SkippedTest" classname="PHPUnit.TestFixture.TestRunnerStopping.SkippedTest" assertions="1" time="%s"/>
  </testsuite>
</testsuites>
