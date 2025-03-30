--TEST--
TestDox: Risky; Colorized
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = __DIR__ . '/_files/RiskyTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

[4mRisky (PHPUnit\TestFixture\TestDox\Risky)[0m
[33m ⚠ [0mThis is a useless test that does not test anything

There was 1 risky test:

1) PHPUnit\TestFixture\TestDox\RiskyTest::test_this_is_a_useless_test_that_does_not_test_anything
This test did not perform any assertions

%s:16

[30;43mOK, but there were issues![0m
[30;43mTests: 1[0m[30;43m, Assertions: 0[0m[30;43m, Risky: 1[0m[30;43m.[0m
