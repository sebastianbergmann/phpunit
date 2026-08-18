--TEST--
phpunit --retry 3 --parallel=2 attempts a PHPT test again after a failed attempt, reporting only the attempt that decided its result
--FILE--
<?php declare(strict_types=1);
@unlink(sys_get_temp_dir() . '/phpunit-parallel-phpt-retry.marker');

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryFlakyPhpt.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 retried test:

1) %sRetryFlakyPhpt.phpt
1 failed attempt

OK (1 test, 1 assertion)
