--TEST--
phpunit -c _files/phpunit.xml --no-logging --log-junit php://stdout _files/NoLogNoCcTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/no-log-cc-override/phpunit.xml';
$_SERVER['argv'][] = '--no-logging';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/no-log-cc-override/NoLogNoCcTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\NoLogNoCcTest" file="%sNoLogNoCcTest.php" tests="1" assertions="1" errors="0" warnings="0" failures="0" skipped="0" time="%f">
    <testcase name="testSuccess" file="%sNoLogNoCcTest.php" line="19" class="PHPUnit\TestFixture\NoLogNoCcTest" classname="PHPUnit.TestFixture.NoLogNoCcTest" assertions="1" time="%f"/>
  </testsuite>
</testsuites>
