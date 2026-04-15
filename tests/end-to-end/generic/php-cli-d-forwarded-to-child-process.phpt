--TEST--
PHP CLI -d settings are forwarded to child processes when using process isolation
--FILE--
<?php declare(strict_types=1);

$process = proc_open(
    [
        PHP_BINARY,
        '-d',
        'auto_prepend_file=' . __DIR__ . '/_files/auto_prepend_file.php',
        __DIR__ . '/../../../phpunit',
        '--do-not-cache-result',
        '--no-configuration',
        __DIR__ . '/_files/AutoPrependFileTest.php',
    ],
    [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes,
);

print stream_get_contents($pipes[1]);
fclose($pipes[1]);

$stderr = stream_get_contents($pipes[2]);
fclose($pipes[2]);

proc_close($process);

if ($stderr !== '') {
    print $stderr;
}
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
