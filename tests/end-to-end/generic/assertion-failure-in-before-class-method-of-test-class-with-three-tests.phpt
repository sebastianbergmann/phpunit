--TEST--
phpunit ../_files/AssertionFailureInBeforeClassMethodOfTestClassWithThreeTestsTest.php ../_files/basic/SuccessTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/AssertionFailureInBeforeClassMethodOfTestClassWithThreeTestsTest.php';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

FFF.                                                                4 / 4 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\AssertionFailureInBeforeClassMethodOfTestClassWithThreeTestsTest
message

%sAssertionFailureInBeforeClassMethodOfTestClassWithThreeTestsTest.php:%d

FAILURES!
Tests: 4, Assertions: 1, Failures: 1.
