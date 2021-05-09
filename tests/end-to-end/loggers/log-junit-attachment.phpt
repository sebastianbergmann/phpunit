--TEST--
phpunit --log-junit php://stdout _files/StatusTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/JunitAttachmentTest.php');

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...test output.test outputF                                                               5 / 5 (100%)<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="JunitAttachmentTest" file="%sJunitAttachmentTest.php" tests="5" assertions="5" errors="0" warnings="0" failures="1" skipped="0" time="%f">
    <testcase name="testOne" class="JunitAttachmentTest" classname="JunitAttachmentTest" file="%sJunitAttachmentTest.php" line="%d" assertions="1" time="%f">
      <system-out>[[ATTACHMENT|/tmp/path/to/example.png]][[ATTACHMENT|/tmp/there/can/be/more/than/one/attachment.txt]]</system-out>
    </testcase>
    <testcase name="testTwo" class="JunitAttachmentTest" classname="JunitAttachmentTest" file="%sJunitAttachmentTest.php" line="%d" assertions="1" time="%f"/>
    <testcase name="testWithOutput" class="JunitAttachmentTest" classname="JunitAttachmentTest" file="%sJunitAttachmentTest.php" line="%d" assertions="1" time="%f">
      <system-out>test output</system-out>
    </testcase>
    <testcase name="testWithOutputAndAttachment" class="JunitAttachmentTest" classname="JunitAttachmentTest" file="%sJunitAttachmentTest.php" line="%d" assertions="1" time="%f">
      <system-out>test output[[ATTACHMENT|/tmp/path/to/example.png]]</system-out>
    </testcase>
    <testcase name="testFailure" class="JunitAttachmentTest" classname="JunitAttachmentTest" file="%sJunitAttachmentTest.php" line="%d" assertions="1" time="%f">
      <failure type="PHPUnit\Framework\ExpectationFailedException">JunitAttachmentTest::testFailure
Failed asserting that false is true.

%sJunitAttachmentTest.php:60</failure>
      <system-out>[[ATTACHMENT|/tmp/path/to/failure.png]]</system-out>
    </testcase>
  </testsuite>
</testsuites>


Time: %s, Memory: %s

There was 1 failure:

1) JunitAttachmentTest::testFailure
Failed asserting that false is true.

%sJunitAttachmentTest.php:60

FAILURES!
Tests: 5, Assertions: 5, Failures: 1.
