--TEST--
phpunit -c _files/phpunit.xml --no-logging --log-junit php://stdout _files/NoLogNoCcTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--configuration',
    \realpath(__DIR__ . '/_files/phpunit.xml'),
    '--no-logging',
    '--log-junit',
    'php://stdout',
//    '--no-coverage',
//    '--coverage-filter',
//    \realpath(__DIR__ . '/_files/NoLogNoCc.php'),
//    '--coverage-text',
    \realpath(__DIR__ . '/_files/NoLogNoCcTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="NoLogNoCcTest" file="%sNoLogNoCcTest.php" tests="1" assertions="1" errors="0" warnings="0" failures="0" skipped="0" time="%f">
    <testcase name="testSuccess" class="NoLogNoCcTest" classname="NoLogNoCcTest" file="%sNoLogNoCcTest.php" line="17" assertions="1" time="%f"/>
  </testsuite>
</testsuites>


Time: %s, Memory: %s

OK (1 test, 1 assertion)
