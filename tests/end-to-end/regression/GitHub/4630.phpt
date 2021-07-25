--TEST--
https://github.com/sebastianbergmann/phpunit/issues/4630
--FILE--
<?php declare(strict_types=1);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/4630/Issue4630Test.php';

require_once __DIR__ . '/../../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

W                                                                   1 / 1 (100%)<?xml version="1.0" encoding="UTF-8"?>
<tests/>


Time: %s, Memory: %s

There was 1 warning:

1) Warning
No tests found in class "PHPUnit\TestFixture\Issue4630Test".

WARNINGS!
Tests: 1, Assertions: 0, Warnings: 1.
