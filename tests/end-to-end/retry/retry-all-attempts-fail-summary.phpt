--TEST--
A test that exhausts all attempts is reported as a regular failure and is not listed as a retried test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = __DIR__ . '/_files/AllAttemptsFailTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Retry\AllAttemptsFailTest::testOne (attempt 3 of 3)
Failure on attempt 3

%sAllAttemptsFailTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
