--TEST--
A test whose deciding attempt is skipped is still listed as a retried test so that its tolerated failed attempt remains visible
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = __DIR__ . '/_files/SkippedAttemptTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

S                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 retried test:

1) PHPUnit\TestFixture\Retry\SkippedAttemptTest::testOne
1 failed attempt

OK, but some tests were skipped!
Tests: 1, Assertions: 0, Skipped: 1.
