--TEST--
Multiple tests that passed after retrying are listed in the test result summary with their number of failed attempts
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = __DIR__ . '/_files/MultipleRetriedTestsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 retried tests:

1) PHPUnit\TestFixture\Retry\MultipleRetriedTestsTest::testOne
2 failed attempts

2) PHPUnit\TestFixture\Retry\MultipleRetriedTestsTest::testTwo
1 failed attempt

OK (2 tests, 2 assertions)
