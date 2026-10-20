--TEST--
The counterexample of a property that is falsified is remembered in the test run history, and the one of a property that holds is forgotten
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-counterexamples-are-remembered-and-forgotten-' . uniqid();

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
                'PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testHolds' => [[0, 5]],
            ],
        ],
    ),
);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testHolds|testSumIsAtMostTen';
$_SERVER['argv'][] = __DIR__ . '/_files/RememberedCounterexampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$data = json_decode(file_get_contents($historyFile), true);

foreach ($data['counterexamples'] as $test => $choiceSequence) {
    print $test . ': ' . json_encode($choiceSequence) . PHP_EOL;
}

unlink($historyFile);
rmdir($cacheDirectory);
--EXPECT--
PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testSumIsAtMostTen: [[0,11]]
