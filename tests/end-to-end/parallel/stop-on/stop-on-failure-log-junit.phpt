--TEST--
phpunit --parallel=2 --stop-on-failure --log-junit closes the test suite of a test class whose worker is terminated when the run stops
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/halted/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="CLI Arguments" tests="1" assertions="1" errors="0" failures="1" skipped="0" time="%s">
    <testsuite name="PHPUnit\TestFixture\ParallelStopOn\HaltedTest" file="%sHaltedTest.php" tests="1" assertions="1" errors="0" failures="1" skipped="0" time="%s">
      <testcase name="testFails" file="%sHaltedTest.php" line="%d" class="PHPUnit\TestFixture\ParallelStopOn\HaltedTest" classname="PHPUnit.TestFixture.ParallelStopOn.HaltedTest" assertions="1" time="%s">
        <failure type="PHPUnit\Framework\AssertionFailedError">PHPUnit\TestFixture\ParallelStopOn\HaltedTest::testFails
failure

%sHaltedTest.php:%d</failure>
      </testcase>
    </testsuite>
  </testsuite>
</testsuites>
