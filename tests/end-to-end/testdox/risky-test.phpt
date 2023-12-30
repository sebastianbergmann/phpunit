--TEST--
TestDox: Risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/RiskyTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Risky (PHPUnit\TestFixture\TestDox\Risky)
 ⚠ This is a useless test that does not test anything

There was 1 risky test:

1) PHPUnit\TestFixture\TestDox\RiskyTest::test_this_is_a_useless_test_that_does_not_test_anything
This test did not perform any assertions

%s:16

OK, but there were issues!
Tests: 1, Assertions: 0, Risky: 1.
