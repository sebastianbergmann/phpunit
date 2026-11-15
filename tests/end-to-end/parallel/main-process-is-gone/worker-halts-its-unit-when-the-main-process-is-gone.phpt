--TEST--
The workers of phpunit --parallel=2 whose main process is gone let the test that they are running finish, start no further test of its class, run tearDownAfterClass(), and end
--FILE--
<?php declare(strict_types=1);
$prefix = sys_get_temp_dir() . '/phpunit-parallel-main-process-is-gone';
$files  = ['.A.pid', '.B.pid', '.A.marker', '.B.marker', '.killed'];

foreach ($files as $suffix) {
    @unlink($prefix . $suffix);
}

$mainProcess = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../../../../phpunit',
        '--do-not-record-test-run-history',
        '--no-configuration',
        '--parallel=2',
        __DIR__ . '/_files/AIsRunningWhenTheMainProcessIsKilledTest.php',
        __DIR__ . '/_files/BIsRunningWhenTheMainProcessIsKilledTest.php',
    ],
    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
    $pipes,
);

// each of the two test classes is run by a worker process of its own, and
// its first test tells which process that is once it has started
for ($waited = 0; $waited < 3000 && !(is_file($prefix . '.A.pid') && is_file($prefix . '.B.pid')); $waited++) {
    usleep(10000);
}

// the main process is not asked to end, it is ended: with SIGKILL where
// there are signals, and with TerminateProcess() on Windows, where the
// signal is ignored
proc_terminate($mainProcess, 9);

while (proc_get_status($mainProcess)['running']) {
    usleep(10000);
}

// the tests finish now that the main process is gone
touch($prefix . '.killed');

$ended = static function (string $name) use ($prefix): bool
{
    return is_file($prefix . '.' . $name . '.marker') &&
           str_contains((string) file_get_contents($prefix . '.' . $name . '.marker'), 'worker process ended');
};

for ($waited = 0; $waited < 1000 && !($ended('A') && $ended('B')); $waited++) {
    usleep(10000);
}

foreach (['A', 'B'] as $name) {
    print $name . ':' . PHP_EOL;

    if (is_file($prefix . '.' . $name . '.marker')) {
        print file_get_contents($prefix . '.' . $name . '.marker');
    }

    // a worker process that did not end is not left behind
    if (!$ended($name) && is_file($prefix . '.' . $name . '.pid')) {
        $worker = (int) file_get_contents($prefix . '.' . $name . '.pid');

        if ($worker > 0 && DIRECTORY_SEPARATOR === '\\') {
            exec('taskkill /F /PID ' . $worker . ' 2>NUL');
        } elseif ($worker > 0) {
            exec('kill -9 ' . $worker . ' 2>/dev/null');
        }
    }
}

foreach ($pipes as $pipe) {
    fclose($pipe);
}

proc_close($mainProcess);

foreach ($files as $suffix) {
    @unlink($prefix . $suffix);
}
--EXPECT--
A:
testIsRunningWhenTheMainProcessIsKilled
tearDownAfterClass
worker process ended
B:
testIsRunningWhenTheMainProcessIsKilled
tearDownAfterClass
worker process ended
