--TEST--
phpunit ../../_files/DataProviderTooManyArguments.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderTooManyArguments.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProviderTooManyArguments::testMethodHavingTwoParameters
The data provider specified for PHPUnit\TestFixture\DataProviderTooManyArguments::testMethodHavingTwoParameters is invalid
The key "2" has more arguments (3) than the test method accepts (2).

%A

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.

