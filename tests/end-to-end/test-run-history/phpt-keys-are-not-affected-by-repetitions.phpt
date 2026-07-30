--TEST--
Test run history entries of repeated PHPT tests are keyed without the repetition
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-test-run-history-phpt-repetitions-' . uniqid();

@mkdir($cacheDirectory, 0777, true);

$historyFile = $cacheDirectory . '/test-run-history';

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/FailingPhpt.phpt');

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$data = json_decode(file_get_contents($historyFile), true);

print "defects:\n";

foreach (array_keys($data['defects']) as $key) {
    print '  ' . basename($key) . "\n";
}

print "times:\n";

foreach (array_keys($data['times']) as $key) {
    print '  ' . basename($key) . "\n";
}

unlink($historyFile);
rmdir($cacheDirectory);
--EXPECT--
defects:
  FailingPhpt.phpt
times:
  FailingPhpt.phpt
