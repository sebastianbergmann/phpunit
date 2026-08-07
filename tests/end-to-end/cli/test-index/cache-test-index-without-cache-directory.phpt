--TEST--
The test index cannot be cached when no cache directory is configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--cache-test-index';
$_SERVER['argv'][] = __DIR__ . '/../../event/_files/SuccessTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered PHPUnit Warning (Cannot cache the test index because no cache directory is configured)
%A
