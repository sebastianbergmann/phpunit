--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5771
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = __DIR__ . '/5771/Issue5771Test.php';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\Issue5771\Issue5771Test" file="%sIssue5771Test.php" tests="1" assertions="0" errors="1" failures="0" skipped="0" time="%s">
    <testcase name="testOne" file="%sIssue5771Test.php" line="18" class="PHPUnit\TestFixture\Issue5771\Issue5771Test" classname="PHPUnit.TestFixture.Issue5771.Issue5771Test" assertions="0" time="%s">
      <error type="PHPUnit\Framework\AssertionFailedError">PHPUnit\TestFixture\Issue5771\Issue5771Test::testOne%A
Test was run in child process and ended unexpectedly</error>
    </testcase>
  </testsuite>
</testsuites>
