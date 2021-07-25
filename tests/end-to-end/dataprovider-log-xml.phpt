--TEST--
phpunit --log-junit php://stdout ../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/DataProviderTest.php';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

..F.                                                                4 / 4 (100%)<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\DataProviderTest" file="%sDataProviderTest.php" tests="4" assertions="4" errors="0" warnings="0" failures="1" skipped="0" time="%f">
    <testsuite name="PHPUnit\TestFixture\DataProviderTest::testAdd" tests="4" assertions="4" errors="0" warnings="0" failures="1" skipped="0" time="%f">
      <testcase name="testAdd with data set #0" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" file="%sDataProviderTest.php" line="%d" assertions="1" time="%f"/>
      <testcase name="testAdd with data set #1" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" file="%sDataProviderTest.php" line="%d" assertions="1" time="%f"/>
      <testcase name="testAdd with data set #2" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" file="%sDataProviderTest.php" line="%d" assertions="1" time="%f">
        <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProviderTest::testAdd with data set #2 (1, 1, 3)
Failed asserting that 2 matches expected 3.

%s:%i</failure>
      </testcase>
      <testcase name="testAdd with data set #3" class="PHPUnit\TestFixture\DataProviderTest" classname="PHPUnit.TestFixture.DataProviderTest" file="%sDataProviderTest.php" line="%d" assertions="1" time="%f"/>
    </testsuite>
  </testsuite>
</testsuites>


Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\DataProviderTest::testAdd with data set #2 (1, 1, 3)
Failed asserting that 2 matches expected 3.

%s:%i

FAILURES!
Tests: 4, Assertions: 4, Failures: 1.
