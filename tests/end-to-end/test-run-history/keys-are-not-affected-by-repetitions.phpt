--TEST--
Test run history entries of repeated tests are keyed without the repetition
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-test-run-history-repetitions-' . uniqid();

@mkdir($cacheDirectory, 0777, true);

$historyFile = $cacheDirectory . '/test-run-history';

$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/repetitions-and-attempts/phpunit.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
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
  PHPUnit\TestFixture\TestRunHistory\RepetitionsAndAttempts\RepetitionsAndAttemptsTest::testThree with data set #0
  PHPUnit\TestFixture\TestRunHistory\RepetitionsAndAttempts\RepetitionsAndAttemptsTest::testTwo
times:
  PHPUnit\TestFixture\TestRunHistory\RepetitionsAndAttempts\RepetitionsAndAttemptsTest::testOne
  PHPUnit\TestFixture\TestRunHistory\RepetitionsAndAttempts\RepetitionsAndAttemptsTest::testThree with data set "named"
  PHPUnit\TestFixture\TestRunHistory\RepetitionsAndAttempts\RepetitionsAndAttemptsTest::testThree with data set #0
  PHPUnit\TestFixture\TestRunHistory\RepetitionsAndAttempts\RepetitionsAndAttemptsTest::testTwo
