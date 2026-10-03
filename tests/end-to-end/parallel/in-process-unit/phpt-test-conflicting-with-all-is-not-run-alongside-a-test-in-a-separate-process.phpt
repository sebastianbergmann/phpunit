--TEST--
phpunit --parallel=2 does not run a PHPT test that conflicts with "all" alongside a test that runs in a separate process, and therefore in the main process, when the PHPT test is started first
--FILE--
<?php declare(strict_types=1);
$intervals = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-parallel-in-process-unit-intervals-' . getmypid();

putenv('PHPUNIT_TEST_INTERVALS=' . $intervals);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/exclusivity/FirstTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/exclusivity/IsolatedTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/exclusivity/conflicts-with-all.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$intervalOf = static function (string $file): array
{
    [$start, $end] = explode(' ', file_get_contents($file));

    return ['start' => (float) $start, 'end' => (float) $end];
};

$isolated = $intervalOf($intervals . '.isolated');
$phpt     = $intervalOf($intervals . '.phpt');

var_dump($isolated['start'] < $phpt['end'] && $phpt['start'] < $isolated['end']);

@unlink($intervals . '.isolated');
@unlink($intervals . '.phpt');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
bool(false)
