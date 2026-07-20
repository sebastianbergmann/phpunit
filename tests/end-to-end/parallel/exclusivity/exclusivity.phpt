--TEST--
phpunit --parallel=3 runs a #[DoNotRunInParallel] test class and a PHPT test that conflicts with "all" without any other test executing alongside them
--FILE--
<?php declare(strict_types=1);
$intervalFiles = [
    'first'     => sys_get_temp_dir() . '/phpunit-parallel-exclusivity-first.interval',
    'exclusive' => sys_get_temp_dir() . '/phpunit-parallel-exclusivity-exclusive.interval',
    'third'     => sys_get_temp_dir() . '/phpunit-parallel-exclusivity-third.interval',
    'phpt'      => sys_get_temp_dir() . '/phpunit-parallel-exclusivity-phpt.interval',
];

foreach ($intervalFiles as $intervalFile) {
    @unlink($intervalFile);
}

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=3';
$_SERVER['argv'][] = __DIR__ . '/_files/SlowFirstTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/ExclusiveTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/SlowThirdTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/exclusive-all.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$intervals = [];

foreach ($intervalFiles as $name => $intervalFile) {
    [$start, $end] = explode(' ', file_get_contents($intervalFile));

    $intervals[$name] = ['start' => (float) $start, 'end' => (float) $end];
}

$overlaps = function (array $a, array $b): bool
{
    return $a['start'] < $b['end'] && $b['start'] < $a['end'];
};

$exclusiveOverlapsAnother = false;
$phptOverlapsAnother      = false;

foreach ($intervals as $name => $interval) {
    if ($name !== 'exclusive' && $overlaps($intervals['exclusive'], $interval)) {
        $exclusiveOverlapsAnother = true;
    }

    if ($name !== 'phpt' && $overlaps($intervals['phpt'], $interval)) {
        $phptOverlapsAnother = true;
    }
}

var_dump($exclusiveOverlapsAnother, $phptOverlapsAnother);

foreach ($intervalFiles as $intervalFile) {
    @unlink($intervalFile);
}
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

OK (4 tests, 4 assertions)
bool(false)
bool(false)
