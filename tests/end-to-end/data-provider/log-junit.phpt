--TEST--
phpunit --log-junit php://stdout ../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderTest.php';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderWithStringKeysTest.php';
$_SERVER['argv'][] = __DIR__ . '/../../_files/success.phpt';
$_SERVER['argv'][] = __DIR__ . '/../../_files/failure.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="CLI Arguments" tests="10" assertions="10" errors="0" failures="3" skipped="0" time="%s">
    <testsuite name="PHPUnit\TestFixture\DataProviderTest" file="%sDataProviderTest.php" tests="4" assertions="4" errors="0" failures="1" skipped="0" time="%s">
      <testsuite name="PHPUnit\TestFixture\DataProviderTest::testAdd" tests="4" assertions="4" errors="0" failures="1" skipped="0" time="%s">
        <testcase name="testAdd with data set #0" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" assertions="1" time="%s"/>
        <testcase name="testAdd with data set #1" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" assertions="1" time="%s"/>
        <testcase name="testAdd with data set #2" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" assertions="1" time="%s">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProviderTest::testAdd with data set #2%A
Failed asserting that 2 matches expected 3.
%A
%sDataProviderTest.php:%d</failure>
        </testcase>
        <testcase name="testAdd with data set #3" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" assertions="1" time="%s"/>
      </testsuite>
    </testsuite>
    <testsuite name="PHPUnit\TestFixture\DataProviderWithStringKeysTest" file="%sDataProviderWithStringKeysTest.php" tests="4" assertions="4" errors="0" failures="1" skipped="0" time="%s">
      <testsuite name="PHPUnit\TestFixture\DataProviderWithStringKeysTest::testAdd" tests="4" assertions="4" errors="0" failures="1" skipped="0" time="%s">
        <testcase name="testAdd with data set &quot;0 + 0 = 0&quot;" file="%sDataProviderWithStringKeysTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderWithStringKeysTest" classname="PHPUnit.TestFixture.DataProviderWithStringKeysTest" assertions="1" time="%s"/>
        <testcase name="testAdd with data set &quot;0 + 1 = 1&quot;" file="%sDataProviderWithStringKeysTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderWithStringKeysTest" classname="PHPUnit.TestFixture.DataProviderWithStringKeysTest" assertions="1" time="%s"/>
        <testcase name="testAdd with data set &quot;1 + 1 = 3&quot;" file="%sDataProviderWithStringKeysTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderWithStringKeysTest" classname="PHPUnit.TestFixture.DataProviderWithStringKeysTest" assertions="1" time="%s">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProviderWithStringKeysTest::testAdd with data set "1 + 1 = 3"%A
Failed asserting that 2 matches expected 3.
%A
%sDataProviderWithStringKeysTest.php:%d</failure>
        </testcase>
        <testcase name="testAdd with data set &quot;1 + 0 = 1&quot;" file="%sDataProviderWithStringKeysTest.php" line="%d" class="PHPUnit\TestFixture\DataProviderWithStringKeysTest" classname="PHPUnit.TestFixture.DataProviderWithStringKeysTest" assertions="1" time="%s"/>
      </testsuite>
    </testsuite>
    <testcase name="success.phpt" file="%ssuccess.phpt" assertions="1" time="%s"/>
    <testcase name="failure.phpt" file="%sfailure.phpt" assertions="1" time="%s">
      <failure type="PHPUnit\Framework\PhptAssertionFailedError">failure.phptFailed asserting that two strings are equal.%A
--- Expected
+++ Actual
@@ @@
-'success'
+'failure'
%A
%s:%d</failure>
    </testcase>
  </testsuite>
</testsuites>
