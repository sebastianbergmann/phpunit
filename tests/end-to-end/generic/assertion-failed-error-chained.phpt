--TEST--
phpunit ../../_files/AssertionFailedErrorChainedTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/AssertionFailedErrorChainedTest.php';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) AssertionFailedErrorChainedTest::testOne
foo

%sAssertionFailedErrorChainedTest.php:%d

Caused by RuntimeException: foo

%sAssertionFailedErrorChainedTest.php:%d

FAILURES!
Tests: 1, Assertions: 0, Failures: 1.
