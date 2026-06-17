--TEST--
#[Retry] with --testdox reports the deciding attempt without retry-related noise
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/PassesOnSecondAttemptTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Passes On Second Attempt (PHPUnit\TestFixture\Retry\PassesOnSecondAttempt)
 ✔ One

There was 1 retried test:

1) PHPUnit\TestFixture\Retry\PassesOnSecondAttemptTest::testOne
1 failed attempt

OK (1 test, 1 assertion)
