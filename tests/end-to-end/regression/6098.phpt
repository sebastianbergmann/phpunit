--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6098
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/6098/Issue6098Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\Issue6098\Issue6098Test" file="%sIssue6098Test.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%f">
    <testcase name="testOne" file="%sIssue6098Test.php" line="16" class="PHPUnit\TestFixture\Issue6098\Issue6098Test" classname="PHPUnit.TestFixture.Issue6098.Issue6098Test" assertions="1" time="%f">
      <system-out>output</system-out>
    </testcase>
  </testsuite>
</testsuites>
