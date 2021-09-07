--TEST--
phpunit --log-junit php://stdout ../end-to-end/phpt-stderr.phpt
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../phpt-stderr.phpt');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="" tests="1" assertions="1" errors="0" warnings="0" failures="0" skipped="0" time="%s">
    <testcase name="phpt-stderr.phpt" file="%send-to-end%ephpt-stderr.phpt" assertions="1" time="%s">
      <system-out>PHPUnit must look at STDERR when running PHPT tests.</system-out>
    </testcase>
  </testsuite>
</testsuites>
