--TEST--
phpunit --compact ../_files/data-provider/EmptyDatasetTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/EmptyDatasetTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

No tests executed!

--- PHPUNIT ERROR: PHPUnit\TestFixture\DataProvider\EmptyDatasetTest::testOne
The data provider specified for PHPUnit\TestFixture\DataProvider\EmptyDatasetTest::testOne is invalid
Empty data set provided by data provider

--- PHPUNIT TEST RUNNER WARNING
No tests found in class "PHPUnit\TestFixture\DataProvider\EmptyDatasetTest".

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --no-configuration --compact %s
Working directory: %s
Configuration: (none)
Bootstrap: (none)
Extensions: (none)
Started: %s
Ended: %s (completed)
Shell exit code: 2
Tests: 0 run, 0 assertions
Issues triggered by tests: 1 PHPUnit error
Issues triggered by the test runner: 1 PHPUnit warning
