--TEST--
#[Retry] with JUnit XML logging reports only the deciding attempt and counts the test once
--FILE--
<?php declare(strict_types=1);
$junitFile = tempnam(sys_get_temp_dir(), 'phpunit_retry_junit_');

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $junitFile;
$_SERVER['argv'][] = __DIR__ . '/_files/PassesOnSecondAttemptTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($junitFile);

unlink($junitFile);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 retried test:

1) PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest::testOne
1 failed attempt

OK (1 test, 1 assertion)
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest" file="%sPassesOnSecondAttemptTest.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%f">
    <testsuite name="PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest::testOne" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%f">
      <testcase name="testOne (attempt 2 of 3)" file="%sPassesOnSecondAttemptTest.php" line="%d" class="PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest" classname="PHPUnit.TestFixture.Retry.PassesOnSecondAttemptTest" assertions="1" time="%f"/>
    </testsuite>
  </testsuite>
</testsuites>
