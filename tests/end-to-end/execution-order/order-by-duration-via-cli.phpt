--TEST--
phpunit --order-by=duration ./tests/end-to-end/execution-order/_files/TestWithDifferentDurations.php
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__);

if (is_dir($cacheDirectory)) {
    rmdir($cacheDirectory);
}

mkdir($cacheDirectory);

copy(__DIR__ . '/_files/TestWithDifferentDurations.phpunit.result.cache.txt', $cacheDirectory . DIRECTORY_SEPARATOR . 'test-results');

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--order-by=duration';
$_SERVER['argv'][] = '--cache-result';
$_SERVER['argv'][] = '--cache-directory=' . $cacheDirectory;
$_SERVER['argv'][] = __DIR__ . '/_files/TestWithDifferentDurations.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

unlink($cacheDirectory . DIRECTORY_SEPARATOR . 'test-results');
rmdir($cacheDirectory);
--EXPECTF--
PHPUnit Started (PHPUnit %s using PHP %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\TestWithDifferentDurations, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\TestWithDifferentDurations::testTwo)
Test Prepared (PHPUnit\TestFixture\TestWithDifferentDurations::testTwo)
Test Passed (PHPUnit\TestFixture\TestWithDifferentDurations::testTwo)
Test Finished (PHPUnit\TestFixture\TestWithDifferentDurations::testTwo)
Test Preparation Started (PHPUnit\TestFixture\TestWithDifferentDurations::testOne)
Test Prepared (PHPUnit\TestFixture\TestWithDifferentDurations::testOne)
Test Passed (PHPUnit\TestFixture\TestWithDifferentDurations::testOne)
Test Finished (PHPUnit\TestFixture\TestWithDifferentDurations::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestWithDifferentDurations::testThree)
Test Prepared (PHPUnit\TestFixture\TestWithDifferentDurations::testThree)
Test Passed (PHPUnit\TestFixture\TestWithDifferentDurations::testThree)
Test Finished (PHPUnit\TestFixture\TestWithDifferentDurations::testThree)
Test Suite Finished (PHPUnit\TestFixture\TestWithDifferentDurations, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
