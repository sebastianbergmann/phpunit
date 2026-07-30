--TEST--
DataProvider: bidirectional control characters in a data set name are escaped
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/BidirectionalControlCharacterKeyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\DataProvider\BidirectionalControlCharacterKeyTest" file="%sBidirectionalControlCharacterKeyTest.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%f">
    <testsuite name="PHPUnit\TestFixture\DataProvider\BidirectionalControlCharacterKeyTest::testOne" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%f">
      <testcase name="testOne with data set &quot;before\u{202E}after&quot;" file="%sBidirectionalControlCharacterKeyTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\BidirectionalControlCharacterKeyTest" classname="PHPUnit.TestFixture.DataProvider.BidirectionalControlCharacterKeyTest" assertions="1" time="%f"/>
    </testsuite>
  </testsuite>
</testsuites>
