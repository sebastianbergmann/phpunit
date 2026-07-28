--TEST--
A full test run prunes test run history entries of tests that no longer exist
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-prune-on-full-run-' . uniqid();

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
$_SERVER['argv'][] = '--no-output';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$data = json_decode(file_get_contents($historyFile), true);

$defects = array_keys($data['defects']);
$times   = array_keys($data['times']);

sort($defects);
sort($times);

print "defects:\n";

foreach ($defects as $key) {
    print '  ' . $key . "\n";
}

print "times:\n";

foreach ($times as $key) {
    print '  ' . $key . "\n";
}

unlink($historyFile);
rmdir($cacheDirectory);
--EXPECT--
defects:
  PHPUnit\TestFixture\TestRunHistory\Prune\PruneTest::testTwo
times:
  PHPUnit\TestFixture\TestRunHistory\Prune\PruneTest::testOne
  PHPUnit\TestFixture\TestRunHistory\Prune\PruneTest::testTwo
