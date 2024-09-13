--TEST--
phpunit --process-isolation --log-junit php://stdout ../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\DataProviderTest" file="%sDataProviderTest.php" tests="4" assertions="4" errors="0" failures="1" skipped="0" time="%f">
    <testsuite name="PHPUnit\TestFixture\DataProviderTest::testAdd" tests="4" assertions="4" errors="0" failures="1" skipped="0" time="%f">
      <testcase name="testAdd with data set #0" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" assertions="1" time="%f"/>
      <testcase name="testAdd with data set #1" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" assertions="1" time="%f"/>
      <testcase name="testAdd with data set #2" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" assertions="1" time="%f">
        <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProviderTest::testAdd with data set #2%A
Failed asserting that 2 matches expected 3.
%A
%s:%i</failure>
      </testcase>
      <testcase name="testAdd with data set #3" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" assertions="1" time="%f"/>
    </testsuite>
  </testsuite>
</testsuites>
