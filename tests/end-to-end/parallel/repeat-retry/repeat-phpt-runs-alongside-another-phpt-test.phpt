--TEST--
phpunit --repeat 2 --parallel=2 runs the repetitions of a repeated PHPT test one after another, but runs two repeated PHPT tests alongside each other
--FILE--
<?php declare(strict_types=1);
$intervalFiles = [
    'a' => sys_get_temp_dir() . '/phpunit-parallel-repeat-interval-a.intervals',
    'b' => sys_get_temp_dir() . '/phpunit-parallel-repeat-interval-b.intervals',
];

foreach ($intervalFiles as $intervalFile) {
    @unlink($intervalFile);
}

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/repeat-interval-a.phpt';
$_SERVER['argv'][] = __DIR__ . '/_files/repeat-interval-b.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$intervals = [];

foreach ($intervalFiles as $name => $intervalFile) {
    foreach (explode(PHP_EOL, trim(file_get_contents($intervalFile))) as $interval) {
        [$start, $end] = explode(' ', $interval);

        $intervals[$name][] = ['start' => (float) $start, 'end' => (float) $end];
    }
}

$overlaps = function (array $a, array $b): bool
{
    return $a['start'] < $b['end'] && $b['start'] < $a['end'];
};

// The repetitions of one test share the temporary files that its runs are
// given, so they must run one after another ...
var_dump($overlaps($intervals['a'][0], $intervals['a'][1]));
var_dump($overlaps($intervals['b'][0], $intervals['b'][1]));

// ... while the two repeated tests are units of their own that run at the
// same time.
var_dump($overlaps($intervals['a'][0], $intervals['b'][0]));

foreach ($intervalFiles as $intervalFile) {
    @unlink($intervalFile);
}
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

OK (4 tests, 4 assertions)
bool(false)
bool(false)
bool(true)
