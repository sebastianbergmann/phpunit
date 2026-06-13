--TEST--
Issues triggered during a tolerated attempt of a retried test are discarded together with its other events
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/_files/DeprecationInToleratedAttemptTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 retried test:

1) PHPUnit\TestFixture\Retry\DeprecationInToleratedAttemptTest::testOne
1 failed attempt

OK (1 test, 1 assertion)
