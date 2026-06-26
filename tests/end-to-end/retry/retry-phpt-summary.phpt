--TEST--
A PHPT test that passed after retrying is listed in the test result summary
--FILE--
<?php declare(strict_types=1);
@unlink(sys_get_temp_dir() . '/phpunit-e2e-phpt-retry.marker');

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/RetryFlakyPhpt.phpt');

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 retried test:

1) %sRetryFlakyPhpt.phpt
1 failed attempt

OK (1 test, 1 assertion)
