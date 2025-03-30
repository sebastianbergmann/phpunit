--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6109
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/6109/Issue6109Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\Issue6109\Issue6109Test" file="%sIssue6109Test.php" tests="1" assertions="0" errors="0" failures="0" skipped="1" time="%s">
    <testcase name="testOne" file="%sIssue6109Test.php" line="23" class="PHPUnit\TestFixture\Issue6109\Issue6109Test" classname="PHPUnit.TestFixture.Issue6109.Issue6109Test" assertions="0" time="%s">
      <skipped/>
    </testcase>
  </testsuite>
</testsuites>
