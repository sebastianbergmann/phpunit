--TEST--
The counterexamples of the other ways in which a property was falsified are remembered in the test run history after its minimal counterexample
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-counterexamples-of-other-falsifications-are-remembered-' . uniqid();

@mkdir($cacheDirectory, 0777, true);

$historyFile = $cacheDirectory . '/test-run-history';

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testIsFalsifiedInTwoWays';
$_SERVER['argv'][] = __DIR__ . '/_files/OtherFalsificationsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$data = json_decode(file_get_contents($historyFile), true);

foreach ($data['counterexamples'] as $test => $choiceSequences) {
    print $test . ': ' . json_encode($choiceSequences) . PHP_EOL;
}

unlink($historyFile);
rmdir($cacheDirectory);
--EXPECTF--
PHPUnit\TestFixture\PropertyBasedTesting\OtherFalsificationsTest::testIsFalsifiedInTwoWays: [[500],[%d]]
