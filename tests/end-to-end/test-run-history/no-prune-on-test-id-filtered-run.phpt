--TEST--
A test run that is filtered by test ID does not prune test run history entries of tests it did not run
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-no-prune-on-test-id-filtered-run-' . uniqid();

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
$_SERVER['argv'][] = __DIR__ . '/_files/prune/phpunit.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--run-test-id';
$_SERVER['argv'][] = 'PHPUnit\TestFixture\TestRunHistory\Prune\PruneTest::testOne';
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
  PHPUnit\TestFixture\TestRunHistory\Prune\PruneTest::testOne
  StaleTest::testGone
