--TEST--
phpunit --parallel=2 --log-junit nests the test suites of the XML configuration inside the root suite, as a sequential run does
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit.xml';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $logfile;

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="%sphpunit.xml" tests="4" assertions="4" errors="0" failures="0" skipped="0" time="%s">
    <testsuite name="one" tests="2" assertions="2" errors="0" failures="0" skipped="0" time="%s">
      <testsuite name="PHPUnit\TestFixture\ParallelLogging\LoggingOneTest" file="%sLoggingOneTest.php" tests="2" assertions="2" errors="0" failures="0" skipped="0" time="%s">
        <testcase name="testOne" file="%sLoggingOneTest.php" line="%d" class="PHPUnit\TestFixture\ParallelLogging\LoggingOneTest" classname="PHPUnit.TestFixture.ParallelLogging.LoggingOneTest" assertions="1" time="%s"/>
        <testcase name="testTwo" file="%sLoggingOneTest.php" line="%d" class="PHPUnit\TestFixture\ParallelLogging\LoggingOneTest" classname="PHPUnit.TestFixture.ParallelLogging.LoggingOneTest" assertions="1" time="%s"/>
      </testsuite>
    </testsuite>
    <testsuite name="two" tests="2" assertions="2" errors="0" failures="0" skipped="0" time="%s">
      <testsuite name="PHPUnit\TestFixture\ParallelLogging\LoggingTwoTest" file="%sLoggingTwoTest.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%s">
        <testcase name="testSomething" file="%sLoggingTwoTest.php" line="%d" class="PHPUnit\TestFixture\ParallelLogging\LoggingTwoTest" classname="PHPUnit.TestFixture.ParallelLogging.LoggingTwoTest" assertions="1" time="%s"/>
      </testsuite>
      <testcase name="logging.phpt" file="%slogging.phpt" assertions="1" time="%s"/>
    </testsuite>
  </testsuite>
</testsuites>
