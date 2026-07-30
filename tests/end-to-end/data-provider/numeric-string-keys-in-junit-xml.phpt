--TEST--
DataProvider: numeric string keys that PHP does not canonicalize are logged distinctly
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/NumericStringKeysTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\DataProvider\NumericStringKeysTest" file="%sNumericStringKeysTest.php" tests="4" assertions="4" errors="0" failures="0" skipped="0" time="%f">
    <testsuite name="PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne" tests="4" assertions="4" errors="0" failures="0" skipped="0" time="%f">
      <testcase name="testOne with data set &quot;1.5&quot;" file="%sNumericStringKeysTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\NumericStringKeysTest" classname="PHPUnit.TestFixture.DataProvider.NumericStringKeysTest" assertions="1" time="%f"/>
      <testcase name="testOne with data set &quot;1.9&quot;" file="%sNumericStringKeysTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\NumericStringKeysTest" classname="PHPUnit.TestFixture.DataProvider.NumericStringKeysTest" assertions="1" time="%f"/>
      <testcase name="testOne with data set &quot;0123&quot;" file="%sNumericStringKeysTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\NumericStringKeysTest" classname="PHPUnit.TestFixture.DataProvider.NumericStringKeysTest" assertions="1" time="%f"/>
      <testcase name="testOne with data set #0" file="%sNumericStringKeysTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\NumericStringKeysTest" classname="PHPUnit.TestFixture.DataProvider.NumericStringKeysTest" assertions="1" time="%f"/>
    </testsuite>
  </testsuite>
</testsuites>
