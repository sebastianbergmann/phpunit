--TEST--
phpunit --log-junit php://stdout ../../_files/DataProvider/
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProvider/';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

..F..F..F............F....F..F..F............F..                  48 / 48 (100%)

Time: %s, Memory: %s

There were 8 failures:

1) PHPUnit\TestFixture\DataProvider\DataProviderExternalTest::testAdd with data set #2
Failed asserting that 2 matches expected 3.

%s:%i

2) PHPUnit\TestFixture\DataProvider\DataProviderExternalTest::testAdd with data set #5
Failed asserting that 2 matches expected 0.

%s:%i

3) PHPUnit\TestFixture\DataProvider\DataProviderExternalTest::testAdd with data set #8
Failed asserting that 3 matches expected 0.

%s:%i

4) PHPUnit\TestFixture\DataProvider\DataProviderExternalTest::testAdd with data set #21
Failed asserting that 2 matches expected 0.

%s:%i

5) PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd with data set #2
Failed asserting that 2 matches expected 3.

%s:%i

6) PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd with data set #5
Failed asserting that 2 matches expected 0.

%s:%i

7) PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd with data set #8
Failed asserting that 3 matches expected 0.

%s:%i

8) PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd with data set #21
Failed asserting that 2 matches expected 0.

%s:%i

FAILURES!
Tests: 48, Assertions: 48, Failures: 8.
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="%sDataProvider" tests="48" assertions="48" errors="0" failures="8" skipped="0" time="%f">
    <testsuite name="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" file="%sDataProviderExternalTest.php" tests="24" assertions="24" errors="0" failures="4" skipped="0" time="%f">
      <testsuite name="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest::testAdd" tests="24" assertions="24" errors="0" failures="4" skipped="0" time="%f">
        <testcase name="testAdd with data set #0" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #1" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #2" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProvider\DataProviderExternalTest::testAdd with data set #2%A
Failed asserting that 2 matches expected 3.
%A
%s:%i</failure>
        </testcase>
        <testcase name="testAdd with data set #3" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #4" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #5" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProvider\DataProviderExternalTest::testAdd with data set #5%A
Failed asserting that 2 matches expected 0.
%A
%s:%i</failure>
        </testcase>
        <testcase name="testAdd with data set #6" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #7" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #8" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProvider\DataProviderExternalTest::testAdd with data set #8%A
Failed asserting that 3 matches expected 0.
%A
%s:%i</failure>
        </testcase>
        <testcase name="testAdd with data set #9" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #10" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #11" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #12" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #13" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #14" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #15" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #16" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #17" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #18" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #19" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #20" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #21" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProvider\DataProviderExternalTest::testAdd with data set #21%A
Failed asserting that 2 matches expected 0.
%A
%s:%i</failure>
        </testcase>
        <testcase name="testAdd with data set #22" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #23" file="%sDataProviderExternalTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderExternalTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderExternalTest" assertions="1" time="%f"/>
      </testsuite>
    </testsuite>
    <testsuite name="PHPUnit\TestFixture\DataProvider\DataProviderTest" file="%sDataProviderTest.php" tests="24" assertions="24" errors="0" failures="4" skipped="0" time="%f">
      <testsuite name="PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd" tests="24" assertions="24" errors="0" failures="4" skipped="0" time="%f">
        <testcase name="testAdd with data set #0" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #1" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #2" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd with data set #2%A
Failed asserting that 2 matches expected 3.
%A
%s:%i</failure>
        </testcase>
        <testcase name="testAdd with data set #3" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #4" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #5" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd with data set #5%A
Failed asserting that 2 matches expected 0.
%A
%s:%i</failure>
        </testcase>
        <testcase name="testAdd with data set #6" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #7" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #8" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd with data set #8%A
Failed asserting that 3 matches expected 0.
%A
%s:%i</failure>
        </testcase>
        <testcase name="testAdd with data set #9" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #10" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #11" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #12" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #13" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #14" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #15" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #16" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #17" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #18" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #19" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #20" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #21" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f">
          <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd with data set #21%A
Failed asserting that 2 matches expected 0.
%A
%s:%i</failure>
        </testcase>
        <testcase name="testAdd with data set #22" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
        <testcase name="testAdd with data set #23" file="%sDataProviderTest.php" line="%d" class="PHPUnit\TestFixture\DataProvider\DataProviderTest" classname="PHPUnit.TestFixture.DataProvider.DataProviderTest" assertions="1" time="%f"/>
      </testsuite>
    </testsuite>
  </testsuite>
</testsuites>
