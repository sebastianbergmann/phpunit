--TEST--
phpunit --parallel=2 --coverage-php writes the merged code coverage of work units that cover different files and different lines of the same file
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/skip-if-requires-code-coverage-driver.php';
--FILE--
<?php declare(strict_types=1);
use SebastianBergmann\CodeCoverage\Report\Facade;
use SebastianBergmann\CodeCoverage\Serialization\Unserializer;

$target = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--coverage-php';
$_SERVER['argv'][] = $target;
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__ . '/_files/src';
$_SERVER['argv'][] = __DIR__ . '/_files/tests';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$data = (new Unserializer)->unserialize($target);

unlink($target);

var_dump($data['basePath'] === realpath(__DIR__ . '/_files/src'));

foreach ($data['codeCoverage']->lineCoverage() as $file => $lines) {
    $tests = [];

    foreach ($lines as $line => $hits) {
        if ($hits === null || $hits === []) {
            continue;
        }

        $tests[] = $line . ' (' . count($hits) . ')';
    }

    print str_replace(DIRECTORY_SEPARATOR, '/', $file) . ': ' . ($tests === [] ? 'no line is covered' : 'covered lines (number of tests): ' . implode(', ', $tests)) . PHP_EOL;
}

$testIds = array_keys($data['testResults']);

sort($testIds);

print implode(PHP_EOL, $testIds) . PHP_EOL;

$summary = Facade::fromSerializedData($data)->summary();

print $summary->numberOfExecutedLines() . ' of ' . $summary->numberOfExecutableLines() . ' executable lines are covered' . PHP_EOL;
--EXPECT--
bool(true)
Alpha/One.php: covered lines (number of tests): 16 (1), 21 (1)
Beta/Two.php: covered lines (number of tests): 16 (1)
Gamma/Three.php: no line is covered
PHPUnit\TestFixture\ParallelCodeCoverage\FirstTest::testOne
PHPUnit\TestFixture\ParallelCodeCoverage\FirstTest::testTwo
PHPUnit\TestFixture\ParallelCodeCoverage\SecondTest::testThree
3 of 4 executable lines are covered
