--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5561
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/5561/Issue5561Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\Issue5561\Issue5561Test" file="%sIssue5561Test.php" tests="1" assertions="0" errors="0" failures="1" skipped="0" time="%s">
    <testcase name="testOne" file="%sIssue5561Test.php" line="21" class="PHPUnit\TestFixture\Issue5561\Issue5561Test" classname="PHPUnit.TestFixture.Issue5561.Issue5561Test" assertions="0" time="%s">
      <failure type="PHPUnit\Framework\ExpectationFailedException">PHPUnit\TestFixture\Issue5561\Issue5561Test::testOne%A
Failed asserting that false is true.
%A
%sIssue5561Test.php:18</failure>
    </testcase>
  </testsuite>
</testsuites>
