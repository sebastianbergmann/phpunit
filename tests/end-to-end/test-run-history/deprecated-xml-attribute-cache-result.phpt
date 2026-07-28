--TEST--
The cacheResult XML configuration attribute is deprecated
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecated-cache-result-attribute';
$_SERVER['argv'][] = '--debug';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered PHPUnit Deprecation (The "cacheResult" attribute is deprecated and will be removed in PHPUnit 14. Use "recordTestRunHistory" instead.)
%A
