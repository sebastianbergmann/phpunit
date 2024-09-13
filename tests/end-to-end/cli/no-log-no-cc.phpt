--TEST--
phpunit -c _files/phpunit.xml --no-logging --log-junit php://stdout _files/NoLogNoCcTest.php
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/no-log-cc-override/phpunit.xml';
$_SERVER['argv'][] = '--no-logging';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../_files/no-log-cc-override/NoLogNoCcTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\NoLogNoCcTest" file="%sNoLogNoCcTest.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%f">
    <testcase name="testSuccess" file="%sNoLogNoCcTest.php" line="18" class="PHPUnit\TestFixture\NoLogNoCcTest" classname="PHPUnit.TestFixture.NoLogNoCcTest" assertions="1" time="%f"/>
  </testsuite>
</testsuites>
