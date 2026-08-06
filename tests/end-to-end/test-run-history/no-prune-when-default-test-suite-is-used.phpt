--TEST--
A test run that only runs the default test suite does not prune test run history entries of tests it did not run
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-no-prune-when-default-test-suite-is-used-' . uniqid();

@mkdir($cacheDirectory, 0777, true);

$historyFile = $cacheDirectory . '/test-run-history';

file_put_contents(
    $historyFile,
    json_encode(
        [
            'version' => 2,
            'defects' => ['StaleTest::testGone' => 7],
            'times'   => ['StaleTest::testGone' => 1.5],
        ],
    ),
);

$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/prune-with-default-test-suite/phpunit.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--no-output';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$data = json_decode(file_get_contents($historyFile), true);

$times = array_keys($data['times']);

sort($times);

print "times:\n";

foreach ($times as $key) {
    print '  ' . $key . "\n";
}

unlink($historyFile);
rmdir($cacheDirectory);
--EXPECT--
times:
  PHPUnit\TestFixture\TestRunHistory\PruneWithDefaultTestSuite\FirstTest::testOne
  StaleTest::testGone
