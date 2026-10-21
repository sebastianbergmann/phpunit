--TEST--
The counterexamples that were remembered for each way in which a property was falsified are checked first, in the order in which they were remembered
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-remembered-counterexample-' . uniqid();

@mkdir($cacheDirectory, 0777, true);

$historyFile = $cacheDirectory . '/test-run-history';

file_put_contents(
    $historyFile,
    json_encode(
        [
            'version'         => 2,
            'defects'         => [],
            'times'           => [],
            'counterexamples' => [
                'PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic' => [[0, 1], [0, 12345]],
            ],
        ],
    ),
);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testValueIsNotMagic';
$_SERVER['argv'][] = __DIR__ . '/_files/RememberedCounterexampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

unlink($historyFile);
rmdir($cacheDirectory);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic)
Test Property Falsified (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic, seed: %d, examples: 2, shrinking steps: %d, shrinking attempts: %d)
$value = 12345;
Test Failed (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic)
Failed asserting that 12345 is not identical to 12345.
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic, seed: %d, examples: 2, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic, 10 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
