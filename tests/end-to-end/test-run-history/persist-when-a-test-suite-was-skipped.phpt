--TEST--
The test run history is persisted when a test suite was skipped before its first test
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-persist-when-a-test-suite-was-skipped-' . uniqid();

@mkdir($cacheDirectory, 0777, true);

$historyFile = $cacheDirectory . '/test-run-history';

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = __DIR__ . '/_files/skipped-suite';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

if (is_file($historyFile)) {
    print "persisted: yes\n";

    $data = json_decode(file_get_contents($historyFile), true);

    print "defects:\n";

    foreach (array_keys($data['defects']) as $key) {
        print '  ' . $key . "\n";
    }

    unlink($historyFile);
} else {
    print "persisted: no\n";
}

rmdir($cacheDirectory);
--EXPECT--
persisted: yes
defects:
  PHPUnit\TestFixture\TestRunHistory\SkippedSuite\SubsequentTest::testOne
