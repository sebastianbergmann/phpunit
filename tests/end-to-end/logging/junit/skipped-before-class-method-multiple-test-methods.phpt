--TEST--
JUnit XML: test suite skipped in setUpBeforeClass() with multiple test methods
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/SkippedBeforeClassWithMultipleTestMethodsTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\Logging\SkippedBeforeClassWithMultipleTestMethodsTest" file="%sSkippedBeforeClassWithMultipleTestMethodsTest.php" tests="3" assertions="0" errors="0" failures="0" skipped="3" time="0.000000"/>
</testsuites>
