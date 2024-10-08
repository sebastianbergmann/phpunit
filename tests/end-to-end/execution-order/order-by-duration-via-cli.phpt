--TEST--
phpunit --order-by=duration ./tests/end-to-end/execution-order/_files/TestWithDifferentDurations.php
--FILE--
<?php declare(strict_types=1);
$tmpResultCache = sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__);

\copy(__DIR__ . '/_files/TestWithDifferentDurations.phpunit.result.cache.txt', $tmpResultCache);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--order-by=duration';
$_SERVER['argv'][] = '--cache-result';
$_SERVER['argv'][] = '--cache-result-file=' . $tmpResultCache;
$_SERVER['argv'][] = __DIR__ . '/_files/TestWithDifferentDurations.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using PHP %s)
Test Runner Configured
Test Suite Loaded (3 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\TestWithDifferentDurations, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\TestWithDifferentDurations::testTwo)
Test Prepared (PHPUnit\TestFixture\TestWithDifferentDurations::testTwo)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\TestWithDifferentDurations::testTwo)
Test Finished (PHPUnit\TestFixture\TestWithDifferentDurations::testTwo)
Test Preparation Started (PHPUnit\TestFixture\TestWithDifferentDurations::testOne)
Test Prepared (PHPUnit\TestFixture\TestWithDifferentDurations::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\TestWithDifferentDurations::testOne)
Test Finished (PHPUnit\TestFixture\TestWithDifferentDurations::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestWithDifferentDurations::testThree)
Test Prepared (PHPUnit\TestFixture\TestWithDifferentDurations::testThree)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\TestWithDifferentDurations::testThree)
Test Finished (PHPUnit\TestFixture\TestWithDifferentDurations::testThree)
Test Suite Finished (PHPUnit\TestFixture\TestWithDifferentDurations, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__));
