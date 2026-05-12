--TEST--
phpunit --filter 'foo}' (filter that compiles to an invalid regex) keeps all data providers running
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'foo}';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderSkipWhenFilteredTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::providerForA for test method PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA)
Data Provider Method Finished for PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA:
- PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::providerForA
Data Provider Method Called (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::providerForB for test method PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testB)
Data Provider Method Finished for PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testB:
- PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::providerForB
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (0 tests)
Test Runner Execution Started (0 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
