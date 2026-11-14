--TEST--
phpunit --parallel=2 checks the counterexample that was remembered in the test run history when a property was last falsified before any other example, although only the main process has the test run history
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-parallel-remembered-counterexample-' . uniqid();

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
                'PHPUnit\TestFixture\ParallelProperty\RememberedCounterexampleTest::testValueIsNotMagic' => [[0, 12345]],
            ],
        ],
    ),
);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testValueIsNotMagic';
$_SERVER['argv'][] = __DIR__ . '/_files/RememberedCounterexampleTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

unlink($historyFile);
rmdir($cacheDirectory);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelProperty\RememberedCounterexampleTest::testValueIsNotMagic
Failed asserting that 12345 is not identical to 12345.

Property falsified after 1 example with this counterexample:

    $value = 12345;

%sRememberedCounterexampleTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
