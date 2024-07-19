--TEST--
phpunit --covers PHPUnit\TestFixture\CoversUsesFiltering\Foo
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--covers';
$_SERVER['argv'][] = \PHPUnit\TestFixture\CoversUsesFiltering\Foo::class . ',stdClass';
$_SERVER['argv'][] = __DIR__ . '/../../_files/covers-uses';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Triggered Warning (Using comma-separated values with --covers is deprecated and will no longer work in PHPUnit 12. You can use --covers multiple times instead.)
Test Runner Configured
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (CLI Arguments, 1 test)
Test Suite Started (PHPUnit\TestFixture\CoversUsesFiltering\CoversTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\CoversUsesFiltering\CoversTest::testOne)
Test Prepared (PHPUnit\TestFixture\CoversUsesFiltering\CoversTest::testOne)
Test Passed (PHPUnit\TestFixture\CoversUsesFiltering\CoversTest::testOne)
Test Finished (PHPUnit\TestFixture\CoversUsesFiltering\CoversTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\CoversUsesFiltering\CoversTest, 1 test)
Test Suite Finished (CLI Arguments, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
