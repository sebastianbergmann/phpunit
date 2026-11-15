--TEST--
A worker of phpunit --parallel=2 whose main process is gone lets the test that it is running finish, starts no further test of its class, runs tearDownAfterClass(), and exits
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}

if (!function_exists('posix_kill')) {
    print "skip: this test requires the posix extension\n";
}
--FILE--
<?php declare(strict_types=1);
$prefix = sys_get_temp_dir() . '/phpunit-parallel-main-process-is-gone';

foreach (['.pid', '.killed', '.marker'] as $suffix) {
    @unlink($prefix . $suffix);
}

$mainProcess = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../../../../phpunit',
        '--do-not-record-test-run-history',
        '--no-configuration',
        '--parallel=2',
        __DIR__ . '/_files/',
    ],
    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
    $pipes,
);

// the test tells which worker process runs it once it has started
for ($waited = 0; $waited < 3000 && !is_file($prefix . '.pid'); $waited++) {
    usleep(10000);
}

$worker = (int) file_get_contents($prefix . '.pid');

proc_terminate($mainProcess, 9);

while (proc_get_status($mainProcess)['running']) {
    usleep(10000);
}

// the test finishes now that the main process is gone
touch($prefix . '.killed');

for ($waited = 0; $waited < 1000 && posix_kill($worker, 0); $waited++) {
    usleep(10000);
}

print 'worker process has exited: ';
var_dump(!posix_kill($worker, 0));

print file_get_contents($prefix . '.marker');

foreach ($pipes as $pipe) {
    fclose($pipe);
}

proc_close($mainProcess);

foreach (['.pid', '.killed', '.marker'] as $suffix) {
    @unlink($prefix . $suffix);
}
--EXPECT--
worker process has exited: bool(true)
testIsRunningWhenTheMainProcessIsKilled
tearDownAfterClass
