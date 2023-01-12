--TEST--
phpunit --stop-on-error ./tests/_files/StopOnErrorTestSuite.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-error';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/StopOnErrorTestSuite.php');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

IE

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\StopOnErrorTestSuite::testWithError
Error: StopOnErrorTestSuite_error

%s%etests%e_files%eStopOnErrorTestSuite.php:%d

ERRORS!
Tests: 2, Assertions: 1, Errors: 1, Incomplete: 1.
