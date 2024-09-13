--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5258
--FILE--
<?php declare(strict_types=1);
$junitFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $junitFile;
$_SERVER['argv'][] = __DIR__ . '/5258/Issue5258Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($junitFile);

unlink($junitFile);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.S                                                                  2 / 2 (100%)

Time: %s, Memory: %s MB

OK, but some tests were skipped!
Tests: 2, Assertions: 1, Skipped: 1.
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\Issue5258\Issue5258Test" file="%sIssue5258Test.php" tests="2" assertions="1" errors="0" failures="0" skipped="1" time="%f">
    <testcase name="testOne" file="%sIssue5258Test.php" line="%d" class="PHPUnit\TestFixture\Issue5258\Issue5258Test" classname="PHPUnit.TestFixture.Issue5258.Issue5258Test" assertions="1" time="%f"/>
    <testcase name="testTwo" file="%sIssue5258Test.php" line="%d" class="PHPUnit\TestFixture\Issue5258\Issue5258Test" classname="PHPUnit.TestFixture.Issue5258.Issue5258Test" assertions="0" time="0.000000">
      <skipped/>
    </testcase>
  </testsuite>
</testsuites>
