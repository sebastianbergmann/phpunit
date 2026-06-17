--TEST--
A test that passes after multiple failed attempts produces a single progress character
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = __DIR__ . '/_files/CliRetryMultipleFailedAttemptsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 retried test:

1) PHPUnit\TestFixture\Retry\CliRetryMultipleFailedAttemptsTest::testOne
2 failed attempts

OK (1 test, 1 assertion)
