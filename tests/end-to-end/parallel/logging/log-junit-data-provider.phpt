--TEST--
phpunit --parallel=2 --log-junit nests the tests of a data provider method in their own test suite, as a sequential run does
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/tests/LoggingDataProviderTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\ParallelLogging\LoggingDataProviderTest" file="%sLoggingDataProviderTest.php" tests="2" assertions="2" errors="0" failures="0" skipped="0" time="%s">
    <testsuite name="PHPUnit\TestFixture\ParallelLogging\LoggingDataProviderTest::testProvided" tests="2" assertions="2" errors="0" failures="0" skipped="0" time="%s">
      <testcase name="testProvided with data set &quot;one&quot;" file="%sLoggingDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\ParallelLogging\LoggingDataProviderTest" classname="PHPUnit.TestFixture.ParallelLogging.LoggingDataProviderTest" assertions="1" time="%s"/>
      <testcase name="testProvided with data set &quot;two&quot;" file="%sLoggingDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\ParallelLogging\LoggingDataProviderTest" classname="PHPUnit.TestFixture.ParallelLogging.LoggingDataProviderTest" assertions="1" time="%s"/>
    </testsuite>
  </testsuite>
</testsuites>
