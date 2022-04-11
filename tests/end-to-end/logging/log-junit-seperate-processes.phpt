--TEST--
phpunit --log-junit php://stdout ./_files/TestSeperateProcesses.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/TestSeperateProcesses.php');

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s #StandWithUkraine

F                                                                   1 / 1 (100%)setUp output;test output;tearDown output;<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="TestSeperateProcesses" file="%s" tests="1" assertions="1" errors="0" warnings="0" failures="1" skipped="0" time="%s">
    <testcase name="testStdout" class="TestSeperateProcesses" classname="TestSeperateProcesses" file="%s" line="27" assertions="1" time="%s">
      <failure type="PHPUnit\Framework\ExpectationFailedException">TestSeperateProcesses::testStdout
Failed asserting that false is true.

%s
</failure>
      <system-out>setUp output;test output;tearDown output;</system-out>
    </testcase>
  </testsuite>
</testsuites>


Time: %s, Memory: %s

There was 1 failure:

1) TestSeperateProcesses::testStdout
Failed asserting that false is true.

%s

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
