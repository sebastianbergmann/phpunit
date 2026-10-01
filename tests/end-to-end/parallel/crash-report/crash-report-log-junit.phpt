--TEST--
phpunit --parallel=2 --log-junit logs every test of a crashed unit whose result never arrived as errored
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/CrashingTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/SteadyTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="CLI Arguments" tests="4" assertions="2" errors="2" failures="0" skipped="0" time="%s">
    <testsuite name="PHPUnit\TestFixture\ParallelCrashReport\CrashingTest" file="%sCrashingTest.php" tests="3" assertions="1" errors="2" failures="0" skipped="0" time="%s">
      <testcase name="testThatPassesBeforeTheCrash" file="%sCrashingTest.php" line="%d" class="PHPUnit\TestFixture\ParallelCrashReport\CrashingTest" classname="PHPUnit.TestFixture.ParallelCrashReport.CrashingTest" assertions="1" time="%s"/>
      <testcase name="testThatKillsTheWorkerProcess" file="%sCrashingTest.php" line="%d" class="PHPUnit\TestFixture\ParallelCrashReport\CrashingTest" classname="PHPUnit.TestFixture.ParallelCrashReport.CrashingTest" assertions="0" time="%s">
        <error type="PHPUnit\Framework\AssertionFailedError">PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatKillsTheWorkerProcess
The worker process running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest ended unexpectedly

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatKillsTheWorkerProcess.</error>
      </testcase>
      <testcase name="testThatNeverRuns" file="%sCrashingTest.php" line="%d" class="PHPUnit\TestFixture\ParallelCrashReport\CrashingTest" classname="PHPUnit.TestFixture.ParallelCrashReport.CrashingTest" assertions="0" time="%s">
        <error type="PHPUnit\Framework\AssertionFailedError">PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatNeverRuns
The worker process running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest ended unexpectedly

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatKillsTheWorkerProcess.</error>
      </testcase>
    </testsuite>
    <testsuite name="PHPUnit\TestFixture\ParallelCrashReport\SteadyTest" file="%sSteadyTest.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%s">
      <testcase name="testThatPasses" file="%sSteadyTest.php" line="%d" class="PHPUnit\TestFixture\ParallelCrashReport\SteadyTest" classname="PHPUnit.TestFixture.ParallelCrashReport.SteadyTest" assertions="1" time="%s"/>
    </testsuite>
  </testsuite>
</testsuites>
