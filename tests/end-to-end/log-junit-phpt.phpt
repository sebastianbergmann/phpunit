--TEST--
phpunit --log-junit php://stdout ../end-to-end/phpt-stderr.phpt
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-junit';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = \realpath(__DIR__ . '/../end-to-end/phpt-stderr.phpt');

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%s">
    <testcase name="%send-to-end%ephpt-stderr.phpt" assertions="1" time="%s">
      <system-out>PHPUnit must look at STDERR when running PHPT tests.</system-out>
    </testcase>
  </testsuite>
</testsuites>


Time: %s, Memory: %s

OK (1 test, 1 assertion)
