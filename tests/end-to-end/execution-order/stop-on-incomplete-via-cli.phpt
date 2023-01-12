--TEST--
phpunit --stop-on-error ./tests/_files/StopOnErrorTestSuite.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-incomplete';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/StopOnErrorTestSuite.php');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

I

Time: %s, Memory: %s

OK, but some tests have issues!
Tests: 1, Assertions: 0, Incomplete: 1.
